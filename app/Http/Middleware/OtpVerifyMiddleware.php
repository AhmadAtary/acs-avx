<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OtpVerifyMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // If the user is not authenticated, let Laravel's auth middleware handle it
        if (!$user) {
            return redirect()->route('auth.login');
        }

        // dd(session()->get('otp_verified'));
        // If OTP verification is required but not completed
        if ($user->is_otp_verified == "1" && !$request->session()->get('otp_verified', false)) {
            // Prevent redirect loop for OTP page
            if ($request->route()->getName() !== 'otp.prompt') {
                return redirect()->route('otp.prompt');
            }
        }

        // If OTP is already verified, allow access
        return $next($request);
    }
}
