<?php

namespace App\Support\Reports;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Helper class for building common report queries.
 */
class ReportQueryBuilder
{
    /**
     * Apply date range filter to a query.
     * 
     * @param Builder $query
     * @param string $column Column name to filter on
     * @param Carbon|null $start Start date
     * @param Carbon|null $end End date
     * @return Builder
     */
    public static function applyDateRange(
        Builder $query,
        string $column,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder {
        if ($start) {
            $query->where($column, '>=', $start);
        }

        if ($end) {
            $query->where($column, '<=', $end);
        }

        return $query;
    }

    /**
     * Apply date range filter with null handling.
     * 
     * @param Builder $query
     * @param string $column Column name to filter on
     * @param Carbon|null $start Start date
     * @param Carbon|null $end End date
     * @param bool $includeNull Whether to include null values
     * @return Builder
     */
    public static function applyDateRangeWithNull(
        Builder $query,
        string $column,
        ?Carbon $start = null,
        ?Carbon $end = null,
        bool $includeNull = false
    ): Builder {
        if (!$start && !$end) {
            return $query;
        }

        $query->where(function ($q) use ($column, $start, $end, $includeNull) {
            if ($start && $end) {
                $q->whereBetween($column, [$start, $end]);
            } elseif ($start) {
                $q->where($column, '>=', $start);
            } elseif ($end) {
                $q->where($column, '<=', $end);
            }

            if ($includeNull) {
                $q->orWhereNull($column);
            }
        });

        return $query;
    }

    /**
     * Apply optional filters to a query.
     * 
     * @param Builder $query
     * @param array $filters Array of ['column' => 'value'] filters
     * @return Builder
     */
    public static function applyOptionalFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Apply search filter (LIKE query on multiple columns).
     * 
     * @param Builder $query
     * @param string|null $searchTerm
     * @param array $columns Columns to search in
     * @return Builder
     */
    public static function applySearch(Builder $query, ?string $searchTerm, array $columns): Builder
    {
        if (!$searchTerm || empty($columns)) {
            return $query;
        }

        $query->where(function ($q) use ($searchTerm, $columns) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $q->{$method}($column, 'LIKE', "%{$searchTerm}%");
            }
        });

        return $query;
    }

    /**
     * Apply status filter with common status values.
     * 
     * @param Builder $query
     * @param string $column Status column name
     * @param string|null $status Status value (null = show all)
     * @return Builder
     */
    public static function applyStatusFilter(Builder $query, string $column, ?string $status): Builder
    {
        if ($status && $status !== 'all') {
            $query->where($column, $status);
        }

        return $query;
    }
}

