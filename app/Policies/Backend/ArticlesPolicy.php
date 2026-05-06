<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\Article;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlesPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-articles');
    }

    public function view(Admin $user, Article $article)
    {
        return $this->viewAny($user);
    }

    public function create(Admin $user)
    {
        return $user->can('create-articles');
    }

    public function update(Admin $user, Article $article)
    {
        return $user->can('edit-articles');
    }

    public function delete(Admin $user, Article $article)
    {
        return $user->can('delete-articles');
    }
}
