<?php
/**
 * api/auth.php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();

    if ($action === 'signup') {
        $email = $input['email'] ?? '';
        $password_raw = $input['password'] ?? '';
        $password = password_hash($password_raw, PASSWORD_BCRYPT);
        $metadata = $input['metadata'] ?? [];
        
        $userId = generateUUID();
        $profileId = generateUUID();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $email, $password]);

            $stmt = $pdo->prepare("INSERT INTO profiles (id, user_id, name, phone, email, city_id, role, blood_type, nni) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $profileId,
                $userId,
                $metadata['name'] ?? '',
                $metadata['phone'] ?? '',
                $email,
                $metadata['city_id'] ?? null,
                $metadata['role'] ?? 'donor',
                $metadata['blood_type'] ?? null,
                $metadata['nni'] ?? null
            ]);

            $stmt = $pdo->prepare("SELECT * FROM profiles WHERE id = ?");
            $stmt->execute([$profileId]);
            $profile = $stmt->fetch();

            $pdo->commit();
            cleanSendResponse([
                "status" => "success", 
                "user" => ["id" => $userId, "email" => $email],
                "profile" => $profile
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            cleanSendResponse(["status" => "error", "message" => $e->getMessage()], 400);
        }
    }

    if ($action === 'signin') {
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_banned']) {
                cleanSendResponse(["status" => "error", "message" => "Your account is banned. Please contact the admin."], 403);
            }

            $stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch();
            
            cleanSendResponse([
                "status" => "success", 
                "user" => ["id" => $user['id'], "email" => $user['email'], "is_banned" => $user['is_banned']],
                "profile" => $profile
            ]);
        } else {
            cleanSendResponse(["status" => "error", "message" => "Invalid credentials"], 401);
        }
    }
}
