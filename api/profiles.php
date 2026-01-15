<?php
/**
 * api/profiles.php
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
    if ($action === 'get_profile') {
        $userId = $_GET['userId'] ?? null;
        if (!$userId) cleanSendResponse(null, 400);

        $stmt = $pdo->prepare("
            SELECT p.*, u.is_banned 
            FROM profiles p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.user_id = ?
        ");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        if ($profile && !$profile['is_available'] && $profile['cooldown_end_date']) {
            if (strtotime($profile['cooldown_end_date']) <= time()) {
                $stmt = $pdo->prepare("UPDATE profiles SET is_available = 1 WHERE id = ?");
                $stmt->execute([$profile['id']]);
                $profile['is_available'] = 1;
            }
        }
        cleanSendResponse($profile);
    }

    if ($action === 'search_donors') {
        $bloodType = $_GET['bloodType'] ?? '';
        // Robustness: Handle '+' being converted to space in query parameters
        $bloodType = str_replace(' ', '+', $bloodType);
        $cityId = $_GET['cityId'] ?? '';
        
        $query = "SELECT p.*, u.is_banned 
                  FROM profiles p 
                  JOIN users u ON p.user_id = u.id
                  WHERE p.role = 'donor' AND u.is_banned = 0 
                  AND (p.is_available = 1 OR (p.cooldown_end_date IS NOT NULL AND p.cooldown_end_date <= CURRENT_TIMESTAMP))";
        $params = [];

        if ($bloodType) {
            $query .= " AND blood_type = ?";
            $params[] = $bloodType;
        }
        if ($cityId) {
            $query .= " AND city_id = ?";
            $params[] = $cityId;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        cleanSendResponse($stmt->fetchAll());
    }
    
    // Simple endpoint to get all users by role (for testing)
    $role = $_GET['role'] ?? '';
    if ($role) {
        $stmt = $pdo->prepare("
            SELECT p.*, u.is_banned 
            FROM profiles p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.role = ? AND u.is_banned = 0 
            LIMIT 50
        ");
        $stmt->execute([$role]);
        cleanSendResponse($stmt->fetchAll());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    if ($action === 'update_profile') {
        $profileId = $input['id'] ?? null;
        if (!$profileId) {
            cleanSendResponse(["status" => "error", "message" => "Missing profile ID"], 400);
        }
        $fields = ['name', 'phone', 'city_id', 'blood_type', 'is_available'];
        $updates = [];
        $params = [];
        
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (!empty($updates)) {
            $params[] = $profileId;
            $stmt = $pdo->prepare("UPDATE profiles SET " . implode(', ', $updates) . " WHERE id = ?");
            $stmt->execute($params);
            cleanSendResponse(["status" => "success"]);
        } else {
            cleanSendResponse(["status" => "success", "message" => "No changes"]);
        }
    }
}
