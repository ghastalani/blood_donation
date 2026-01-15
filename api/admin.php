<?php
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_users') {
        try {
            $stmt = $pdo->query("
                SELECT p.*, u.is_banned 
                FROM profiles p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC
            ");
            $users = $stmt->fetchAll();
            sendResponse($users);
        } catch (Exception $e) {
            sendResponse(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    
    if ($action === 'update_user') {
        $profileId = $input['id'] ?? '';
        
        if (isset($input['is_banned'])) {
            try {
                // Get user_id from profile
                $stmt = $pdo->prepare("SELECT user_id FROM profiles WHERE id = ?");
                $stmt->execute([$profileId]);
                $profile = $stmt->fetch();
                
                if ($profile) {
                    $userId = $profile['user_id'];
                    $stmt = $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
                    $stmt->execute([$input['is_banned'] ? 1 : 0, $userId]);
                    
                    // Also update profile if needed or keep it in users for auth check
                    // The user said "Update the user record"
                    sendResponse(["status" => "success"]);
                } else {
                    sendResponse(["status" => "error", "message" => "Profile not found"], 404);
                }
            } catch (Exception $e) {
                sendResponse(["status" => "error", "message" => $e->getMessage()], 500);
            }
        }
    }
}
