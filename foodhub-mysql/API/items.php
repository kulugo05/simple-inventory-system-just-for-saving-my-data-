    <?php
    // FILE: api/items.php
    require_once __DIR__ . '/../config/database.php';
    apiHeaders();
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ── LOAD ITEMS ──
    if ($method === 'GET') {
        $id = $_GET['id'] ?? null; // ETO ANG KULANG MO DATI
        $search = $_GET['search'] ?? '';
        $cat = $_GET['category'] ?? 'all';
        
        // 1. KUNG MAY ID (Para sa EDIT popup)
        if ($id) {
            $query = "SELECT *, 'in_stock' as status FROM products WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            $items = $stmt->fetchAll();
            jsonResponse(['success' => true, 'data' => $items]);
            exit; // Tapos na ang request dito
        }

        // 2. KUNG WALANG ID (Para sa INVENTORY TABLE)
        $query = "SELECT *, 
                    CASE 
                        WHEN quantity <= 0 THEN 'out_of_stock' 
                        WHEN quantity <= reorder_level THEN 'low_stock_count' 
                        ELSE 'in_stock' 
                    END as status 
                FROM products WHERE 1";
        
        $params = [];

        if ($search) {
            $query .= " AND (sku LIKE ? OR name LIKE ?)";
            $params[] = $search . '%';    
            $params[] = '%' . $search . '%'; 
        }

        if ($cat !== 'all') {
            $query .= " AND category = ?";
            $params[] = $cat;
        }

        // Dagdagan natin ng ORDER BY para hindi nagkakagulo ang listahan
        $query .= " ORDER BY id DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $items]);
    }

    // ── SAVE / UPDATE ITEM ──
    if ($method === 'POST' || $method === 'PUT') {
        $body = getBody();
        $id = $_GET['id'] ?? null;
        
        $data = [
            $body['name'], $body['sku'], $body['category'], $body['unit'],
            $body['quantity'], $body['reorder_lvl'], $body['cost_price'],
            $body['sell_price'], $body['supplier'], $body['description']
        ];

        if ($id) {
            $sql = "UPDATE products SET name=?, sku=?, category=?, unit=?, quantity=?, reorder_level=?, cost_price=?, sell_price=?, supplier=?, description=? WHERE id=?";
            $data[] = $id;
        } else {
            $sql = "INSERT INTO products (name, sku, category, unit, quantity, reorder_level, cost_price, sell_price, supplier, description) VALUES (?,?,?,?,?,?,?,?,?,?)";
        }

        $db->prepare($sql)->execute($data);
        jsonResponse(['success' => true]);
    }

    // ── DELETE ITEM ──
    if ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);
    }