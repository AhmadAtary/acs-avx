<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $module, $action)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        // Retrieve user permissions (Assuming it's stored as JSON in the database)
        $user = Auth::user();
        $permissions = json_decode($user->permissions, true); // Assuming 'permissions' is a column in the users table

        // Check if the module and action exist in permissions
        if (isset($permissions[$module][$action]) && $permissions[$module][$action] === true) {
            return $next($request);
        }

        // If permission is not granted, redirect to the 403 error page
        return redirect()->route('Errors.page-error-403');
    }
}
