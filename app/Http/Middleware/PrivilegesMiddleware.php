<?php

namespace Firstwap\SmsApiDashboard\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class PrivilegesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @param  array  $privileges
     * @return mixed
     */
    public function handle($request, Closure $next, ...$privileges)
    {
        if(auth()->check()){
            if($user = auth()->user()){
                if($user->hasPrivileges($privileges)){
                    return $next($request);
                }
            }
        }

        throw new AuthorizationException(trans('app.unauthorized'));
    }
}
