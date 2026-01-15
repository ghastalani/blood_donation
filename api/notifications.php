<?php
/**
 * api/notifications.php
 */

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once 'db.php';

$action = $_GET['action'] ?? '';

function cleanSendResponse($data, $statusCode = 200) {
    if (ob_get_length()) ob_clean(); 
    sendResponse($data, $statusCode);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['userId'] ?? null;
    if (!$userId) cleanSendResponse([]);

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    cleanSendResponse($stmt->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    
    if ($action === 'create_notification') {
        $id = generateUUID();
        $stmt = $pdo->prepare("INSERT INTO notifications (id, user_id, title_ar, title_en, message_ar, message_en, type, related_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $input['user_id'] ?? null,
            $input['title_ar'] ?? '',
            $input['title_en'] ?? '',
            $input['message_ar'] ?? '',
            $input['message_en'] ?? '',
            $input['type'] ?? 'info',
            $input['related_request_id'] ?? null
        ]);
        cleanSendResponse(["status" => "success", "id" => $id]);
    }

    if ($action === 'mark_read') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$input['id'] ?? null]);
        cleanSendResponse(["status" => "success"]);
    }
}
