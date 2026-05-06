<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;

trait ResolvesLocalizedRelations
{
    protected $localizedRelationCache = [];

    protected function localizedRelation(string $relation, ?string $language = null)
    {
        $language = $language ?: language();
        $candidates = language_fallbacks($language);
        $relationLoaded = $this->relationLoaded($relation);

        foreach ($candidates as $candidate) {
            $cacheKey = $relation . ':' . $candidate;

            if (array_key_exists($cacheKey, $this->localizedRelationCache)) {
                $cached = $this->localizedRelationCache[$cacheKey];
                if ($cached !== null) {
                    return $cached;
                }
                continue;
            }

            if ($relationLoaded) {
                $localized = $this->loadedLocalizedRelation($relation, $candidate);
                if ($localized) {
                    return $this->localizedRelationCache[$cacheKey] = $localized;
                }
                // Trust the eager-loaded data: do not fall through to a query.
                $this->localizedRelationCache[$cacheKey] = null;
                continue;
            }

            $localized = $this->{$relation}()
                ->where('language', $candidate)
                ->first();

            if ($localized) {
                return $this->localizedRelationCache[$cacheKey] = $localized;
            }

            $this->localizedRelationCache[$cacheKey] = null;
        }

        return null;
    }

    protected function localizedRelationOrNull(string $relation, ?string $language = null)
    {
        if (!$this->relationLoaded($relation)) {
            return null;
        }

        $language = $language ?: language();

        foreach (language_fallbacks($language) as $candidate) {
            $cacheKey = $relation . ':' . $candidate;

            if (array_key_exists($cacheKey, $this->localizedRelationCache)) {
                $cached = $this->localizedRelationCache[$cacheKey];
                if ($cached !== null) {
                    return $cached;
                }
                continue;
            }

            $localized = $this->loadedLocalizedRelation($relation, $candidate);
            if ($localized) {
                return $this->localizedRelationCache[$cacheKey] = $localized;
            }

            $this->localizedRelationCache[$cacheKey] = null;
        }

        return null;
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
