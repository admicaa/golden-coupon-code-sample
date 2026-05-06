<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Search extends Model
{
    /** Number of FULLTEXT stage columns participating in the relevance expression. */
    private const STAGE_COUNT = 5;

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    /**
     * Apply the full search filter: term + countries + filters + type restrictions.
     *
     * When `$count` is true, only the WHERE clauses are applied so callers can
     * wrap the result in an aggregation query (see SearchFacetService).
     * Otherwise the SELECT also pulls a relevance score used to order results.
     */
    public function scopeSearch(
        Builder $query,
        ?string $term,
        array $countries = [],
        bool $count = false,
        array $filters = [],
        bool $storeOnly = false,
        bool $couponOnly = false,
    ): Builder {
        $matchTerm = $this->buildMatchTerm($term);

        if ($matchTerm !== null) {
            $query->whereRaw(
                'MATCH(`stage_1`,`stage_2`,`stage_3`,`stage_4`,`stage_5`) AGAINST (? IN BOOLEAN MODE)',
                [$matchTerm]
            );
        }

        if (!$count && $matchTerm !== null) {
            $bindings = array_fill(0, self::STAGE_COUNT + 1, $matchTerm);

            $query->select(['coupon_id', 'store_id'])
                ->selectRaw('COUNT(*) AS all_count')
                ->selectRaw('SUM(' . $this->relevanceExpression() . ') AS rev_sum', $bindings)
                ->orderByDesc('rev_sum');
        }

        $query->groupBy('coupon_id', 'store_id');

        $this->applyCountryFilter($query, $countries);
        $this->applyTypeFilter($query, $storeOnly, $couponOnly);
        $this->applyOptionsFilter($query, $filters);

        return $query;
    }

    /**
     * Restrict the result set to rows whose owning store is in any of the given countries.
     *
     * A `searches` row carries either a `store_id` or a `coupon_id` (or both),
     * so we union both relations rather than join them, which would produce
     * duplicate matched pairs.
     */
    protected function applyCountryFilter(Builder $query, array $countries): void
    {
        if (empty($countries)) {
            return;
        }

        $query->where(function (Builder $q) use ($countries) {
            $q->whereHas('coupon.store', fn (Builder $store) => $store->whereIn('country_id', $countries))
                ->orWhereHas('store', fn (Builder $store) => $store->whereIn('country_id', $countries));
        });
    }

    protected function applyTypeFilter(Builder $query, bool $storeOnly, bool $couponOnly): void
    {
        // Both flags simultaneously means "no restriction".
        if ($storeOnly && !$couponOnly) {
            $query->whereNotNull('store_id');
        }

        if ($couponOnly && !$storeOnly) {
            $query->whereNotNull('coupon_id');
        }
    }

    protected function applyOptionsFilter(Builder $query, array $filters): void
    {
        if (empty($filters)) {
            return;
        }

        $query->where(function (Builder $q) use ($filters) {
            $q->whereHas('coupon.options', fn (Builder $option) => $option->whereIn('search_options.id', $filters))
                ->orWhereHas('store.options', fn (Builder $option) => $option->whereIn('search_options.id', $filters));
        });
    }

    /**
     * Sanitise a free-text term for MySQL boolean-mode FULLTEXT.
     *
     * We split digit runs into their own tokens and append the original input
     * so partial matches against numeric ranges still hit (e.g. "20%" → "20  20%").
     * Operator characters that boolean mode would interpret are stripped.
     */
    protected function buildMatchTerm(?string $term): ?string
    {
        $term = trim((string) $term);
        if ($term === '') {
            return null;
        }

        $spaced = preg_replace('/(\d+)/', ' ${1} ', $term);
        $combined = $spaced . ',' . $term;
        $sanitized = preg_replace('/[+\-*<>~()"\'@]/u', ' ', $combined);

        return trim(preg_replace('/\s+/', ' ', $sanitized));
    }

    /**
     * Build the SQL expression that ranks a row against the searched term.
     *
     * The expression must be paired with exactly `STAGE_COUNT + 1` bindings of
     * the same match term — one for the combined `MATCH(...)` and one for each
     * per-stage match call.
     */
    protected function relevanceExpression(): string
    {
        $expression = '10 * MATCH(`stage_1`,`stage_2`,`stage_3`,`stage_4`,`stage_5`) AGAINST (? IN BOOLEAN MODE)';

        for ($i = 1; $i <= self::STAGE_COUNT; $i++) {
            $weight = (int) (750 / ($i * $i));
            $expression .= ' + ' . $weight . ' * MATCH(`stage_' . $i . '`) AGAINST (? IN BOOLEAN MODE)';
        }

        return $expression;
    }
}
