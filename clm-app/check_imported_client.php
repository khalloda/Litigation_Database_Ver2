<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Client;

echo "Checking imported client data...\n\n";

$client = Client::find(47);
if ($client) {
    echo "Client 47:\n";
    echo "ID: {$client->id}\n";
    echo "Name: {$client->client_name_en}\n";
    echo "status: " . ($client->status ?? 'NULL') . "\n";
    echo "cash_or_probono: " . ($client->cash_or_probono ?? 'NULL') . "\n";
    echo "power_of_attorney_location: " . ($client->power_of_attorney_location ?? 'NULL') . "\n";
    echo "documents_location: " . ($client->documents_location ?? 'NULL') . "\n";
    echo "contact_lawyer: " . ($client->contact_lawyer ?? 'NULL') . "\n";
    echo "\n";
    
    echo "FK IDs:\n";
    echo "status_id: " . ($client->status_id ?? 'NULL') . "\n";
    echo "cash_or_probono_id: " . ($client->cash_or_probono_id ?? 'NULL') . "\n";
    echo "power_of_attorney_location_id: " . ($client->power_of_attorney_location_id ?? 'NULL') . "\n";
    echo "documents_location_id: " . ($client->documents_location_id ?? 'NULL') . "\n";
    echo "contact_lawyer_id: " . ($client->contact_lawyer_id ?? 'NULL') . "\n";
} else {
    echo "Client 47 not found.\n";
}

echo "\nDone.\n";
