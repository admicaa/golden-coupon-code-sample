<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;
use App\Models\CountryNames;

class Country extends Model
{
    //
    use ResolvesLocalizedRelations;

    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = ['name', 'header_name', 'metatags'];

    public function names()
    {
        return $this->hasMany(CountryNames::class, 'country_id', 'id');
    }

    public function countryName()
    {
        return $this->localizedRelation('names');
    }

    /**
     * Accessor-safe view of the localized name row. Prefers the eager-loaded
     * `names` collection (set up by `frontFormula`), falls back to a single
     * targeted query, and returns null instead of throwing when no row exists.
     */
    protected function resolvedName()
    {
        return $this->localizedRelationOrNull('names') ?: $this->countryName();
    }

    protected function getArrayableAppends()
    {
        $appends = parent::getArrayableAppends();

        if (should_hide_tour_page_description()) {
            return array_values(array_diff($appends, ['metatags']));
        }

        return $appends;
    }

    public function getMetatagsAttribute()
    {
        return optional($this->resolvedName())->metatags;
    }

    public function getHeaderNameAttribute()
    {
        return optional($this->resolvedName())->header_name;
    }

    public function getNameAttribute()
    {
        return optional($this->resolvedName())->name;
    }
    // stores
    public function scopeSectionsFormula($query)
    {
        return $query->with(['sections' => function ($query) {
            return $query->frontFormula();
        }]);
    }

    public function scopeFrontFormula($query)
    {
        return $query->with(['names' => function ($query) {
            return $query->whereIn('language', language_fallbacks());
        }]);
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'country_id', 'id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'country_id', 'id');
    }
}
