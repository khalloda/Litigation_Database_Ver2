<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Move existing logo files from storage/app/public/logos to public/uploads/logos
        $clients = DB::table('clients')->whereNotNull('logo')->get();

        foreach ($clients as $client) {
            $oldPath = storage_path('app/public/' . $client->logo);
            $newPath = public_path('uploads/' . $client->logo);

            // Check if old file exists
            if (File::exists($oldPath)) {
                // Create directory if it doesn't exist
                $directory = dirname($newPath);
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }

                // Copy file to new location
                if (File::copy($oldPath, $newPath)) {
                    echo "Moved logo file: {$client->logo}\n";

                    // Update database path to new format
                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update(['logo' => 'uploads/' . $client->logo]);
                } else {
                    echo "Failed to move logo file: {$client->logo}\n";
                }
            } else {
                echo "Logo file not found: {$oldPath}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move files back from public/uploads/logos to storage/app/public/logos
        $clients = DB::table('clients')->whereNotNull('logo')->get();

        foreach ($clients as $client) {
            // Skip if already in old format
            if (str_starts_with($client->logo, 'logos/')) {
                continue;
            }

            $newPath = public_path($client->logo);
            $oldPath = storage_path('app/public/' . str_replace('uploads/', '', $client->logo));

            // Check if new file exists
            if (File::exists($newPath)) {
                // Create directory if it doesn't exist
                $directory = dirname($oldPath);
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }

                // Copy file back to old location
                if (File::copy($newPath, $oldPath)) {
                    echo "Moved logo file back: {$client->logo}\n";

                    // Update database path back to old format
                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update(['logo' => str_replace('uploads/', '', $client->logo)]);
                } else {
                    echo "Failed to move logo file back: {$client->logo}\n";
                }
            } else {
                echo "Logo file not found: {$newPath}\n";
            }
        }
    }
};
