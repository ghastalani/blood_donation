<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'mailer.php';

echo "<h1>Web Mail Test</h1>";
echo "<p>User: " . SMTP_USER . "</p>";

$result = sendBloodRequestEmail(SMTP_USER, "Test Requester WEB", "AB+");

if ($result) {
    echo "<h2 style='color:green'>SUCCESS: Email sent via WEB</h2>";
} else {
    echo "<h2 style='color:red'>FAILURE: Email returned false</h2>";
    echo "<p>Check error logs.</p>";
}
?>
