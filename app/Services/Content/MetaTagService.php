<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;

class MetaTagService
{
    public function sync($page, array $tags, $touchPage = false)
    {
        return DB::transaction(function () use ($page, $tags, $touchPage) {
            foreach ($tags as $tag) {
                $type = $tag['type'] ?? 1;

                if (!empty($tag['id'])) {
                    $metaTag = $page->metatags()->where('id', $tag['id'])->firstOrFail();
                    $metaTag->update([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                    continue;
                }

                $page->metatags()->create([
                    'name' => $tag['name'],
                    'value' => $tag['value'],
                    'type' => $type,
                ]);
            }

            if ($touchPage && method_exists($page, 'touch')) {
                $page->touch();
            }

            return $page->fresh('metatags')->metatags;
        });
    }
}
