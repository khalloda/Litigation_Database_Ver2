<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Arabic character normalization...\n\n";

// Test the normalization function
$service = new \App\Services\FuzzyMatchingChoiceService();

// Test cases
$testCases = [
    'مدعي' => 'مدعى',
    'مدعى' => 'مدعى',
    'مستأنفة' => 'مستأنفت',
    'مستأنفت' => 'مستأنفت',
    'محامي' => 'محامى',
    'محامى' => 'محامى',
    'قاضي' => 'قاضى',
    'قاضى' => 'قاضى'
];

echo "Testing normalizeArabicText method:\n";
foreach ($testCases as $input => $expected) {
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('normalizeArabicText');
    $method->setAccessible(true);

    $result = $method->invoke($service, $input);
    echo "Input: '{$input}' -> Output: '{$result}' (Expected: '{$expected}')\n";
}

echo "\nTesting capacity choices with ي/ى variations:\n";

// Test capacity choices with different variations
$testValues = ['مدعي', 'مدعى', 'مستأنفة', 'مستأنفت'];

foreach ($testValues as $testValue) {
    echo "\nTesting: '{$testValue}'\n";

    $choices = $service->getChoicesForField('client_capacity_id', $testValue);

    echo "Found " . count($choices['choices']) . " choices:\n";
    foreach ($choices['choices'] as $choice) {
        echo "  - {$choice['label_ar']} ({$choice['label_en']})\n";
    }
}

echo "\nTesting lawyer choices with ي/ى variations:\n";

// Test lawyer choices with different variations
$testValues = ['محامي', 'محامى', 'قاضي', 'قاضى'];

foreach ($testValues as $testValue) {
    echo "\nTesting: '{$testValue}'\n";

    $choices = $service->getChoicesForField('matter_partner_id', $testValue);

    echo "Found " . count($choices['choices']) . " choices:\n";
    foreach ($choices['choices'] as $choice) {
        echo "  - {$choice['name_ar']} ({$choice['name_en']})\n";
    }
}
