<?php
/**
 * api/requests.php
 * Robust API for donation requests.
 */

// Start output buffering to catch any accidental output/warnings
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide errors from output to keep JSON valid

require_once 'db.php';

// Force JSON header immediately
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$input = getJsonInput();

// Verify action exists
if (!$action) {
    sendResponse(["status" => "error", "message" => "No action specified"], 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_requests') {
        $profileId = $_GET['profileId'] ?? null;
        $role = $_GET['role'] ?? ''; 
        
        if (!$profileId) {
            sendResponse(["status" => "error", "message" => "Missing profileId"], 400);
        }

        $column = ($role === 'donor') ? 'donor_id' : 'requester_id';
        $other_table = ($role === 'donor') ? 'requester_id' : 'donor_id';

        try {
            $stmt = $pdo->prepare("
                SELECT r.*, p.name as other_name, p.phone as other_phone, p.email as other_email, p.blood_type as other_blood_type
                FROM donation_requests r
                JOIN profiles p ON r.$other_table = p.id
                WHERE r.$column = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$profileId]);
            sendResponse($stmt->fetchAll());
        } catch (Exception $e) {
            sendResponse(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create_request' || $action === 'create_request_v2') {
        $requester_id = $input['requester_id'] ?? null;
        $donor_id = $input['donor_id'] ?? null;
        $message = htmlspecialchars($input['message'] ?? '', ENT_QUOTES, 'UTF-8');

        if (!$requester_id || !$donor_id) {
            sendResponse(["status" => "error", "message" => "Missing IDs"], 400);
        }

        try {
            $pdo->beginTransaction();

            // 1. Check if requester is banned
            $stmt = $pdo->prepare("SELECT u.is_banned FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
            $stmt->execute([$requester_id]);
            $reqUser = $stmt->fetch();
            if ($reqUser && $reqUser['is_banned']) {
                $pdo->rollBack();
                sendResponse(["status" => "error", "message" => "Your account is banned. You cannot send requests."], 403);
            }

            // 2. Check if donor is banned
            $stmt = $pdo->prepare("SELECT u.is_banned FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
            $stmt->execute([$donor_id]);
            $donUser = $stmt->fetch();
            if ($donUser && $donUser['is_banned']) {
                $pdo->rollBack();
                sendResponse(["status" => "error", "message" => "This donor is no longer available."], 403);
            }

            $id = generateUUID();
            $stmt = $pdo->prepare("INSERT INTO donation_requests (id, requester_id, donor_id, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $requester_id, $donor_id, $message]);

            // Get Requester Info (from profiles for display name)
            $stmt = $pdo->prepare("SELECT name, blood_type, city_id FROM profiles WHERE id = ?");
            $stmt->execute([$requester_id]);
            $req = $stmt->fetch();
            $requester_name = $req ? $req['name'] : 'Someone';
            $requester_blood = $req ? $req['blood_type'] : 'N/A';
            
            // Get Donor Email from USERS table (as requested)
            $stmt = $pdo->prepare("
                SELECT u.email 
                FROM profiles p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$donor_id]);
            $donor_user = $stmt->fetch();
            $donor_email = $donor_user ? $donor_user['email'] : null;

            $notif_id = generateUUID();
            $stmt = $pdo->prepare("INSERT INTO notifications (id, user_id, title_ar, title_en, message_ar, message_en, type, related_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $notif_id, $donor_id, 'طلب دم جديد', 'New Blood Request',
                "لديك طلب جديد - أنقذ حياة! ($requester_name)", "You have a new request – save a life! ($requester_name)",
                'request', $id
            ]);

            $pdo->commit();

            // Notify via email using helper - ALWAYS attempt to send
            $emailResult = ['success' => false, 'message' => 'No donor email found'];
            
            if ($donor_email) {
                error_log("VitalConnect: Attempting to send email to donor: $donor_email");
                error_log("VitalConnect: Requester name: $requester_name, Blood type: $requester_blood");
                
                require_once 'mailer.php';
                $emailResult = sendBloodRequestEmail($donor_email, $requester_name, $requester_blood);
                
                if ($emailResult['success']) {
                    error_log("VitalConnect: Email sent successfully to $donor_email");
                } else {
                    error_log("VitalConnect: Email FAILED to send to $donor_email - " . $emailResult['message']);
                }
            } else {
                error_log("VitalConnect: No donor email found for donor_id: $donor_id");
            }

            error_log("Debug: emailResult: " . print_r($emailResult, true));

            sendResponse([
                "status" => "success", 
                "id" => $id, 
                "email_sent" => $emailResult['success'],
                "email_message" => $emailResult['message'],
                "debug" => "Active " . date('H:i:s')
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            sendResponse(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }

    if ($action === 'update_status') {
        $requestId = $input['id'] ?? null;
        $status = $input['status'] ?? 'pending';

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE donation_requests SET status = ? WHERE id = ?");
            $stmt->execute([$status, $requestId]);

            if ($status === 'rejected') {
                $stmt = $pdo->prepare("SELECT requester_id FROM donation_requests WHERE id = ?");
                $stmt->execute([$requestId]);
                $req = $stmt->fetch();
                if ($req) {
                    $notif_id = generateUUID();
                    $stmt = $pdo->prepare("INSERT INTO notifications (id, user_id, title_ar, title_en, message_ar, message_en, type, related_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$notif_id, $req['requester_id'], 'تم رفض الطلب', 'Request Rejected', 'تم رفض طلبك.', 'Your request was rejected.', 'info', $requestId]);
                }
            }
            $pdo->commit();
            sendResponse(["status" => "success"]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            sendResponse(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }

    if ($action === 'accept_request') {
        // Log for internal tracing (visible in PHP error logs if needed)
        error_log("Accepting request flow started");

        $requestId = $input['id'] ?? ($_POST['id'] ?? null);
        $donorId   = $input['donor_id'] ?? ($_POST['donor_id'] ?? null);

        if (!$requestId || !$donorId) {
            sendResponse(["status" => "error", "message" => "Missing request ID or donor ID"], 400);
        }

        try {
            $pdo->beginTransaction();
            
            // 0. Check if donor is banned
            $stmt = $pdo->prepare("SELECT u.is_banned FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
            $stmt->execute([$donorId]);
            $donUser = $stmt->fetch();
            if ($donUser && $donUser['is_banned']) {
                $pdo->rollBack();
                sendResponse(["status" => "error", "message" => "Your account is banned. You cannot accept requests."], 403);
            }

            // Check request existence and status
            $stmt = $pdo->prepare("SELECT status, requester_id FROM donation_requests WHERE id = ? FOR UPDATE");
            $stmt->execute([$requestId]);
            $req = $stmt->fetch();

            if (!$req) {
                $pdo->rollBack();
                sendResponse(["status" => "error", "message" => "Request not found"], 404);
            }

            if ($req['status'] !== 'pending') {
                $pdo->rollBack();
                sendResponse(["status" => "error", "message" => "Request already processed or not pending"], 400);
            }

            // Update main request status
            $stmt = $pdo->prepare("UPDATE donation_requests SET status = 'accepted' WHERE id = ?");
            $stmt->execute([$requestId]);

            // Track accepted donation
            $accepted_id = generateUUID();
            $stmt = $pdo->prepare("INSERT INTO accepted_donations (id, donor_id, request_id) VALUES (?, ?, ?)");
            $stmt->execute([$accepted_id, $donorId, $requestId]);

            // Set donor cooldown
            $cooldown = date('Y-m-d H:i:s', strtotime('+90 days'));
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE profiles SET is_available = 0, last_donation_date = ?, cooldown_end_date = ? WHERE id = ?");
            $stmt->execute([$now, $cooldown, $donorId]);

            // Auto-reject other pending requests for this donor
            $stmt = $pdo->prepare("UPDATE donation_requests SET status = 'rejected' WHERE donor_id = ? AND status = 'pending' AND id != ?");
            $stmt->execute([$donorId, $requestId]);

            // Notify requester
            $stmt = $pdo->prepare("SELECT name, phone, email FROM profiles WHERE id = ?");
            $stmt->execute([$donorId]);
            $donorInfo = $stmt->fetch();

            if ($donorInfo) {
                $notif_id = generateUUID();
                $stmt = $pdo->prepare("INSERT INTO notifications (id, user_id, title_ar, title_en, message_ar, message_en, type, related_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $notif_id, $req['requester_id'], 'تم قبول الطلب!', 'Request Accepted!',
                    "تم قبول طلبك من {$donorInfo['name']}. التواصل: {$donorInfo['phone']} | {$donorInfo['email']}",
                    "Your request was accepted by {$donorInfo['name']}. Contact: {$donorInfo['phone']} | {$donorInfo['email']}",
                    'success', $requestId
                ]);
            }

            $pdo->commit();
            sendResponse(["status" => "success", "message" => "accepted"]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            sendResponse(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }
}

// If no action matched or fallthrough
sendResponse(["status" => "error", "message" => "Invalid action or method"], 404);