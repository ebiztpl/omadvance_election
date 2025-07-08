<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLogin
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('logged_in_user')) {
            return redirect('/login');
        }

        return $next($request);
    }
}
