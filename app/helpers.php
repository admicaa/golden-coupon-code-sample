<?php

use App\Models\Languages;

if (!function_exists('language')) {
    function language()
    {
        $allowed = config('app.supported_languages', ['GB', 'AR']);
        $header = request()->header('Content-Language');

        return in_array($header, $allowed, true) ? $header : 'GB';
    }
}

if (!function_exists('languages')) {
    function languages()
    {
        return Languages::all();
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
