<?php
require_once 'db.php';

$email = 'admin@gmail.com';
$password_raw = 'admin';
$password_hash = password_hash($password_raw, PASSWORD_BCRYPT);
$name = 'System Admin';
$role = 'admin';

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $userId = $existingUser['id'];
        // Update password just in case
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$password_hash, $userId]);
        
        // Ensure profile exists and has admin role
        $stmt = $pdo->prepare("SELECT id FROM profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        
        if ($profile) {
            $stmt = $pdo->prepare("UPDATE profiles SET role = 'admin', name = ? WHERE user_id = ?");
            $stmt->execute([$name, $userId]);
        } else {
            $profileId = generateUUID();
            $stmt = $pdo->prepare("INSERT INTO profiles (id, user_id, name, email, role, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$profileId, $userId, $name, $email, $role, '00000000']);
        }
        echo json_encode(["status" => "success", "message" => "Admin updated"]);
    } else {
        $userId = generateUUID();
        $profileId = generateUUID();

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $email, $password_hash]);

        $stmt = $pdo->prepare("INSERT INTO profiles (id, user_id, name, email, role, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$profileId, $userId, $name, $email, $role, '00000000']);

        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Admin created"]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
