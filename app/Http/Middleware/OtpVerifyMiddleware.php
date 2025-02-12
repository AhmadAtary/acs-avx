<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OtpVerifyMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->is_otp_verified && !$request->session()->get('otp_verified', false)) {
            return redirect()->route('otp.prompt');
        }

        return $next($request);
    }
}
