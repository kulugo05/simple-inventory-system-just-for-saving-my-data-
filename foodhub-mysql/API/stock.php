<?php
// FILE: api/stock.php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Kulang ang data.']);
    exit;
}

try {
    $db     = getDB();
    $id     = (int)$data['id'];
    $amount = (int)($data['amount'] ?? 0);
    $type   = $data['type'] ?? 'add';
    $reason = $data['reason'] ?? 'Manual Adjustment';

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        exit;
    }

    // Kunin ang item name para sa log
    $itemStmt = $db->prepare("SELECT name, quantity FROM products WHERE id = ?");
    $itemStmt->execute([$id]);
    $item = $itemStmt->fetch();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    // Check kung sapat ang stock para sa remove
    if ($type === 'remove' && $item['quantity'] < $amount) {
        echo json_encode(['success' => false, 'message' => 'Hindi sapat ang stock. Available: ' . $item['quantity']]);
        exit;
    }

    // Update quantity
    if ($type === 'add') {
        $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
           ->execute([$amount, $id]);
    } else {
        $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")
           ->execute([$amount, $id]);
    }

    // I-log sa stock_logs kasama ang item_name
    $db->prepare("
        INSERT INTO stock_logs (item_id, item_name, type, amount, reason, timestamp)
        VALUES (?, ?, ?, ?, ?, NOW())
    ")->execute([$id, $item['name'], $type, $amount, $reason]);

    echo json_encode(['success' => true, 'message' => 'Stock updated!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}