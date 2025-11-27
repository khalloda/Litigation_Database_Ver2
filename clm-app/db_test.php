<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Load .env manually
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                putenv(trim($line));
            }
        }
    }
    
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: 'litigation';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    $output = "Database Connection Test\n";
    $output .= "========================\n";
    $output .= "Host: {$host}:{$port}\n";
    $output .= "Database: {$database}\n";
    $output .= "User: {$username}\n\n";
    
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database}",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $output .= "Connection: SUCCESS\n\n";
    
    $tables = ['clients', 'lawyers', 'cases', 'hearings', 'opponents', 'courts'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt2 = $pdo->query("SELECT MAX(id) as max_id FROM `{$table}`");
        $maxId = $stmt2->fetch(PDO::FETCH_ASSOC)['max_id'] ?? 0;
        
        $isAutoInc = stripos($column['Extra'] ?? '', 'auto_increment') !== false;
        
        $output .= sprintf("%-15s | AI: %-3s | Max ID: %d\n", 
            $table, 
            $isAutoInc ? 'YES' : 'NO',
            $maxId
        );
    }
    
} catch (Exception $e) {
    $output = "ERROR: " . $e->getMessage() . "\n";
    $output .= "Trace: " . $e->getTraceAsString() . "\n";
}

// Write to storage/logs which should be writable
file_put_contents(__DIR__ . '/storage/logs/db_test.log', $output);

// Also write to a guaranteed location
file_put_contents('C:/temp/db_test.log', $output);

echo $output;

