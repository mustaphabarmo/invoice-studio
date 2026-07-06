<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException; // <-- CRITICAL IMPORT: Needed to manually throw the exception
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * This is only used for web/browser requests. For API requests, we return null.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return Route::has('login') ? route('login') : null;
    }

    /**
     * Handle an unauthenticated user, ensuring a 401 JSON response for API guards.
     *
     * This method is triggered when authentication fails. We override it to look
     * specifically at the failed guards (e.g., 'sanctum') and throw the exception
     * needed for a JSON 401 response, bypassing default web redirection.
     */
    protected function unauthenticated($request, array $guards)
    {
        // Identify the guards typically used for APIs
        $apiGuards = ['sanctum', 'api', 'admin']; 

        // Check if any of the guards that failed authentication are considered API guards.
        if (collect($guards)->intersect($apiGuards)->isNotEmpty()) {
            
            // Explicitly throw the AuthenticationException. The Laravel Exception
            // Handler will catch this and render the standard: {"message": "Unauthenticated."}
            // because an API guard was involved.
            throw new AuthenticationException(
                'Unauthenticated.', $guards
            );
        }

        // Fallback to the parent's default handling (usually a redirect) for non-API guards.
        parent::unauthenticated($request, $guards);
    }
}
