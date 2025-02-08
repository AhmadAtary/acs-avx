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
            'email'    => 'required|email',
            'password' => 'required',
        ]);


        $user = User::where('email', $request->email)->first();


        if ($user && \Hash::check($request->password, $user->password)) {
            Auth::login($user);

            // Check if OTP verification is needed
            if (!$user->is_otp_verified) {
                return $this->sendOtp($user);
            }

            // Redirect based on user role
            return $this->redirectToDashboard($user);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
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

    private function redirectToDashboard(User $user)
    {
        switch ($user->role) {
            case 'owner':
                return redirect()->intended('/owner-dashboard');
            case 'eng':
                return redirect()->intended('/engineer-dashboard');
            case 'cs':
                return redirect()->intended('/cs-dashboard');
            default:
                return redirect()->intended('/dashboard');
        }
    }
}
