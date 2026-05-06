<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;

/**
 * Resolves a "localized" relation (one row per language) to the right row for
 * the current request.
 *
 * Two entry points:
 *
 *   - `localizedRelation()` is the strict resolver. If callers want a
 *     guaranteed row (admin endpoints loading a single record), they should
 *     call `mainPage()` from a controller path that already validated input.
 *     Returns `null` when nothing matches; never throws.
 *
 *   - `localizedRelationOrNull()` is the accessor-safe resolver. It NEVER
 *     issues a database query when the relation has not been eager loaded;
 *     this is the form used from `getPageAttribute()` style accessors so a
 *     forgotten `with()` does not turn a list endpoint into an N+1.
 */
trait ResolvesLocalizedRelations
{
    protected $localizedRelationCache = [];

    /**
     * Resolve a localized relation, allowing one targeted query per candidate
     * language only when the relation has not been eager loaded.
     *
     * Returns `null` if no row matches any fallback. The previous version of
     * this trait called `firstOrFail()` here, which threw mid-serialization
     * on list endpoints when even one record was missing translations.
     */
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

    /**
     * Strictly accessor-safe variant. Only inspects already-loaded data.
     */
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
