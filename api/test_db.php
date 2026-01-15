<?php
require_once 'db.php';
try {
    $stmt = $pdo->query("SELECT 1");
    echo "Connection successful";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
