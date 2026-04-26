<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TranslationFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class TranslationFilesController extends Controller
{
    public function getFiles($language)
    {
        $language = $this->normaliseLanguage($language);
        $this->authorizeLanguage($language);

        $directory = $this->languageRoot($language);
        if (!File::isDirectory($directory)) {
            abort(404);
        }

        return $this->collectFiles($directory);
    }

    public function get(Request $request)
    {
        [$language, $relativePath] = $this->validatePathRequest($request);
        $absolutePath = $this->resolveSafePath($language, $relativePath);

        return $this->loadTranslations($absolutePath);
    }

    public function saveFile(Request $request)
    {
        [$language, $relativePath] = $this->validatePathRequest($request);
        $absolutePath = $this->resolveSafePath($language, $relativePath);

        $existing = $this->loadTranslations($absolutePath);
        $rules = $this->buildRulesFromExisting($existing);

        $validated = $this->validate($request, array_merge([
            'data' => 'required|array',
        ], $rules));

        $payload = '<?php return ' . var_export($validated['data'], true) . ';' . PHP_EOL;
        File::put($absolutePath, $payload);

        return $validated['data'];
    }

    protected function validatePathRequest(Request $request)
    {
        $allowedLanguages = languages()->pluck('shortcut')->all();

        $data = $this->validate($request, [
            'language' => ['required', Rule::in($allowedLanguages)],
            'file' => ['required', 'string', 'regex:/^[A-Za-z0-9_\-\/]+\.php$/'],
        ]);

        $this->authorizeLanguage($data['language']);

        return [$data['language'], $data['file']];
    }

    protected function resolveSafePath($language, $relativePath)
    {
        $root = $this->languageRoot($language);
        $candidate = $root . DIRECTORY_SEPARATOR . ltrim($relativePath, '/');
        $real = realpath($candidate);
        $rootReal = realpath($root);

        if (!$real || !$rootReal || strpos($real, $rootReal . DIRECTORY_SEPARATOR) !== 0) {
            abort(404);
        }

        return $real;
    }

    protected function loadTranslations($path)
    {
        $contents = require $path;

        return is_array($contents) ? $contents : [];
    }

    protected function buildRulesFromExisting(array $data, $prefix = 'data')
    {
        $rules = [$prefix => 'required|array'];

        foreach ($data as $key => $value) {
            $rules[$prefix . '.' . $key] = is_array($value) ? 'required|array' : 'required|string';
            if (is_array($value)) {
                $rules = array_merge($rules, $this->buildRulesFromExisting($value, $prefix . '.' . $key));
            }
        }

        return $rules;
    }

    protected function collectFiles($directory)
    {
        $list = [];
        foreach (File::files($directory) as $file) {
            if ($file->getExtension() === 'php') {
                $list[$file->getFilename()] = true;
            }
        }
        foreach (File::directories($directory) as $sub) {
            $list[basename($sub)] = $this->collectFiles($sub);
        }

        return $list;
    }

    protected function languageRoot($language)
    {
        return resource_path('lang/' . $language . '/pages');
    }

    protected function normaliseLanguage($language)
    {
        $allowed = languages()->pluck('shortcut')->all();

        return in_array($language, $allowed, true) ? $language : abort(404);
    }

    protected function authorizeLanguage($language)
    {
        $fake = new TranslationFiles();
        $fake->language = $language;

        $this->authorize('update', $fake);
    }
}
