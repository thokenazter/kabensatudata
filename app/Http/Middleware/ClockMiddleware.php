<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ClockMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rekam waktu mulai
        $start = microtime(true);

        // Proses request
        $response = $next($request);

        // Rekam waktu selesai dan hitung durasi
        $end = microtime(true);
        $executionTime = ($end - $start) * 1000; // dalam ms

        // Tambahkan header X-Execution-Time untuk debugging
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');

        // Log jika melebihi threshold (misalnya 200ms)
        if ($executionTime > 200) {
            Log::channel('daily')->warning("Endpoint lambat: {$request->path()}", [
                'time' => round($executionTime, 2) . 'ms',
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'query_params' => $request->query(),
                'user_id' => $request->user() ? $request->user()->id : 'guest',
            ]);
        }

        return $response;
    }
}
