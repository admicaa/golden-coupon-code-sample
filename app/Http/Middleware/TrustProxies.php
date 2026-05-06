<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Loaded from the `TRUSTED_IP_ADDRESS` env via `config('app.trusted')`
     * to preserve the original behavior. Returning `null` from the constructor
     * is not allowed by the parent contract; we lazily merge in `proxies()`.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $configured = (array) config('app.trusted', []);
        $this->proxies = array_values(array_filter($configured, static fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * `Request::HEADER_X_FORWARDED_ALL` was removed in Symfony 5.2. We list the
     * individual flags we want to honour. Excludes HEADER_X_FORWARDED_PREFIX,
     * which the legacy ALL constant did not include.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_AWS_ELB;
}
