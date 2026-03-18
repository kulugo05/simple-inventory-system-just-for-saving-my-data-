<?php
require_once __DIR__ . '/../config/database.php';
apiHeaders();

try {
    $db = getDB();
    // Ginamit ang JOIN para makuha ang pangalan mula sa products table
    $logs = $db->query("
        SELECT sl.id, p.name as item_name, sl.type, sl.amount, sl.reason, sl.timestamp
        FROM stock_logs sl
        JOIN products p ON sl.item_id = p.id
        ORDER BY sl.timestamp DESC LIMIT 100
    ")->fetchAll();
    jsonResponse(['success' => true, 'data' => $logs]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}