<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LanguageController extends Controller
{
    public function languageFiles($lang)
    {
        $lang = Str::upper($lang);
        $allowed = config('app.supported_languages', ['GB', 'AR']);

        if (!in_array($lang, $allowed, true)) {
            abort(404);
        }

        return Cache::remember('lang.' . $lang, now()->addMinutes(15), function () use ($lang) {
            $directory = config('app.env') === 'local'
                ? resource_path('langMain/en/pages')
                : resource_path('lang/' . $lang . '/pages');

            $real = realpath($directory);
            $root = realpath(resource_path('lang'));
            $rootMain = realpath(resource_path('langMain')) ?: null;

            $insideRoot = $real && $root && strpos($real, $root . DIRECTORY_SEPARATOR) === 0;
            $insideMain = $real && $rootMain && strpos($real, $rootMain . DIRECTORY_SEPARATOR) === 0;

            if (!$real || (!$insideRoot && !$insideMain)) {
                abort(404);
            }

            return $this->loadDirectory($real);
        });
    }

    protected function loadDirectory($directory)
    {
        $strings = [];

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $name = $file->getFilenameWithoutExtension();
            $contents = require $file->getRealPath();
            if (!is_array($contents)) {
                continue;
            }

            if ($name === 'index') {
                $strings = array_merge($contents, $strings);
            } else {
                $strings[$name] = $contents;
            }
        }

        foreach (File::directories($directory) as $sub) {
            $strings[basename($sub)] = $this->loadDirectory($sub);
        }

        return $strings;
    }
}
