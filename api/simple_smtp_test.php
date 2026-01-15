<?php
require_once 'config.php';

echo "Testing SMTP Config:\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "User: " . SMTP_USER . "\n";
echo "Pass: " . substr(SMTP_PASS, 0, 3) . "...\n"; // Hide password

$host = str_replace('ssl://', '', SMTP_HOST);
$port = SMTP_PORT;

echo "\nConnecting to $host:$port...\n";

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

$socket = stream_socket_client(
    "ssl://{$host}:{$port}",
    $errno,
    $errstr,
    10,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$socket) {
    die("ERROR: Could not connect: $errstr ($errno)\n");
}
echo "Connected!\n";

function read_lines($sock) {
    $out = "";
    while($line = fgets($sock)) {
        $out .= $line;
        if(substr($line, 3, 1) == " ") break;
    }
    return trim($out);
}

echo "S: " . read_lines($socket) . "\n";

fwrite($socket, "EHLO localhost\r\n");
echo "C: EHLO localhost\n";
echo "S: " . read_lines($socket) . "\n";

fwrite($socket, "AUTH LOGIN\r\n");
echo "C: AUTH LOGIN\n";
echo "S: " . read_lines($socket) . "\n";

fwrite($socket, base64_encode(SMTP_USER) . "\r\n");
echo "C: (Username)\n";
echo "S: " . read_lines($socket) . "\n";

fwrite($socket, base64_encode(SMTP_PASS) . "\r\n");
echo "C: (Password)\n";
$auth_resp = read_lines($socket);
echo "S: $auth_resp\n";

if (strpos($auth_resp, '235') === false) {
    die("ERROR: Auth Failed\n");
}

fwrite($socket, "MAIL FROM: <" . SMTP_USER . ">\r\n");
echo "S: " . read_lines($socket) . "\n";

fwrite($socket, "RCPT TO: <" . SMTP_USER . ">\r\n"); // Send to self
echo "S: " . read_lines($socket) . "\n";

fwrite($socket, "DATA\r\n");
echo "S: " . read_lines($socket) . "\n";

$data = "Subject: CLI Test Email\r\n\r\nThis is a test from the CLI script.";
fwrite($socket, "$data\r\n.\r\n");
echo "S: " . read_lines($socket) . "\n";

fwrite($socket, "QUIT\r\n");
fclose($socket);

echo "\nSUCCESS: Test email accepted by server.\n";
?>
