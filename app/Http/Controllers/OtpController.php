<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function prompt()
    {
        $otp = random_int(100000, 999999);

        $user = Auth::user();
        $user->otp_code = $otp;
        $user->save();

        // Send OTP email
        Mail::to($user->email)->send(new \App\Mail\SendOtp($otp));

        return view('otp.prompt');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|numeric',
        ]);

        $user = Auth::user();

        if ($request->otp_code == $user->otp_code) {
            $request->session()->put('otp_verified', true);
            return redirect()->route('home');
        }

        return redirect()->back()->withErrors(['otp_code' => 'Invalid OTP code.']);
    }
}
