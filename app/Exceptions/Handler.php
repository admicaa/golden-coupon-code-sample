<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /** @var array<int, class-string<\Throwable>> */
    protected $dontReport = [];

    /** @var array<int, string> */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        return parent::render($request, $e);
    }
}
