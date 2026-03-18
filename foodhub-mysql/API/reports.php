<?php
require_once __DIR__ . '/../config/database.php';
apiHeaders();
$db = getDB();

try {
    // 1. Kunin ang stats (Total, Low Stock, Out of Stock)
    $stats = $db->query("
        SELECT 
            COUNT(*) AS total_products,
            COALESCE(SUM(quantity * cost_price), 0) AS total_value,
            SUM(CASE WHEN quantity <= reorder_level AND quantity > 0 THEN 1 ELSE 0 END) AS low_stock_count,
            SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock
        FROM products
    ")->fetch();

    // 2. Kunin ang listahan ng mismong Alerts (Eto yung lalabas sa Dashboard)
    $alerts = $db->query("
        SELECT id, name, sku, quantity, reorder_level,
        CASE 
            WHEN quantity <= 0 THEN 'out_of_stock' 
            ELSE 'low_stock' 
        END as status
        FROM products 
        WHERE quantity <= reorder_level
        ORDER BY quantity ASC LIMIT 10
    ")->fetchAll();

    // 3. Category summary
    $categories = $db->query("
        SELECT category, COUNT(*) AS count, SUM(quantity * cost_price) AS value
        FROM products GROUP BY category
    ")->fetchAll();

    jsonResponse([
        'total_products'  => (int)$stats['total_products'],
        'total_value'     => (float)$stats['total_value'],
        'low_stock_count' => (int)$stats['low_stock_count'],
        'out_of_stock'    => (int)$stats['out_of_stock'],
        'alerts'          => $alerts, // Siguraduhin na 'alerts' ang name
        'categories'      => $categories
    ]);
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}