<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\LinksSaveRequest;
use App\Models\Link;
use Illuminate\Support\Facades\DB;

class LinksController extends Controller
{
    public function index()
    {
        return Link::whereNull('link_id')->adminFormula()->get();
    }

    public function store(LinksSaveRequest $request)
    {
        DB::transaction(function () use ($request) {
            foreach ($request->input('links') as $link) {
                $this->saveLink($link, null);
            }
        });

        return $this->index();
    }

    public function destroy(Link $link)
    {
        $link->delete();

        return $link->id;
    }

    protected function saveLink(array $link, $parentId)
    {
        $payload = [
            'link' => $link['url'],
            'name__ar' => $link['pages']['AR']['name'],
            'name__GB' => $link['pages']['GB']['name'],
            'link_id' => $parentId,
        ];

        $saved = Link::find($link['id']);
        if ($saved) {
            $saved->update($payload);
        } else {
            $saved = Link::create($payload);
        }

        if (!empty($link['links'])) {
            foreach ($link['links'] as $child) {
                $this->saveLink($child, $saved->id);
            }
        }

        return $saved;
    }
}
