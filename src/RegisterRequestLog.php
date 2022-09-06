<?php

namespace HAOC\Logstash;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegisterRequestLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $context['request']  = $this->getRequestProperties($request);
            $context['user'] = $this->getUserProperties($request);
            Log::channel('logstash')->info('Request Log', $context);
            return $next($request);
        } catch (Exception $e) {
            Log::emergency($e->getMessage());
            return $next($request);
        }
    }

    private function getRequestProperties(Request $request)
    {
        $requestProperties = [
            'route_name' => $request->route()->getName(),
            'uri' => $request->path(),
            'method' => $request->getMethod(),
            'ip' => $request->ip()
        ];

        match ($requestProperties['method']) {
            'GET' => $requestProperties['params'] = $request->query(),
            default => $requestProperties['body'] = (array) json_decode($request->getContent())
        };

        return  $requestProperties;
    }

    private function getUserProperties(Request $request)
    {
        $user =  $request->user();

        if (!$user->exists) return [];

        return [
            'id' => $user->identifier,
            'phone' => $user->phone,
            'email' => $user->email
        ];
    }
}
