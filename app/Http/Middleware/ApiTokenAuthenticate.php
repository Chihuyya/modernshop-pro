<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization') ?? '';
        
        $token = trim(str_replace('Bearer', '', $authHeader));

        if (empty($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak ditemukan! Anda tidak memiliki akses bray.'
            ], 401);
        }

        // Otentikasi menggunakan random token dinamis yang valid di Cache
        if (!Cache::has('api_token_' . $token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token kedaluwarsa atau tidak valid! Silakan login ulang.'
            ], 403);
        }

        return $next($request);
    }
}
