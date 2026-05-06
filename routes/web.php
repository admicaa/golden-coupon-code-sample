<?php

use App\Models\StoreImages;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/storage/{filename}', function ($filename) {
    $relative = ltrim($filename, '/');
    $root = realpath(storage_path('app'));
    $absolute = realpath(storage_path('app/' . $relative));

    if (!$absolute || !$root || strpos($absolute, $root . DIRECTORY_SEPARATOR) !== 0) {
        $image = StoreImages::where('path', '/storage/' . $relative)->firstOrFail();
        $absolute = realpath(storage_path('app/' . ltrim($image->storage_path, '/')));

        if (!$absolute || strpos($absolute, $root . DIRECTORY_SEPARATOR) !== 0) {
            abort(404);
        }
    }

    if (!File::exists($absolute)) {
        abort(404);
    }

    return response()->file($absolute, [
        'Content-Type' => File::mimeType($absolute),
    ]);
})->where('filename', '[A-Za-z0-9_\-./]+');
