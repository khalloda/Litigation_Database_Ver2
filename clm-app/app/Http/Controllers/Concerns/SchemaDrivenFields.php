<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;

trait SchemaDrivenFields
{
    /**
     * Get all columns with metadata for a table.
     * 
     * @param string $table
     * @param \Illuminate\Database\Eloquent\Model|null $model
     * @return array
     */
    protected function getSchemaFields(string $table, $model = null): array
    {
        $columns = Schema::getColumnListing($table);
        $types = [];
        $fkHints = [];
        
        // Get column types using Doctrine schema manager
        try {
            $connection = DB::connection();
            $schemaManager = $connection->getDoctrineSchemaManager();
            $doctrineTable = $schemaManager->listTableDetails($table);
            
            foreach ($columns as $column) {
                $columnObj = $doctrineTable->getColumn($column);
                $types[$column] = $columnObj->getType()->getName();
                
                // Check if it's a foreign key
                $isFk = false;
                if (str_ends_with($column, '_id')) {
                    $isFk = true;
                } else {
                    // Check actual FK constraints
                    $foreignKeys = $doctrineTable->getForeignKeys();
                    foreach ($foreignKeys as $fk) {
                        if (in_array($column, $fk->getLocalColumns())) {
                            $isFk = true;
                            break;
                        }
                    }
                }
                $fkHints[$column] = $isFk;
            }
        } catch (\Exception $e) {
            // Fallback: use basic type detection
            foreach ($columns as $column) {
                $types[$column] = 'unknown';
                $fkHints[$column] = str_ends_with($column, '_id');
            }
        }
        
        return [
            'columns' => $columns,
            'types' => $types,
            'fkHints' => $fkHints,
        ];
    }
}

