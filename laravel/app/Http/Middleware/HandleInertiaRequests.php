<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests
{
    public function handle(Request $request, \Closure $next)
    {
        // Share auth user with all views
        view()->share('authUser', Auth::user());
        
        return $next($request);
    }
}

