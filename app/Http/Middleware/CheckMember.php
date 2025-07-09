<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMember
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('registration_id') || session('admin_role') !== 'member') {
            return redirect('/login')->with('error', 'कृपया पहले मेंबर लॉगिन करें।');
        }

        return $next($request);
    }
}