<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMultipartPutSupport
{
    public function handle(Request $request, Closure $next): Response
    {
        $usesMultipartPut = in_array($request->method(), ['PUT', 'PATCH'], true)
            && str_starts_with(strtolower((string) $request->header('Content-Type')), 'multipart/form-data');

        if ($usesMultipartPut && PHP_VERSION_ID < 80400) {
            return response()->json([
                'success' => false,
                'message' => 'Server PHP belum mendukung PUT multipart. Gunakan POST dengan field _method=PUT atau perbarui PHP ke 8.4+.',
            ], 415);
        }

        return $next($request);
    }
}
