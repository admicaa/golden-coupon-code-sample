<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\TranslationFiles;
use Illuminate\Auth\Access\HandlesAuthorization;

class TranslationFilesPolicy
{
    use HandlesAuthorization;

    public function update(Admin $user, TranslationFiles $file)
    {
        return $user->can('translate-' . $file->language);
    }
}
