<?php

// Read-only audit script for cases table schema and longest values

$host = 'localhost';
$port = '3306';
$db = 'litigation_db_ver2';
$user = 'root';
$pass = '1234';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 1: Get column information
    $stmt = $pdo->prepare("
        SELECT
            COLUMN_NAME,
            DATA_TYPE,
            IS_NULLABLE,
            CHARACTER_MAXIMUM_LENGTH,
            ORDINAL_POSITION
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ?
          AND TABLE_NAME = 'cases'
        ORDER BY ORDINAL_POSITION
    ");
    $stmt->execute([$db]);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 2: Check if cases table has any rows
    $rowCountStmt = $pdo->query("SELECT COUNT(*) as cnt FROM cases");
    $rowCount = $rowCountStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    if ($rowCount == 0) {
        echo "# Cases Table Schema Audit\n\n";
        echo "**Note:** The cases table is empty (0 rows). Longest values cannot be computed.\n\n";
        echo "| field_name | data_type | longest_value_chars |\n";
        echo "|------------|-----------|---------------------|\n";
        foreach ($columns as $col) {
            $maxLen = $col['CHARACTER_MAXIMUM_LENGTH'] ?? ($col['DATA_TYPE'] === 'text' ? 65535 : 'NULL');
            echo "| {$col['COLUMN_NAME']} | {$col['DATA_TYPE']} | " . ($rowCount == 0 ? '0 (empty table)' : 'N/A') . " |\n";
        }
        exit(0);
    }

    // Step 3: Build dynamic query to get longest values
    $selectParts = [];
    foreach ($columns as $col) {
        $colName = $col['COLUMN_NAME'];
        $selectParts[] = "MAX(CHAR_LENGTH(CAST(`{$colName}` AS CHAR))) AS `{$colName}`";
    }

    $maxQuery = "SELECT " . implode(", ", $selectParts) . " FROM cases";
    $maxStmt = $pdo->query($maxQuery);
    $maxValues = $maxStmt->fetch(PDO::FETCH_ASSOC);

    // Step 4: Output markdown table
    echo "# Cases Table Schema Audit\n\n";
    echo "| field_name | data_type | longest_value_chars |\n";
    echo "|------------|-----------|---------------------|\n";

    foreach ($columns as $col) {
        $fieldName = $col['COLUMN_NAME'];
        $dataType = $col['DATA_TYPE'];
        $longestValue = $maxValues[$fieldName] ?? 0;

        echo "| {$fieldName} | {$dataType} | {$longestValue} |\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
