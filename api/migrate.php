<?php
require_once 'db.php';

try {
    echo "Starting migration...\n";
    
    // 1. Drop foreign key if it exists
    try {
        echo "Dropping foreign key profiles_ibfk_2...\n";
        $pdo->exec("ALTER TABLE profiles DROP FOREIGN KEY profiles_ibfk_2");
        echo "Foreign key dropped.\n";
    } catch (Exception $e) {
        echo "Note: profiles_ibfk_2 could not be dropped: " . $e->getMessage() . "\n";
    }

    // 2. Modify city_id column
    echo "Modifying column city_id...\n";
    $pdo->exec("ALTER TABLE profiles MODIFY COLUMN city_id VARCHAR(255)");
    echo "Column modified.\n";

    // 3. Clear city_id column
    echo "Clearning city_id column...\n";
    $pdo->exec("UPDATE profiles SET city_id = NULL");
    echo "Column cleared.\n";

    // 4. Drop cities table
    echo "Dropping cities table...\n";
    $pdo->exec("DROP TABLE IF EXISTS cities");
    echo "Cities table dropped.\n";

    echo "Migration successful!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
