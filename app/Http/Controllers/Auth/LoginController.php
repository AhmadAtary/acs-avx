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
    
            if ($user->is_otp_verified) {
                return redirect()->route('otp.prompt');
            }
    
            return redirect()->route('home');
        }
    
        return redirect()->back()->withErrors(['email' => 'Invalid credentials.']);
    }
    

    private function sendOtp(User $user)
    {
        // Generate a new OTP
        $otp = rand(100000, 999999);

        // Store OTP in the database
        Otp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp_code' => $otp,
                'attempts' => 0,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );

        // Send OTP via email
        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your OTP Code');
        });

        return redirect()->route('otp.verify')->with('success', 'OTP has been sent to your email.');
    }

    public function logout()
    {
        Auth::logout(); // Log out the user
        return redirect('/login'); // Redirect to the login page
    }
}
