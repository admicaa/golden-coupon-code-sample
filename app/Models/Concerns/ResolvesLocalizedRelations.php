<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;

trait ResolvesLocalizedRelations
{
    protected $localizedRelationCache = [];

    protected function localizedRelation(string $relation, ?string $language = null)
    {
        $language = $language ?: language();
        $cacheKey = $relation . ':' . $language;

        if (array_key_exists($cacheKey, $this->localizedRelationCache)) {
            return $this->localizedRelationCache[$cacheKey];
        }

        if ($this->relationLoaded($relation)) {
            $localized = $this->loadedLocalizedRelation($relation, $language);
            if ($localized) {
                return $this->localizedRelationCache[$cacheKey] = $localized;
            }
        }

        return $this->localizedRelationCache[$cacheKey] = $this->{$relation}()
            ->where('language', $language)
            ->firstOrFail();
    }

    protected function loadedLocalizedRelation(string $relation, string $language)
    {
        $loadedRelation = $this->getRelation($relation);

        if ($loadedRelation instanceof Collection) {
            return $loadedRelation->firstWhere('language', $language);
        }

        if ($loadedRelation && data_get($loadedRelation, 'language') === $language) {
            return $loadedRelation;
        }

        return null;
    }
}
