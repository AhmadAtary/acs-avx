<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LogController; 
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function loginView(){
        return view('Auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    
        $remember = $request->has('remember');
        $ipAddress = $request->ip(); // Get user's IP address
    
        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $user = Auth::user();
    
            // Reset OTP verification session status
            $request->session()->forget('otp_verified');
    
            // Check if the user has OTP verification enabled
            if ($user->is_otp_verified) {
                LogController::saveLog('login_attempt', "User {$user->email} attempted login from IP: {$ipAddress}, OTP required.");
                return redirect()->route('otp.prompt');
            }
    
            // Log successful login without OTP
            LogController::saveLog('login_success', "User {$user->email} logged in without OTP from IP: {$ipAddress}");
    
            return redirect()->route('dashboard');
        }
    
        // Log failed login attempt
        // LogController::saveLog('login_failed', "Failed login attempt for email: {$request->email} at " . now());
    
        return redirect()->back()->withErrors(['email' => 'Invalid credentials.']);
    }
    
    public function logout()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $ipAddress = request()->ip(); // Get user's IP address
            LogController::saveLog('logout', "User {$user->email} logged out from IP: {$ipAddress} at " . now());
        }
    
        Auth::logout();
        return redirect('/login');
    }
    
}
