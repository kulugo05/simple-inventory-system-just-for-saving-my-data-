<?php
// ============================================================
// REPORTS API - api/reports.php
// Nagbibigay ng summary data para sa Dashboard.
// Kasama ang: stats, stock alerts, at category breakdown.
// ============================================================

require_once __DIR__ . '/../config/database.php';
apiHeaders();

$db = getDB();

try {
    // --- Kunin ang overall stats ng inventory ---
    $stats = $db->query("
        SELECT
            COUNT(*) AS total_products,
            COALESCE(SUM(quantity * cost_price), 0) AS total_value,
            SUM(CASE WHEN quantity <= reorder_level AND quantity > 0 THEN 1 ELSE 0 END) AS low_stock_count,
            SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock
        FROM products
    ")->fetch();

    // --- Kunin ang listahan ng items na mababa na ang stock ---
    // Gagamitin ito para sa Stock Alerts section ng dashboard
    $alerts = $db->query("
        SELECT id, name, sku, quantity, reorder_level AS reorder_lvl,
            CASE
                WHEN quantity <= 0 THEN 'out_of_stock'
                ELSE 'low_stock'
            END AS status
        FROM products
        WHERE quantity <= reorder_level
        ORDER BY quantity ASC
        LIMIT 10
    ")->fetchAll();

    // --- Kunin ang breakdown ng items per category ---
    $categories = $db->query("
        SELECT category, COUNT(*) AS count, SUM(quantity * cost_price) AS value
        FROM products
        GROUP BY category
    ")->fetchAll();

    // I-return ang lahat ng data bilang JSON
    jsonResponse([
        'total_products'  => (int)$stats['total_products'],
        'total_value'     => (float)$stats['total_value'],
        'low_stock_count' => (int)$stats['low_stock_count'],
        'out_of_stock'    => (int)$stats['out_of_stock'],
        'alerts'          => $alerts,
        'categories'      => $categories,
    ]);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
