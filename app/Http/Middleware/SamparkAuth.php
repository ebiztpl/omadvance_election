<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SamparkAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('sampark_user')) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'कृपया पहले लॉगिन करें।',
                    'redirect' => route('sampark.login')
                ], 401);
            }
            return redirect()->route('sampark.login')->with('error', 'कृपया पहले लॉगिन करें।');
        }

        return $next($request);
    }
}
