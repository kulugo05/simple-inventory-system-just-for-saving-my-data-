<?php
require_once __DIR__ . '/../config/database.php';
apiHeaders();
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Inalis ang "as _id" para magtugma sa app.js
    $cats = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
    jsonResponse(['success' => true, 'data' => $cats]);
}

if ($method === 'POST') {
    $body = getBody();
    if(!empty($body['name'])) {
        $db->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$body['name']]);
        jsonResponse(['success' => true]);
    }
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}