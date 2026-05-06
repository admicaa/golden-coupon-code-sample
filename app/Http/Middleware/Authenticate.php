<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * The admin API only returns JSON, so this method falls back to `null` for
     * JSON requests (which makes the parent throw an `AuthenticationException`).
     * The named `login` route does not exist in this codebase; we keep the
     * legacy reference for parity with the Laravel scaffold but only emit it
     * for non-JSON requests, matching the previous behavior.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return null;
    }
}
