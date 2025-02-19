<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is an admin.
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }
        return $next($request);
    }
}
