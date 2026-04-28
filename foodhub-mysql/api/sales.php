<?php
session_start();
require_once __DIR__ . '/../config/database.php';
apiHeaders();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = $_GET['type'] ?? 'today';
    $cat  = trim($_GET['category'] ?? '');

    // WHERE clause para sa sales table (walang alias)
    if ($type === 'today') {
        $where = "WHERE sale_date = '" . date('Y-m-d') . "'";
    } elseif ($type === 'custom' && !empty($_GET['date'])) {
        $date  = $_GET['date'];
        $where = "WHERE sale_date = '$date'";
    } elseif ($type === 'week') {
        $where = "WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($type === 'month') {
        $where = "WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($type === 'graph') {
        $days  = (int)($_GET['days'] ?? 30);
        $month = $_GET['month'] ?? null;

        if ($month) {
            $where = "WHERE DATE_FORMAT(sale_date, '%Y-%m') = '$month'";
        } else {
            $where = "WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)";
        }

        $graphData = $db->query("
            SELECT sale_date, SUM(total) AS daily_total
            FROM sales $where
            GROUP BY sale_date
            ORDER BY sale_date ASC
        ")->fetchAll();

        $labels = array_map(fn($r) => date('M d', strtotime($r['sale_date'])), $graphData);
        $values = array_map(fn($r) => round((float)$r['daily_total'], 2), $graphData);

        $todayTotal = $db->query("SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date = CURDATE()")->fetchColumn();
        $weekTotal  = $db->query("SELECT COALESCE(SUM(total),0) FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
        $monthTotal = $db->query("SELECT COALESCE(SUM(total),0) FROM sales WHERE DATE_FORMAT(sale_date,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();

        jsonResponse([
            'success'     => true,
            'labels'      => $labels,
            'values'      => $values,
            'today_total' => round((float)$todayTotal, 2),
            'week_total'  => round((float)$weekTotal, 2),
            'month_total' => round((float)$monthTotal, 2),
        ]);
    } else {
        $where = "";
    }

    // I-apply ang category filter — walang alias para sa simple queries
    if ($cat && $cat !== 'all') {
        $catVal  = "'" . addslashes($cat) . "'";
        $where  .= ($where ? " AND category = " : " WHERE category = ") . $catVal;
        // Separate WHERE para sa JOIN query (kailangan ng alias)
        $whereJoin = $where . ""; // same
    } else {
        $whereJoin = $where;
    }

    // Stats query - simple, walang JOIN
    $stats = $db->query("
        SELECT
            COALESCE(SUM(total), 0) AS total_sales,
            COALESCE(SUM(quantity), 0) AS total_orders
        FROM sales $where
    ")->fetch();

    // Best sellers - simple, walang JOIN
    $best = $db->query("
        SELECT item_name, category,
               SUM(quantity) AS total_qty,
               SUM(total) AS total_sales
        FROM sales $where
        GROUP BY menu_item_id, item_name, category
        ORDER BY total_qty DESC
        LIMIT 10
    ")->fetchAll();

    // Sales list - may JOIN, kailangan ng s. prefix para sa category
    $whereForJoin = str_replace('WHERE category =', 'WHERE s.category =', $whereJoin);
    $whereForJoin = str_replace('AND category =', 'AND s.category =', $whereForJoin);

    $list = $db->query("
        SELECT s.*, mi.name AS menu_name
        FROM sales s
        LEFT JOIN menu_items mi ON s.menu_item_id = mi.id
        $whereForJoin
        ORDER BY s.created_at DESC
        LIMIT 50
    ")->fetchAll();

    $selectedDate = !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $weekly = $db->query("
        SELECT COALESCE(SUM(total), 0)
        FROM sales
        WHERE sale_date >= DATE_SUB('$selectedDate', INTERVAL 7 DAY)
        AND sale_date <= '$selectedDate'
    ")->fetchColumn();

    jsonResponse([
        'success'      => true,
        'total_sales'  => round((float)$stats['total_sales'], 2),
        'total_orders' => (int)$stats['total_orders'],
        'weekly_total' => round((float)$weekly, 2),
        'best_sellers' => $best,
        'list'         => $list,
    ]);
}

if ($method === 'POST') {
    $body = getBody();

    if (empty($body['menu_item_id']) || empty($body['quantity'])) {
        jsonResponse(['success' => false, 'message' => 'Missing data'], 422);
    }

    $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([(int)$body['menu_item_id']]);
    $menu = $stmt->fetch();

    if (!$menu) jsonResponse(['success' => false, 'message' => 'Menu item not found'], 404);

    $qty   = (int)$body['quantity'];
    $price = (float)($body['price'] ?? $menu['price']);
    $total = $qty * $price;
    $date  = $body['sale_date'] ?? date('Y-m-d');

    $db->prepare("
        INSERT INTO sales (menu_item_id, item_name, category, quantity, price, total, sale_date, recorded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $menu['id'], $menu['name'], $menu['category'],
        $qty, $price, $total, $date,
        $_SESSION['username'] ?? 'staff'
    ]);

    jsonResponse(['success' => true, 'message' => 'Sale recorded!', 'total' => $total]);
}