<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEndUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Adjust this based on how you store login state (e.g., session, cookie)
        if (!$request->session()->has('end_user_authenticated')) {
            return redirect()->route('end.user.login.show')->with('error', 'You must be logged in.');
        }

        return $next($request);
    }
}
