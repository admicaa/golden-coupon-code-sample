<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\Translations\TranslationFileService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LanguageController extends Controller
{
    public function languageFiles($lang, TranslationFileService $translationFiles)
    {
        $lang = Str::upper($lang);
        $allowed = language_shortcuts();

        if (!in_array($lang, $allowed, true)) {
            abort(404);
        }

        return Cache::remember('lang.' . $lang, now()->addMinutes(15), function () use ($lang, $translationFiles) {
            return $translationFiles->exportLanguageTree($lang);
        });
    }
}
