<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SectionContents;
use Illuminate\Http\Request;

class SectionsController extends Controller
{
    //
    public function delete(Section $section)
    {
        $this->authorize('delete', $section);
        $section->delete();
        return $section->id;
    }

    public function deleteContent(SectionContents $content)
    {
        $section = $content->section;
        $this->authorize('delete', $section);
        $content->delete();
        return $content->id;
    }
}
