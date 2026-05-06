<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TranslationFiles;
use App\Services\Translations\TranslationFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class TranslationFilesController extends Controller
{
    protected $translationFiles;

    public function __construct(TranslationFileService $translationFiles)
    {
        $this->translationFiles = $translationFiles;
    }

    public function getFiles($language)
    {
        $language = $this->normaliseLanguage($language);
        $this->authorizeLanguage($language);

        return $this->translationFiles->listFiles();
    }

    public function get(Request $request)
    {
        [$language, $relativePath] = $this->validatePathRequest($request);

        return $this->translationFiles->load($language, $relativePath);
    }

    public function saveFile(Request $request)
    {
        [$language, $relativePath] = $this->validatePathRequest($request);
        $existing = $this->translationFiles->load($language, $relativePath);
        $rules = $this->buildRulesFromExisting($existing);

        $validated = $this->validate($request, array_merge([
            'data' => 'required|array',
        ], $rules));

        $saved = $this->translationFiles->save($language, $relativePath, $validated['data']);
        Cache::forget('lang.' . $language);

        return $saved;
    }

    protected function validatePathRequest(Request $request)
    {
        $allowedLanguages = language_shortcuts();
        $request->merge([
            'language' => strtoupper((string) $request->input('language')),
        ]);

        $data = $this->validate($request, [
            'language' => ['required', Rule::in($allowedLanguages)],
            'file' => ['required', 'string', 'regex:/^[A-Za-z0-9_\-\/]+\.php$/'],
        ]);

        $this->authorizeLanguage($data['language']);
        $this->translationFiles->assertEditableFile($data['file']);

        return [$data['language'], $data['file']];
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

    protected function normaliseLanguage($language)
    {
        $language = strtoupper((string) $language);
        $allowed = language_shortcuts();

        return in_array($language, $allowed, true) ? $language : abort(404);
    }

    protected function authorizeLanguage($language)
    {
        $fake = new TranslationFiles();
        $fake->language = $language;

        $this->authorize('update', $fake);
    }
}
