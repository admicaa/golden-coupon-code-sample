<?php

namespace App\Services\Navigation;

use App\Models\Link;
use Illuminate\Support\Facades\DB;

class LinkTreeService
{
    public function save(array $links)
    {
        return DB::transaction(function () use ($links) {
            foreach ($links as $link) {
                $this->saveLink($link, null);
            }
        });
    }

    public function saveLink(array $link, $parentId)
    {
        $saved = Link::find($link['id'] ?? null);

        if ($saved) {
            $saved->update($this->payload($link, $parentId));
        } else {
            $saved = Link::create($this->payload($link, $parentId));
        }

        foreach ((array) ($link['links'] ?? []) as $child) {
            $this->saveLink($child, $saved->id);
        }

        return $saved;
    }

    public function payload(array $link, $parentId)
    {
        $payload = [
            'link' => $link['url'],
            'link_id' => $parentId,
        ];

        foreach ((array) ($link['pages'] ?? []) as $language => $page) {
            $payload['name__' . strtoupper($language)] = $page['name'] ?? null;
        }

        return $payload;
    }
}
