<?php
// ============================================================
// STOCK ADJUSTMENT API - api/stock.php
// Nagha-handle ng pag-add at pag-remove ng stock.
// Awtomatikong naglo-log sa stock_logs table.
// ============================================================

require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Basahin ang data mula sa request body
$data = json_decode(file_get_contents('php://input'), true);

// I-check kung kumpleto ang data
if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Kulang ang data.']);
    exit;
}

try {
    $db     = getDB();
    $id     = (int)$data['id'];
    $amount = (int)($data['amount'] ?? 0);
    $type   = trim(strtolower($data['type'] ?? 'add')); // 'add' o 'remove'
    $reason = $data['reason'] ?? 'Manual Adjustment';

    // Dapat positive ang amount
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        exit;
    }

    // Kunin ang current data ng item
    $stmt = $db->prepare("SELECT name, quantity FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    // Kung nag-remove, i-check kung may sapat na stock
    if ($type === 'remove' && $item['quantity'] < $amount) {
        echo json_encode([
            'success' => false,
            'message' => 'Hindi sapat ang stock. Available: ' . $item['quantity']
        ]);
        exit;
    }

    // I-update ang quantity ng product
    if ($type === 'add') {
        $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
           ->execute([$amount, $id]);
    } else {
        $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")
           ->execute([$amount, $id]);
    }

    // I-log ang adjustment sa stock_logs para may history
    $db->prepare("
        INSERT INTO stock_logs (item_id, item_name, type, amount, reason, timestamp)
        VALUES (?, ?, ?, ?, ?, NOW())
    ")->execute([$id, $item['name'], $type, $amount, $reason]);

    echo json_encode(['success' => true, 'message' => 'Stock updated!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
