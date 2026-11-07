<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeFormCsrf
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if (!$request->input('_token') && $request->filled('nonce')) {
                $request->merge(['_token' => $request->input('nonce')]);
            }
        }

        return $next($request);
    }
}