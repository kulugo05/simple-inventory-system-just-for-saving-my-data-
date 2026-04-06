<?php
// ============================================================
// ITEMS API - api/items.php
// Nagha-handle ng lahat ng CRUD operations para sa inventory.
// GET    = kunin ang items (may search, date, at category filter)
// POST   = mag-add ng bagong item
// PUT    = i-update ang item
// DELETE = burahin ang item
// ============================================================

require_once __DIR__ . '/../config/database.php';
apiHeaders();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ── KUNIN ANG ITEMS ─────────────────────────────────────────
if ($method === 'GET') {
    $id     = $_GET['id'] ?? null;
    $search = $_GET['search'] ?? '';
    $cat    = $_GET['category'] ?? 'all';
    $date   = $_GET['date'] ?? '';

    // Kung may specific ID, ibalik lang yung isang item (para sa edit)
    if ($id) {
        $query = "SELECT *, CASE
                    WHEN quantity <= 0 THEN 'out_of_stock'
                    WHEN quantity <= reorder_level THEN 'low_stock'
                    ELSE 'in_stock'
                  END AS status FROM products WHERE id = ?";
        $stmt  = $db->prepare($query);
        $stmt->execute([$id]);
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    // Base query para sa inventory table
    // Ang CASE statement ay nagdedetermin ng status ng bawat item
    $query  = "SELECT *, CASE
                    WHEN quantity <= 0 THEN 'out_of_stock'
                    WHEN quantity <= reorder_level THEN 'low_stock'
                    ELSE 'in_stock'
               END AS status FROM products WHERE 1";
    $params = [];

    // I-apply ang search filter kapag may hinahanap
    if ($search) {
        $query   .= " AND (sku LIKE ? OR name LIKE ?)";
        $params[] = $search . '%';
        $params[] = '%' . $search . '%';
    }

    // I-filter by date kung may pinili
    if ($date) {
        $query   .= " AND DATE(created_at) = ?";
        $params[] = $date;
    }

    // I-filter by category kung hindi 'all'
    if ($cat !== 'all') {
        $query   .= " AND category = ?";
        $params[] = $cat;
    }

    // Pinaka-bago muna ang ipapakita
    $query .= " ORDER BY id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

// ── MAG-SAVE O MAG-UPDATE NG ITEM ───────────────────────────
if ($method === 'POST' || $method === 'PUT') {
    $body = getBody();
    $id   = $_GET['id'] ?? null;

    $data = [
        $body['name'],        $body['sku'],         $body['category'],
        $body['unit'],        $body['quantity'],     $body['reorder_lvl'],
        $body['cost_price'],  $body['sell_price'],   $body['supplier'],
        $body['description']
    ];

    if ($id) {
        // UPDATE - existing item
        $sql    = "UPDATE products SET name=?, sku=?, category=?, unit=?, quantity=?,
                   reorder_level=?, cost_price=?, sell_price=?, supplier=?, description=?
                   WHERE id=?";
        $data[] = $id;
    } else {
        // INSERT - bagong item
        $sql = "INSERT INTO products (name, sku, category, unit, quantity, reorder_level,
                cost_price, sell_price, supplier, description) VALUES (?,?,?,?,?,?,?,?,?,?)";
    }

    $db->prepare($sql)->execute($data);

    // Kung bagong item, mag-log sa stock_logs para may record
    if (!$id) {
        $newId = $db->lastInsertId();
        $db->prepare("
            INSERT INTO stock_logs (item_id, item_name, type, amount, reason, timestamp)
            VALUES (?, ?, 'add', ?, 'New item added', NOW())
        ")->execute([$newId, $body['name'], $body['quantity']]);
    }

    jsonResponse(['success' => true]);
}

// ── BURAHIN ANG ITEM ────────────────────────────────────────
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;

    // Kunin muna ang item info bago burahin para sa log
    $stmt = $db->prepare("SELECT name, quantity FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

    // I-log ang deletion
    if ($product) {
        $db->prepare("
            INSERT INTO stock_logs (item_id, item_name, type, amount, reason, timestamp)
            VALUES (?, ?, 'remove', ?, 'Item deleted', NOW())
        ")->execute([$id, $product['name'], $product['quantity']]);
    }

    jsonResponse(['success' => true]);
}
