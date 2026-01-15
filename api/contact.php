<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'get_messages') {
        $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll();
        sendResponse($messages);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    $input = getJsonInput();
    
    if ($action === 'mark_read') {
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$input['id']]);
        sendResponse(["status" => "success"]);
    } else {
        try {
            $id = generateUUID();
            $stmt = $pdo->prepare("INSERT INTO contact_messages (id, name, email, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $input['name'],
                $input['email'],
                $input['message']
            ]);
            sendResponse(["status" => "success", "id" => $id]);
        } catch (Exception $e) {
            sendResponse(["status" => "error", "message" => $e->getMessage()], 400);
        }
    }
}
