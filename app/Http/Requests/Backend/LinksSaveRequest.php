<?php

namespace App\Http\Requests\Backend;

use App\Models\Link;
use Illuminate\Foundation\Http\FormRequest;

class LinksSaveRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()
            && $this->user()->can('create', Link::class);
    }

    public function rules()
    {
        $rules = $this->linkRules('links');

        $links = $this->input('links', []);
        foreach ($links as $index => $link) {
            if (!empty($link['links'])) {
                $rules = array_merge($rules, $this->linkRules('links.' . $index . '.links'));
            }
        }

        return $rules;
    }

    protected function linkRules($prefix)
    {
        return [
            $prefix => 'required|array|min:1',
            $prefix . '.*.url' => 'required|string|max:191',
            $prefix . '.*.id' => 'required',
            $prefix . '.*.pages' => 'required|array|min:1',
            $prefix . '.*.pages.GB' => 'required|array',
            $prefix . '.*.pages.GB.name' => 'required|string|max:191',
            $prefix . '.*.pages.AR' => 'required|array',
            $prefix . '.*.pages.AR.name' => 'required|string|max:191',
        ];
    }
}
