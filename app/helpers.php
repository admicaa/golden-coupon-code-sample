<?php

use App\Models\Languages;
use Illuminate\Support\Facades\Cache;

if (!function_exists('front_storage')) {
    /**
     * Resolve an absolute filesystem path under the "front" disk root.
     *
     * Mirrors Storage::disk('front')->path($path) but returns a string even
     * when callers want to use it with the File facade or PHP filesystem
     * functions directly. Falls back to storage/app/front when
     * FRONT_END_STORAGE_PATH is not set.
     */
    function front_storage($path = '')
    {
        $root = config('filesystems.front_storage') ?: storage_path('app/front');
        $root = rtrim((string) $root, DIRECTORY_SEPARATOR);

        if ($path === '' || $path === null) {
            return $root;
        }

        return $root . DIRECTORY_SEPARATOR . ltrim((string) $path, '/' . DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('default_language')) {
    function default_language()
    {
        return (string) config('app.locale', 'GB');
    }
}

if (!function_exists('language_shortcuts')) {
    function language_shortcuts()
    {
        $configured = array_values(array_unique((array) config('app.supported_languages', [default_language(), 'AR'])));
        $stored = languages()->pluck('shortcut')->all();

        return !empty($stored) ? array_values(array_unique($stored)) : $configured;
    }
}

if (!function_exists('language')) {
    function language()
    {
        $allowed = language_shortcuts();
        $default = in_array(default_language(), $allowed, true) ? default_language() : ($allowed[0] ?? 'GB');
        $request = request();

        foreach (language_header_candidates($request->header('Content-Language')) as $candidate) {
            $resolved = resolve_language_candidate($candidate, $allowed);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        foreach (explode(',', (string) $request->header('Accept-Language')) as $segment) {
            $parts = explode(';', $segment);
            foreach (language_header_candidates($parts[0] ?? null) as $candidate) {
                $resolved = resolve_language_candidate($candidate, $allowed);
                if ($resolved !== null) {
                    return $resolved;
                }
            }
        }

        return $default;
    }
}

if (!function_exists('languages')) {
    function languages()
    {
        if (app()->runningUnitTests()) {
            return Languages::query()->orderBy('id')->get();
        }

        static $loaded;

        if ($loaded !== null) {
            return $loaded;
        }

        $loaded = Cache::rememberForever('languages.all', function () {
            return Languages::query()->orderBy('id')->get();
        });

        return $loaded;
    }
}

if (!function_exists('language_header_candidates')) {
    function language_header_candidates($value)
    {
        $value = strtoupper(trim((string) $value));

        if ($value === '') {
            return [];
        }

        $tokens = preg_split('/[-_]/', $value) ?: [];
        $primary = strtoupper($tokens[0] ?? '');
        $region = strtoupper($tokens[1] ?? '');

        $candidates = array_filter([
            $value,
            $primary,
            $region,
            map_primary_language_to_legacy_shortcut($primary),
        ]);

        return array_values(array_unique($candidates));
    }
}

if (!function_exists('map_primary_language_to_legacy_shortcut')) {
    function map_primary_language_to_legacy_shortcut($primary)
    {
        $map = [
            'EN' => 'GB',
            'AR' => 'AR',
        ];

        return $map[$primary] ?? null;
    }
}

if (!function_exists('resolve_language_candidate')) {
    function resolve_language_candidate($candidate, array $allowed)
    {
        return in_array($candidate, $allowed, true) ? $candidate : null;
    }
}

if (!function_exists('language_fallbacks')) {
    function language_fallbacks($language = null)
    {
        return array_values(array_unique([
            $language ?: language(),
            default_language(),
        ]));
    }
}

if (!function_exists('should_include_page_body')) {
    function should_include_page_body()
    {
        return request()->boolean('body');
    }
}

if (!function_exists('should_hide_tour_page_description')) {
    function should_hide_tour_page_description()
    {
        return request()->boolean('hide_tour_page_description');
    }
}

if (!function_exists('abortJson')) {
    function abortJson($body = ['message' => 'Not Found'], $status = 404)
    {
        abort(response()->json($body, $status));
    }
}

if (!function_exists('per_page')) {
    function per_page($requested = null, $default = null, $max = null)
    {
        $default = $default ?: config('pagination.default_per_page', 10);
        $max = $max ?: config('pagination.max_per_page', 100);
        $value = (int) ($requested ?: $default);

        if ($value < 1) {
            $value = $default;
        }

        return min($value, $max);
    }
}
