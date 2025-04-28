<?php

namespace App\Http\Middleware;

use App\CPU\CustomerAuthHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {

        if (Auth::guard('sanctum')->check()) {
            CustomerAuthHelper::setConfig();

            return $next($request);
        }
        return $next($request);
    }
}
