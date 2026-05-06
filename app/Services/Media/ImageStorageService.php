<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImageStorageService
{
    public function storeUploadedImage(UploadedFile $file, $directory)
    {
        $storedPath = $file->store($directory);

        return $this->payloadFromStoredPath($storedPath);
    }

    public function payloadFromStoredPath($storedPath, array $attributes = [])
    {
        $publicPath = Storage::url($storedPath);

        return array_merge([
            'storage_path' => ltrim(str_replace('/storage/', '', $publicPath), '/'),
            'path' => $publicPath,
            'image_path' => url($publicPath),
        ], $attributes);
    }

    public function payloadFromPublicPath($publicPath, $storagePath = null, array $attributes = [])
    {
        return array_merge([
            'storage_path' => $storagePath,
            'path' => $publicPath,
            'image_path' => url($publicPath),
        ], $attributes);
    }

    public function deleteStoredPath($storagePath)
    {
        if (!$storagePath) {
            return;
        }

        $absolute = realpath(storage_path('app/' . ltrim($storagePath, '/')));
        $root = realpath(storage_path('app'));

        if ($absolute && $root && strpos($absolute, $root . DIRECTORY_SEPARATOR) === 0 && File::exists($absolute)) {
            File::delete($absolute);
        }
    }
}
