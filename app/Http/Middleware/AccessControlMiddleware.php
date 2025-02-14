<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Access;

class AccessControlMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        $user = Auth::user();
        $access = Access::where('user_id', $user->id)->first();

        if ($access && json_decode($access->permissions)->$permission) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
