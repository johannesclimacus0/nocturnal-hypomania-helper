<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAuthenticationRequests
{
    public function __construct(protected ThrottleRequests $throttle) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('register.store')) {
            return $this->throttle->handle($request, $next, 'registration');
        }

        if ($request->routeIs('password.email')) {
            return $this->throttle->handle($request, $next, 'password-reset');
        }

        return $next($request);
    }
}
