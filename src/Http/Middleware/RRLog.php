<?php

namespace SkyaTura\LaravelRRLog\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RRLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->startTime = microtime(true);

        $response = $next($request);

        $this->endTime = microtime(true);

        $this->log($request, $response);

        return $response;
    }

    private function log($request, $response)
    {
        $log_request = [
            "url" => $request->fullUrl(),
            "parameters" => $request->all(),
            "headers" => $request->header(),
        ];
        $log_response = [
            "headers" => $response->headers->all(),
            "body" => $response->original,
        ];

        DB::table('laravel_rrlogs')->insert([
            "origin" => $request->ip(),
            "method" => $request->method(),
            "status" => $response->status(),
            "request" => json_encode($log_request),
            "response" => json_encode($log_response),
            "duration" => $this->endTime - $this->startTime,
        ]);
    }

}