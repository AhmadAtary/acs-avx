<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EngineerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If the user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('auth.login');
        }

        // Check user role and restrict access for unauthorized roles
        if ($user->access->role !== 'owner' && $user->access->role !== 'eng') {
            return redirect()->route('Errors.page-error-403'); // Redirect to 403 page for unauthorized access
        }

        // If user has the correct role, allow access
        return $next($request);
    }
}
