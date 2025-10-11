<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "Inspecting clients.xlsx columns...\n\n";

$file = __DIR__ . '/../Access_Data_Export/clients.xlsx';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();

// Get headers (first row)
$headers = [];
$headerRow = $sheet->getRowIterator(1, 1)->current();
$cellIterator = $headerRow->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(false);

foreach ($cellIterator as $cell) {
    $headers[] = $cell->getValue();
}

echo "Column Headers:\n";
foreach ($headers as $index => $header) {
    if (!empty($header)) {
        echo ($index + 1) . ". " . $header . "\n";
    }
}

// Get first data row to see sample values
echo "\n\nSample Data (First Row):\n";
$dataRow = $sheet->getRowIterator(2, 2)->current();
$cellIterator = $dataRow->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(false);

$colIndex = 0;
foreach ($cellIterator as $cell) {
    if (!empty($headers[$colIndex])) {
        $value = $cell->getValue();
        echo $headers[$colIndex] . " = " . ($value ?? 'NULL') . "\n";
    }
    $colIndex++;
}

echo "\nDone.\n";
