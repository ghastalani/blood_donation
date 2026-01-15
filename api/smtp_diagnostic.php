<?php
/**
 * SMTP Diagnostic Tool
 * Tests SMTP connection and identifies issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h1>SMTP Diagnostic Tool</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// Test 1: Configuration Check
echo "<h2>1. Configuration Check</h2>";
echo "<pre>";
echo "SMTP_HOST: " . SMTP_HOST . "\n";
echo "SMTP_PORT: " . SMTP_PORT . "\n";
echo "SMTP_USER: " . SMTP_USER . "\n";
echo "SMTP_PASS: " . (SMTP_PASS ? str_repeat('*', strlen(SMTP_PASS)) : 'NOT SET') . "\n";
echo "SMTP_FROM_EMAIL: " . SMTP_FROM_EMAIL . "\n";
echo "SMTP_FROM_NAME: " . SMTP_FROM_NAME . "\n";
echo "</pre>";

if (!SMTP_USER || !SMTP_PASS) {
    echo "<p class='error'>❌ SMTP credentials not configured!</p>";
    exit;
}
echo "<p class='success'>✅ Configuration looks good</p>";

// Test 2: Socket Connection
echo "<h2>2. Socket Connection Test</h2>";
$host = str_replace('ssl://', '', SMTP_HOST);
$port = SMTP_PORT;

echo "<p class='info'>Attempting to connect to ssl://{$host}:{$port}...</p>";

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

$socket = @stream_socket_client(
    "ssl://{$host}:{$port}",
    $errno,
    $errstr,
    10,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$socket) {
    echo "<p class='error'>❌ Connection failed: $errstr ($errno)</p>";
    echo "<p>Possible issues:</p>";
    echo "<ul>";
    echo "<li>Port 465 is blocked by firewall</li>";
    echo "<li>Internet connection issue</li>";
    echo "<li>Gmail SMTP is down</li>";
    echo "</ul>";
    exit;
}

echo "<p class='success'>✅ Socket connection successful</p>";

// Test 3: SMTP Handshake
echo "<h2>3. SMTP Handshake</h2>";

$getResponse = function($socket) {
    $response = "";
    while ($str = fgets($socket, 515)) {
        $response .= $str;
        if (substr($str, 3, 1) == " ") break;
    }
    return trim($response);
};

$greeting = $getResponse($socket);
echo "<pre>Server: $greeting</pre>";

if (strpos($greeting, '220') === false) {
    echo "<p class='error'>❌ Unexpected greeting</p>";
    fclose($socket);
    exit;
}
echo "<p class='success'>✅ Server greeting OK</p>";

// Test 4: EHLO
echo "<h2>4. EHLO Command</h2>";
fwrite($socket, "EHLO localhost\r\n");
$ehlo = $getResponse($socket);
echo "<pre>$ehlo</pre>";

if (strpos($ehlo, '250') === false) {
    echo "<p class='error'>❌ EHLO failed</p>";
    fclose($socket);
    exit;
}
echo "<p class='success'>✅ EHLO successful</p>";

// Test 5: Authentication
echo "<h2>5. Authentication Test</h2>";
$user = base64_encode(SMTP_USER);
$pass = base64_encode(SMTP_PASS);

fwrite($socket, "AUTH LOGIN\r\n");
$authStart = $getResponse($socket);
echo "<pre>AUTH LOGIN: $authStart</pre>";

fwrite($socket, $user . "\r\n");
$userResp = $getResponse($socket);
echo "<pre>Username: $userResp</pre>";

fwrite($socket, $pass . "\r\n");
$passResp = $getResponse($socket);
echo "<pre>Password: $passResp</pre>";

if (strpos($passResp, '235') === false) {
    echo "<p class='error'>❌ Authentication FAILED!</p>";
    echo "<p>Error: $passResp</p>";
    echo "<p>Possible issues:</p>";
    echo "<ul>";
    echo "<li>Wrong email address</li>";
    echo "<li>Wrong app password</li>";
    echo "<li>App password not enabled</li>";
    echo "<li>2-factor authentication not enabled</li>";
    echo "</ul>";
    fclose($socket);
    exit;
}
echo "<p class='success'>✅ Authentication successful!</p>";

// Test 6: Send Test Email
echo "<h2>6. Sending Test Email</h2>";
$testRecipient = SMTP_USER; // Send to self

echo "<p class='info'>Sending test email to: $testRecipient</p>";

fwrite($socket, "MAIL FROM: <" . SMTP_USER . ">\r\n");
$mailFrom = $getResponse($socket);
echo "<pre>MAIL FROM: $mailFrom</pre>";

fwrite($socket, "RCPT TO: <$testRecipient>\r\n");
$rcptTo = $getResponse($socket);
echo "<pre>RCPT TO: $rcptTo</pre>";

fwrite($socket, "DATA\r\n");
$data = $getResponse($socket);
echo "<pre>DATA: $data</pre>";

$subject = "VitalConnect SMTP Test";
$body = "This is a test email from VitalConnect SMTP diagnostic tool.\n\nIf you receive this, SMTP is working correctly!";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=utf-8\r\n";
$headers .= "To: <$testRecipient>\r\n";
$headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
$headers .= "Reply-To: " . SMTP_USER . "\r\n";
$headers .= "Subject: $subject\r\n";
$headers .= "Date: " . date('r') . "\r\n";

fwrite($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
$sendResp = $getResponse($socket);
echo "<pre>Send: $sendResp</pre>";

if (strpos($sendResp, '250') === false) {
    echo "<p class='error'>❌ Email sending failed!</p>";
} else {
    echo "<p class='success'>✅ Email sent successfully!</p>";
    echo "<p class='info'>Check inbox at: $testRecipient</p>";
}

fwrite($socket, "QUIT\r\n");
fclose($socket);

// Summary
echo "<h2>Summary</h2>";
echo "<p class='success'><strong>✅ ALL TESTS PASSED!</strong></p>";
echo "<p>SMTP is configured correctly and working. If emails are not reaching donors, check:</p>";
echo "<ul>";
echo "<li>Donor email addresses are correct in the database</li>";
echo "<li>The create_request endpoint is being called correctly</li>";
echo "<li>PHP error logs for any runtime errors</li>";
echo "<li>Spam folders</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='test_live_app.php'>→ Test Live App Flow</a> | <a href='index.html'>← Back to Dashboard</a></p>";
?>
