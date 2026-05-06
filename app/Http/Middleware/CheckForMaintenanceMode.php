<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

/**
 * Kept under the legacy class name (`CheckForMaintenanceMode`) so the existing
 * Kernel registration and any downstream references continue to work. The base
 * class was renamed to `PreventRequestsDuringMaintenance` in Laravel 8.
 */
class CheckForMaintenanceMode extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
