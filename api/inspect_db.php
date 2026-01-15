<?php
require_once 'db.php';

try {
    $stmt = $pdo->query("SHOW CREATE TABLE profiles");
    $profilesSchema = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $data = json_encode([
        "profiles" => $profilesSchema,
        "tables" => $tables
    ], JSON_PRETTY_PRINT);
    
    file_put_contents('inspect_output.json', $data);
    echo "Output written to inspect_output.json";
} catch (Exception $e) {
    file_put_contents('inspect_output.json', json_encode(["error" => $e->getMessage()]));
    echo "Error occurred";
}
