<?php
require_once __DIR__ . '/../src/DB.php';
use Hospital\DB;
$path = DB::getPath();
echo "DB path: $path\n";
if (!file_exists($path)) {
    echo "DB file does not exist.\n";
    exit(0);
}
$pdo = DB::getPDO();
try {
    $rows = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $rows ? $rows->fetchAll(PDO::FETCH_COLUMN) : [];
    if (empty($tables)) {
        echo "No tables found.\n";
    } else {
        echo "Tables:\n";
        foreach ($tables as $t) echo " - $t\n";
    }
} catch (Exception $e) {
    echo "Query failed: " . $e->getMessage() . "\n";
}
