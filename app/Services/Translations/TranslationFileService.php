<?php

namespace App\Services\Translations;

use Illuminate\Support\Facades\File;

class TranslationFileService
{
    public function listFiles()
    {
        return $this->collectFiles($this->sourceRoot());
    }

    public function load($language, $relativePath)
    {
        $base = $this->loadBaseTranslations($relativePath);
        $override = $this->loadOverrideTranslations($language, $relativePath);

        return $this->mergeTranslations($base, $override);
    }

    public function save($language, $relativePath, array $data)
    {
        $path = $this->overridePath($language, $relativePath);
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            true
        );

        return $data;
    }

    public function exportLanguageTree($language)
    {
        return $this->loadDirectory($language, '');
    }

    public function assertEditableFile($relativePath)
    {
        $sourcePath = $this->sourcePath($relativePath);

        if (!File::exists($sourcePath)) {
            abort(404);
        }

        return $sourcePath;
    }

    protected function loadDirectory($language, $relativeDirectory)
    {
        $directory = rtrim($this->sourceRoot() . DIRECTORY_SEPARATOR . ltrim($relativeDirectory, '/'), DIRECTORY_SEPARATOR);
        $strings = [];

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = ltrim($relativeDirectory . '/' . $file->getFilename(), '/');
            $name = $file->getFilenameWithoutExtension();
            $contents = $this->load($language, $relativePath);

            if ($name === 'index') {
                $strings = array_merge($contents, $strings);
            } else {
                $strings[$name] = $contents;
            }
        }

        foreach (File::directories($directory) as $subDirectory) {
            $subName = basename($subDirectory);
            $strings[$subName] = $this->loadDirectory($language, ltrim($relativeDirectory . '/' . $subName, '/'));
        }

        return $strings;
    }

    protected function collectFiles($directory)
    {
        $list = [];

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() === 'php') {
                $list[$file->getFilename()] = true;
            }
        }

        foreach (File::directories($directory) as $subDirectory) {
            $list[basename($subDirectory)] = $this->collectFiles($subDirectory);
        }

        return $list;
    }

    protected function loadBaseTranslations($relativePath)
    {
        $sourcePath = $this->sourcePath($relativePath);
        $contents = require $sourcePath;

        return is_array($contents) ? $contents : [];
    }

    protected function loadOverrideTranslations($language, $relativePath)
    {
        $overridePath = $this->overridePath($language, $relativePath);

        if (!File::exists($overridePath)) {
            return [];
        }

        $decoded = json_decode((string) File::get($overridePath), true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function mergeTranslations(array $base, array $override)
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeTranslations($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    protected function sourceRoot()
    {
        return resource_path('langMain/en/pages');
    }

    protected function sourcePath($relativePath)
    {
        $root = realpath($this->sourceRoot());
        $candidate = $this->sourceRoot() . DIRECTORY_SEPARATOR . ltrim($relativePath, '/');
        $real = realpath($candidate);

        if (!$real || !$root || strpos($real, $root . DIRECTORY_SEPARATOR) !== 0) {
            abort(404);
        }

        return $real;
    }

    protected function overridePath($language, $relativePath)
    {
        $relative = preg_replace('/\.php$/', '.json', ltrim($relativePath, '/'));
        $path = storage_path('app/translations/' . $language . '/pages/' . $relative);
        $root = realpath(storage_path('app')) ?: storage_path('app');
        $directory = dirname($path);

        if (strpos($directory, $root) !== 0) {
            abort(404);
        }

        return $path;
    }
}
