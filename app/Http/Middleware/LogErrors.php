<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ErrorAddHelper;
use Throwable;

class LogErrors
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
        try {
            $response = $next($request);
        } catch (Throwable $e) {
            // Log the exception
            ErrorAddHelper::logException($e);

            // Rethrow the exception to propagate it further
            throw $e;
        }

        // Check if the response is a server error
        if ($response->isServerError()) {
            // Get the exception from the response
            $exception = $response->exception;

            // Check if the exception is not null
            if ($exception !== null) {
                // Log the exception
                ErrorAddHelper::logException($exception);
            }
        }

        return $response;
    }
}