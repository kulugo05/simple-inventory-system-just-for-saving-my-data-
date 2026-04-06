<?php
// ============================================================
// MENU ITEMS API - api/menu_items.php
// Nagha-handle ng lahat ng menu items (dishes ng restaurant).
// Ginagamit ito sa Sales page para pumili ng items.
// GET    = kunin ang menu items (pwedeng filter by category)
// POST   = mag-add ng bagong menu item
// PUT    = i-update ang menu item
// DELETE = burahin ang menu item
// ============================================================

require_once __DIR__ . '/../config/database.php';
apiHeaders();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// --- Kunin ang menu items ---
if ($method === 'GET') {
    $cat = trim($_GET['category'] ?? '');

    // Kung may specific ID, ibalik lang yung isang item
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['success' => true, 'data' => $stmt->fetch()]);
    }

    // Kung may category filter, i-apply ito
    if ($cat && $cat !== 'all') {
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE category = ? AND is_active = 1 ORDER BY name");
        $stmt->execute([$cat]);
    } else {
        // Walang filter, ibalik lahat ng active items
        $stmt = $db->query("SELECT * FROM menu_items WHERE is_active = 1 ORDER BY category, name");
    }

    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

// --- Mag-add ng bagong menu item ---
if ($method === 'POST') {
    $body = getBody();
    if (empty($body['name']) || empty($body['category']) || !isset($body['price'])) {
        jsonResponse(['success' => false, 'message' => 'Punan ang lahat!'], 422);
    }
    $db->prepare("INSERT INTO menu_items (name, category, price) VALUES (?,?,?)")
       ->execute([trim($body['name']), trim($body['category']), (float)$body['price']]);
    jsonResponse(['success' => true, 'message' => 'Menu item added!'], 201);
}

// --- I-update ang menu item ---
if ($method === 'PUT') {
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required'], 400);
    $body = getBody();
    $db->prepare("UPDATE menu_items SET name=?, category=?, price=?, is_active=? WHERE id=?")
       ->execute([trim($body['name']), trim($body['category']), (float)$body['price'], (int)($body['is_active'] ?? 1), $id]);
    jsonResponse(['success' => true, 'message' => 'Updated!']);
}

// --- Burahin ang menu item ---
if ($method === 'DELETE') {
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required'], 400);
    $db->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Deleted!']);
}
