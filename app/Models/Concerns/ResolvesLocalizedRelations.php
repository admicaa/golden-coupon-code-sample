<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;

trait ResolvesLocalizedRelations
{
    protected $localizedRelationCache = [];

    protected function localizedRelation(string $relation, ?string $language = null)
    {
        $language = $language ?: language();

        foreach (language_fallbacks($language) as $candidate) {
            $cacheKey = $relation . ':' . $candidate;

            if (array_key_exists($cacheKey, $this->localizedRelationCache)) {
                return $this->localizedRelationCache[$cacheKey];
            }

            if ($this->relationLoaded($relation)) {
                $localized = $this->loadedLocalizedRelation($relation, $candidate);
                if ($localized) {
                    return $this->localizedRelationCache[$cacheKey] = $localized;
                }
            }

            $localized = $this->{$relation}()
                ->where('language', $candidate)
                ->first();

            if ($localized) {
                return $this->localizedRelationCache[$cacheKey] = $localized;
            }
        }

        return $this->{$relation}()
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
