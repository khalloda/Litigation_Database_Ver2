<?php

namespace App\Services\Import;

final class ResolverRegistry
{
    /**
     * Map import columns (semantic keys) to resolver callables.
     * Each resolver signature: fn(array $row, string $rawValue): ?array
     */
    public static function map(): array
    {
        return [
            // Examples (to be implemented incrementally)
            // 'court_name' => [\App\Services\Import\Resolvers\CourtResolver::class, 'resolve'],
            // 'lawyer_name' => [\App\Services\Import\Resolvers\LawyerResolver::class, 'resolve'],
            // 'client_name' => [\App\Services\Import\Resolvers\ClientResolver::class, 'resolve'],
            // 'opponent_name' => [\App\Services\Import\Resolvers\OpponentResolver::class, 'resolve'],
        ];
    }
}

