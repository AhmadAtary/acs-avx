<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function loginView(){
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $user = Auth::user();

            // Reset OTP verification session status
            $request->session()->forget('otp_verified');

            // Check if the user has OTP verification enabled
            if ($user->is_otp_verified) {
                return redirect()->route('otp.prompt');
            }

            return redirect()->route('dashboard');
        }

        return redirect()->back()->withErrors(['email' => 'Invalid credentials.']);
    }

    

    public function logout()
    {
        Auth::logout(); // Log out the user
        return redirect('/login'); // Redirect to the login page
    }
}
