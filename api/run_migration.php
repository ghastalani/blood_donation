<?php
require_once 'db.php';

try {
    // Read the migration file
    $sql = file_get_contents('../database/migration_accept_workflow.sql');
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo json_encode(["status" => "success", "message" => "Migration applied successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
