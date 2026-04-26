<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StoreImages;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MainPageSectionsService
{
    protected $contentColumns = [
        'coupon' => ['coupon_id'],
        'store' => ['store_id'],
        'country' => ['country_id'],
        'article' => ['page_id'],
        'other' => ['image_id', 'url'],
    ];

    public function save(array $sections, $column, $rowValue)
    {
        DB::transaction(function () use ($sections, $column, $rowValue) {
            foreach ($sections as $index => $payload) {
                $section = $this->upsertSection($payload, $index, $column, $rowValue);
                $this->saveContents($section, $payload['contents'] ?? []);
                $this->savePages($section, $payload['pages'] ?? []);
            }
        });
    }

    protected function upsertSection(array $payload, $index, $column, $rowValue)
    {
        $section = null;

        if (!empty($payload['id'])) {
            $section = Section::where($column, $rowValue)
                ->where('id', $payload['id'])
                ->first();
        }

        $attributes = [
            'sort' => $index,
            'template' => $payload['template'],
            'is_blog' => filter_var($payload['is_blog'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        if (!$section) {
            $attributes[$column] = $rowValue;
            return Section::create($attributes);
        }

        $section->update($attributes);

        return $section;
    }

    protected function saveContents(Section $section, array $contents)
    {
        foreach ($contents as $index => $content) {
            if (($content['type'] ?? null) === 'other') {
                $content = $this->persistImageContent($content);
            }

            $record = !empty($content['id'])
                ? $section->contents()->find($content['id'])
                : $section->contents()->create(['sort' => $index]);

            if (!$record) {
                continue;
            }

            $columns = $this->contentColumns[$content['type']] ?? [];
            $payload = ['sort' => $index];
            foreach ($columns as $column) {
                $payload[$column] = $content[$column] ?? null;
            }

            $record->update($payload);
        }
    }

    protected function persistImageContent(array $content)
    {
        $image = !empty($content['image_id'])
            ? StoreImages::findOrFail($content['image_id'])
            : null;

        $imageData = $content['image'] ?? [];
        $imageData['path'] = '/storage/' . ltrim($imageData['path'] ?? '', '/');

        $upload = $imageData['image'][0] ?? null;
        if ($upload instanceof UploadedFile) {
            if ($image && $image->storage_path) {
                $absolute = realpath(storage_path('app/' . ltrim($image->storage_path, '/')));
                $root = realpath(storage_path('app'));
                if ($absolute && $root && strpos($absolute, $root . DIRECTORY_SEPARATOR) === 0 && File::exists($absolute)) {
                    File::delete($absolute);
                }
            }
            $stored = $upload->store('sections');
            $imageData['storage_path'] = ltrim(str_replace('/storage/', '', Storage::url($stored)), '/');
        }

        $payload = [
            'path' => $imageData['path'],
            'image_path' => url($imageData['path']),
            'title' => $imageData['title'] ?? null,
            'alt' => $imageData['alt'] ?? null,
            'storage_path' => $imageData['storage_path'] ?? ($image->storage_path ?? null),
        ];

        if (!$image) {
            $image = StoreImages::create($payload);
        } else {
            $image->update($payload);
        }

        $content['image_id'] = $image->id;

        return $content;
    }

    protected function savePages(Section $section, array $pagesPayload)
    {
        foreach (languages() as $language) {
            if (!isset($pagesPayload[$language->shortcut])) {
                continue;
            }

            $section->pages()->updateOrCreate(
                [
                    'section_id' => $section->id,
                    'language' => $language->shortcut,
                ],
                array_intersect_key(
                    $pagesPayload[$language->shortcut],
                    array_flip(['title', 'subtitle', 'description'])
                )
            );
        }
    }
}
