<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class Search extends Model
{
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function scopeSearch(
        Builder $query,
        $term,
        array $countries = [],
        $count = false,
        array $filters = [],
        $storeOnly = false,
        $couponOnly = false
    ) {
        $matchTerm = $this->buildMatchTerm($term);

        if (!$count && $matchTerm !== null) {
            $bindings = array_fill(0, 6, $matchTerm);
            $query->select([
                'coupon_id',
                'store_id',
                new Expression('COUNT(*) AS all_count'),
                new Expression('SUM(' . $this->relevanceExpression() . ') AS rev_sum'),
            ])->setBindings($bindings, 'select')->orderBy('rev_sum', 'DESC');
        }

        if ($matchTerm !== null) {
            $query->whereRaw(
                'MATCH(`stage_1`,`stage_2`,`stage_3`,`stage_4`,`stage_5`) AGAINST (? IN BOOLEAN MODE)',
                [$matchTerm]
            );
        }

        $query->groupBy('coupon_id', 'store_id');

        if (!empty($countries)) {
            $query->where(function (Builder $query) use ($countries) {
                $query->whereHas('coupon.store', function (Builder $store) use ($countries) {
                    $store->whereIn('country_id', $countries);
                })->orWhereHas('store', function (Builder $store) use ($countries) {
                    $store->whereIn('country_id', $countries);
                });
            });
        }

        if ($storeOnly && !$couponOnly) {
            $query->whereNotNull('store_id');
        }

        if ($couponOnly && !$storeOnly) {
            $query->whereNotNull('coupon_id');
        }

        if (!empty($filters)) {
            $query->where(function (Builder $query) use ($filters) {
                $query->whereHas('coupon.options', function (Builder $option) use ($filters) {
                    $option->whereIn('search_options.id', $filters);
                })->orWhereHas('store.options', function (Builder $option) use ($filters) {
                    $option->whereIn('search_options.id', $filters);
                });
            });
        }

        return $query;
    }

    protected function buildMatchTerm($term)
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

    protected function relevanceExpression()
    {
        $expression = '10 * MATCH(`stage_1`,`stage_2`,`stage_3`,`stage_4`,`stage_5`) AGAINST (? IN BOOLEAN MODE)';

        for ($i = 1; $i <= 5; $i++) {
            $weight = (int) (750 / ($i * $i));
            $expression .= ' + ' . $weight . ' * MATCH(`stage_' . $i . '`) AGAINST (? IN BOOLEAN MODE)';
        }

        return $expression;
    }
}
