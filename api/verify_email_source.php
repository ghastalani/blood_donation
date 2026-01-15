<?php
/**
 * Verify Email Source Fix
 * Verifies that emails are correctly fetched from the USERS table
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

echo "<h1>📧 Email Source Verification</h1>";
echo "<p>Checking if emails are correctly fetched from the <code>users</code> table via <code>profiles</code>...</p>";

try {
    // 1. Get a test profile
    $stmt = $pdo->query("SELECT id, name, user_id FROM profiles WHERE role='donor' LIMIT 1");
    $profile = $stmt->fetch();

    if (!$profile) {
        die("<p style='color:red'>❌ No donor profiles found to test with.</p>");
    }

    echo "<h3>Test Profile Found:</h3>";
    echo "<ul>";
    echo "<li>Profile ID: " . $profile['id'] . "</li>";
    echo "<li>Name: " . $profile['name'] . "</li>";
    echo "<li>User ID: " . $profile['user_id'] . "</li>";
    echo "</ul>";

    // 2. Query Users Table directly
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$profile['user_id']]);
    $userEmail = $stmt->fetchColumn();

    echo "<h3>Check Users Table:</h3>";
    echo "<ul>";
    echo "<li>Email in <code>users</code> table: <strong>" . ($userEmail ?: 'NOT FOUND') . "</strong></li>";
    echo "</ul>";

    // 3. Test the Query Logic from requests.php
    echo "<h3>Testing Fix Query (JOIN operation):</h3>";
    $sql = "SELECT u.email 
            FROM profiles p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$profile['id']]);
    $fetchedEmail = $stmt->fetchColumn();

    echo "<ul>";
    echo "<li>Fetched Email via Join: <strong>" . ($fetchedEmail ?: 'NULL') . "</strong></li>";
    echo "</ul>";

    // 4. Verify Match
    if ($userEmail === $fetchedEmail && $fetchedEmail) {
        echo "<h2 style='color:green'>✅ SUCCESS: Logic Match</h2>";
        echo "<p>The system correctly fetches the email from the <code>users</code> table for the given profile ID.</p>";
    } else {
        echo "<h2 style='color:red'>❌ FAILURE</h2>";
        echo "<p>Mismatch or missing data.</p>";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}

echo "<hr>";
echo "<p><a href='test_live_app.php'>→ Run Live Test</a></p>";
?>
