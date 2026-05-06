<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /** @var array<int, string>|string|null */
    protected $proxies;

    public function __construct()
    {
        $configured = (array) config('app.trusted', []);
        $this->proxies = array_values(array_filter($configured, static fn ($value) => $value !== null && $value !== ''));
    }

    /** @var int */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_AWS_ELB;
}
