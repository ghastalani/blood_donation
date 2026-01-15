<?php
require_once 'db.php';

$fp = fopen('users_dump.txt', 'w');
fwrite($fp, sprintf("%-36s | %-20s | %-10s | %-30s | %-30s | %-5s\n", "ID (Profile)", "Name", "Role", "Profile Email", "User Email", "Match"));
fwrite($fp, str_repeat("-", 140) . "\n");

$stmt = $pdo->query("SELECT p.id as pid, p.name, p.role, p.email as p_email, p.user_id, u.email as u_email 
                     FROM profiles p 
                     LEFT JOIN users u ON p.user_id = u.id");

while ($row = $stmt->fetch()) {
    $match = ($row['p_email'] === $row['u_email']) ? 'YES' : 'NO';
    fwrite($fp, sprintf("%-36s | %-20s | %-10s | %-30s | %-30s | %-5s\n", 
        $row['pid'], 
        substr($row['name'], 0, 20), 
        $row['role'], 
        $row['p_email'], 
        $row['u_email'], 
        $match
    ));
}
fclose($fp);
echo "Dumped to users_dump.txt\n";
?>
