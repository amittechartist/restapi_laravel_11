<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        if ($request->is('api/*') && !$request->expectsJson()) {
            $response->headers->set('Accept', 'application/json');
        }

        return $response;
    }
}
