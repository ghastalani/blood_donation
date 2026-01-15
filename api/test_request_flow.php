<!DOCTYPE html>
<html>
<head>
    <title>Blood Request Flow Test - VitalConnect</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #2196F3; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>🩸 Blood Request Flow Test</h1>
    <p>This test simulates the complete flow of creating a blood request and sending email notification to the donor.</p>
    
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    require_once 'db.php';
    require_once 'mailer.php';
    
    // Step 1: Find a test donor and requester
    echo '<div class="step">';
    echo '<h2>Step 1: Finding Test Users</h2>';
    
    try {
        // Find a donor
        $stmt = $pdo->prepare("SELECT id, name, email, blood_type FROM profiles WHERE role = 'donor' AND is_available = 1 LIMIT 1");
        $stmt->execute();
        $donor = $stmt->fetch();
        
        // Find a requester
        $stmt = $pdo->prepare("SELECT id, name, email, blood_type FROM profiles WHERE role = 'requester' LIMIT 1");
        $stmt->execute();
        $requester = $stmt->fetch();
        
        if ($donor && $requester) {
            echo '<div class="success">';
            echo '<strong>✅ Test users found:</strong><br>';
            echo '<strong>Donor:</strong> ' . htmlspecialchars($donor['name']) . ' (' . $donor['email'] . ') - Blood Type: ' . $donor['blood_type'] . '<br>';
            echo '<strong>Requester:</strong> ' . htmlspecialchars($requester['name']) . ' (' . $requester['email'] . ') - Blood Type: ' . $requester['blood_type'];
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong> Could not find test users in database.<br>';
            echo 'Donor found: ' . ($donor ? 'Yes' : 'No') . '<br>';
            echo 'Requester found: ' . ($requester ? 'Yes' : 'No');
            echo '</div>';
            exit;
        }
    } catch (Exception $e) {
        echo '<div class="error"><strong>❌ Database Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        exit;
    }
    echo '</div>';
    
    // Step 2: Create a blood request
    echo '<div class="step">';
    echo '<h2>Step 2: Creating Blood Request</h2>';
    
    try {
        $pdo->beginTransaction();
        
        $requestId = generateUUID();
        $message = 'Test blood request - Email notification test at ' . date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("INSERT INTO donation_requests (id, requester_id, donor_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$requestId, $requester['id'], $donor['id'], $message]);
        
        echo '<div class="success">';
        echo '<strong>✅ Blood request created successfully</strong><br>';
        echo 'Request ID: ' . $requestId . '<br>';
        echo 'Message: ' . htmlspecialchars($message);
        echo '</div>';
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo '<div class="error"><strong>❌ Error creating request:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        exit;
    }
    echo '</div>';
    
    // Step 3: Create notification in database
    echo '<div class="step">';
    echo '<h2>Step 3: Creating Database Notification</h2>';
    
    try {
        $notifId = generateUUID();
        $stmt = $pdo->prepare("INSERT INTO notifications (id, user_id, title_ar, title_en, message_ar, message_en, type, related_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $notifId,
            $donor['id'],
            'طلب دم جديد',
            'New Blood Request',
            "لديك طلب جديد - أنقذ حياة! (" . $requester['name'] . ")",
            "You have a new request – save a life! (" . $requester['name'] . ")",
            'request',
            $requestId
        ]);
        
        $pdo->commit();
        
        echo '<div class="success">';
        echo '<strong>✅ Notification created in database</strong><br>';
        echo 'Notification ID: ' . $notifId;
        echo '</div>';
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo '<div class="error"><strong>❌ Error creating notification:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        exit;
    }
    echo '</div>';
    
    // Step 4: Send email notification
    echo '<div class="step">';
    echo '<h2>Step 4: Sending Email Notification</h2>';
    
    echo '<div class="info">';
    echo '<strong>Email will be sent to:</strong> ' . $donor['email'] . '<br>';
    echo '<strong>Requester Name:</strong> ' . htmlspecialchars($requester['name']) . '<br>';
    echo '<strong>Blood Type Needed:</strong> ' . $requester['blood_type'];
    echo '</div>';
    
    $emailResult = sendBloodRequestEmail($donor['email'], $requester['name'], $requester['blood_type']);
    
    if ($emailResult) {
        echo '<div class="success">';
        echo '<strong>✅ EMAIL SENT SUCCESSFULLY!</strong><br>';
        echo 'The donor should receive an email notification at: ' . $donor['email'] . '<br>';
        echo 'Please check the inbox (and spam folder).';
        echo '</div>';
    } else {
        echo '<div class="error">';
        echo '<strong>❌ EMAIL FAILED TO SEND</strong><br>';
        echo 'The request was created in the database, but the email notification failed.<br>';
        echo 'Check your SMTP configuration and error logs.';
        echo '</div>';
    }
    echo '</div>';
    
    // Summary
    echo '<div class="step">';
    echo '<h2>📊 Test Summary</h2>';
    
    if ($emailResult) {
        echo '<div class="success">';
        echo '<strong>✅ ALL TESTS PASSED!</strong><br><br>';
        echo 'The complete flow is working:<br>';
        echo '1. ✅ Blood request created in database<br>';
        echo '2. ✅ Notification stored in database<br>';
        echo '3. ✅ Email sent to donor<br><br>';
        echo '<strong>The feature is ready for production use!</strong>';
        echo '</div>';
    } else {
        echo '<div class="warning">';
        echo '<strong>⚠️ PARTIAL SUCCESS</strong><br><br>';
        echo '1. ✅ Blood request created in database<br>';
        echo '2. ✅ Notification stored in database<br>';
        echo '3. ❌ Email failed to send<br><br>';
        echo '<strong>Action needed:</strong> Check SMTP configuration and credentials.';
        echo '</div>';
    }
    echo '</div>';
    ?>
    
    <hr>
    <p>
        <a href="test_mail.php">← Back to Simple Email Test</a> | 
        <a href="javascript:location.reload()">🔄 Run Test Again</a>
    </p>
</body>
</html>
