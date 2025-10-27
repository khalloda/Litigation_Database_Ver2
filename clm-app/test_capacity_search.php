<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Searching for capacity values containing 'طاعة'...\n\n";

$values = \App\Models\OptionValue::whereHas('optionSet', function($q) {
    $q->where('key', 'capacity.type');
})->where('label_ar', 'like', '%طاعة%')->get();

echo "Found {$values->count()} values containing 'طاعة':\n";
foreach($values as $v) {
    echo "ID: {$v->id}, AR: '{$v->label_ar}', EN: '{$v->label_en}'\n";
}

echo "\nSearching for capacity values containing 'مستأنف'...\n";
$values2 = \App\Models\OptionValue::whereHas('optionSet', function($q) {
    $q->where('key', 'capacity.type');
})->where('label_ar', 'like', '%مستأنف%')->get();

echo "Found {$values2->count()} values containing 'مستأنف':\n";
foreach($values2 as $v) {
    echo "ID: {$v->id}, AR: '{$v->label_ar}', EN: '{$v->label_en}'\n";
}
