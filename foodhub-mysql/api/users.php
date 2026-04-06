<?php
require_once __DIR__ . '/../config/database.php';
apiHeaders();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$body   = getBody();

// --- Kunin lahat ng users ---
if ($method === 'GET') {
    $users = $db->query("
        SELECT id, username, full_name, role, is_active, last_login, created_at
        FROM users
        ORDER BY created_at DESC
    ")->fetchAll();
    jsonResponse(['success' => true, 'data' => $users]);
}

// --- Gumawa ng bagong account ---
if ($method === 'POST') {
    // Siguraduhing kumpleto ang data
    if (empty($body['username']) || empty($body['password']) || empty($body['fullname'])) {
        jsonResponse(['success' => false, 'message' => 'Punan ang lahat!'], 422);
    }

    // I-check kung existing na ang username
    $check = $db->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([trim($body['username'])]);
    if ($check->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Username already exists!'], 409);
    }

    // I-hash ang password bago i-save (hindi pwedeng plain text)
    $hash = password_hash($body['password'], PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?,?,?,?)")
       ->execute([trim($body['username']), $hash, trim($body['fullname']), $body['role'] ?? 'staff']);

    jsonResponse(['success' => true, 'message' => 'Account created!']);
}

// --- I-reset ang password ng user ---
if ($method === 'PUT') {
    if (empty($body['id']) || empty($body['password'])) {
        jsonResponse(['success' => false, 'message' => 'Missing data'], 422);
    }
    // Mag-hash ng bagong password bago i-save
    $hash = password_hash($body['password'], PASSWORD_DEFAULT);
    $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, (int)$body['id']]);
    jsonResponse(['success' => true, 'message' => 'Password reset!']);
}

// --- I-toggle ang active/inactive status ng user ---
if ($method === 'PATCH') {
    if (!isset($body['id']) || !isset($body['is_active'])) {
        jsonResponse(['success' => false, 'message' => 'Missing data'], 422);
    }
    $db->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([(int)$body['is_active'], (int)$body['id']]);
    jsonResponse(['success' => true, 'message' => 'User updated!']);
}
