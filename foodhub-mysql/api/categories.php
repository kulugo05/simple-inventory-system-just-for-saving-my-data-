<?php
// ============================================================
// CATEGORIES API - api/categories.php
// Nagha-handle ng lahat ng requests para sa categories table.
// GET    = kunin lahat ng categories
// POST   = mag-add ng bagong category
// DELETE = burahin ang category
// ============================================================

require_once __DIR__ . '/../config/database.php';
apiHeaders();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// --- Kunin lahat ng categories ---
if ($method === 'GET') {
    $cats = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
    jsonResponse(['success' => true, 'data' => $cats]);
}

// --- Mag-add ng bagong category ---
if ($method === 'POST') {
    $body = getBody();
    if (!empty($body['name'])) {
        $db->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$body['name']]);
        jsonResponse(['success' => true]);
    }
    jsonResponse(['success' => false, 'message' => 'Kulang ang pangalan'], 422);
}

// --- Burahin ang category ---
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}
