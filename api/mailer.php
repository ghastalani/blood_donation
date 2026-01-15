<?php
/**
 * api/mailer.php
 * Robust SMTP Mailer helper for the application.
 * Uses SMTP settings from config.php directly to ensure compatibility on WAMP.
 */

require_once 'config.php';

function sendBloodRequestEmail($recipientEmail, $requesterName, $bloodType) {
    // Log the email attempt
    error_log("=== sendBloodRequestEmail called ===");
    error_log("Recipient: $recipientEmail");
    
    if (!$recipientEmail) {
        return ['success' => false, 'message' => 'No recipient email provided'];
    }

    $subject = "VitalConnect Hero: You have a new blood request";
    
    // Email Content
    $body = "Hello,\n\n";
    $body .= "You have a new blood request – save a life!\n\n";
    $body .= "Request Details:\n";
    $body .= "----------------\n";
    $body .= "• Requester Name: " . $requesterName . "\n";
    $body .= "• Blood Type Needed: " . $bloodType . "\n\n";
    $body .= "Please login to your dashboard to view full details and respond:\n";
    $body .= "http://localhost/login\n\n";
    $body .= "Best regards,\n";
    $body .= "VitalConnect Team";

    // SMTP Protocol Implementation
    $host = str_replace('ssl://', '', SMTP_HOST);
    $port = SMTP_PORT;
    $user = base64_encode(SMTP_USER);
    $pass = base64_encode(SMTP_PASS);
    $from = SMTP_FROM_EMAIL;

    try {
        // Create SSL context for Gmail
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        // Connect with SSL
        $socket = stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            $msg = "SMTP Socket Error: $errstr ($errno)";
            error_log($msg);
            return ['success' => false, 'message' => $msg];
        }

        $getResponse = function($socket) {
            $response = "";
            while ($str = fgets($socket, 515)) {
                $response .= $str;
                if (substr($str, 3, 1) == " ") break;
            }
            error_log("SMTP Response: " . trim($response));
            return trim($response);
        };

        $getResponse($socket); // Connection greeting
        
        fwrite($socket, "EHLO localhost\r\n");
        $getResponse($socket);

        if (SMTP_USER && SMTP_PASS) {
            fwrite($socket, "AUTH LOGIN\r\n");
            $getResponse($socket);
            fwrite($socket, $user . "\r\n");
            $getResponse($socket);
            fwrite($socket, $pass . "\r\n");
            $auth_response = $getResponse($socket);
            
            // Check if authentication succeeded
            if (strpos($auth_response, '235') === false) {
                $msg = "SMTP Auth Failed: " . $auth_response;
                error_log($msg);
                fclose($socket);
                return ['success' => false, 'message' => $msg];
            }
        }

        fwrite($socket, "MAIL FROM: <" . SMTP_USER . ">\r\n");
        $mailResponse = $getResponse($socket);
        if (substr($mailResponse, 0, 1) != '2') {
            return ['success' => false, 'message' => "MAIL FROM failed: $mailResponse"];
        }
        
        fwrite($socket, "RCPT TO: <$recipientEmail>\r\n");
        $rcptResponse = $getResponse($socket);
        if (substr($rcptResponse, 0, 1) != '2') {
            return ['success' => false, 'message' => "RCPT TO failed: $rcptResponse"];
        }
        
        fwrite($socket, "DATA\r\n");
        $dataResponse = $getResponse($socket);
        if (substr($dataResponse, 0, 1) != '3') {
            return ['success' => false, 'message' => "DATA failed: $dataResponse"];
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=utf-8\r\n";
        $headers .= "To: <$recipientEmail>\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . SMTP_USER . "\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        fwrite($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $sendResponse = $getResponse($socket);
        if (substr($sendResponse, 0, 1) != '2') {
            return ['success' => false, 'message' => "Body send failed: $sendResponse"];
        }

        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        error_log("Mailer Success: Email sent to $recipientEmail");
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        error_log("Mailer Exception: " . $e->getMessage());
        return ['success' => false, 'message' => "Exception: " . $e->getMessage()];
    }
}
