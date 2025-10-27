<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking lawyers in database...\n\n";

$lawyers = \App\Models\Lawyer::select('id', 'lawyer_name_ar', 'lawyer_name_en')->get();

echo "Found " . $lawyers->count() . " lawyers:\n";
foreach($lawyers as $lawyer) {
    echo "ID {$lawyer->id}: {$lawyer->lawyer_name_ar} ({$lawyer->lawyer_name_en})\n";
}
