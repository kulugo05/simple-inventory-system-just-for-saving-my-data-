<?php
require_once __DIR__ . '/../config/database.php';
apiHeaders();

try {
    $db   = getDB();
    // Kunin ang pinakabagong 100 logs, pinaka-bago muna
    $logs = $db->query("
        SELECT id, item_name, type, amount, reason, timestamp
        FROM stock_logs
        ORDER BY timestamp DESC
        LIMIT 100
    ")->fetchAll();

    jsonResponse(['success' => true, 'data' => $logs]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
