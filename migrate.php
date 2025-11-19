<?php

require_once __DIR__ . '/src/DB.php';

use Hospital\DB;

$pdo = DB::getPDO();

// Apply all .sql files in migrations/ in alphabetical order
$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);
try {
    foreach ($files as $f) {
        $sql = file_get_contents($f);
        if ($sql) {
            $pdo->exec($sql);
            echo "Applied migration: " . basename($f) . "\n";
        }
    }
    echo "Migration applied, database initialized at: " . DB::getPath() . "\n";

    // ensure a default admin user exists
    try {
        $stmt = $pdo->prepare('SELECT count(*) as c FROM users');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false || (int)$row['c'] === 0) {
            $password = password_hash('adminpass', PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (username,password_hash,role) VALUES (:u,:p,:r)')
                ->execute([':u' => 'admin', ':p' => $password, ':r' => 'admin']);
            echo "Created default admin user: username=admin password=adminpass\n";
        }
    } catch (Exception $e) {
        // ignore if users table doesn't exist yet
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
