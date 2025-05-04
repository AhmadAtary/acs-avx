<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OtpController extends Controller
{
    public function prompt()
    {
        $user = Auth::user();
    
        // Prevent access to OTP page if OTP is already verified
        if (session('otp_verified', false)) {
            return redirect()->route('dashboard');
        }
    
        // Check if an existing unexpired OTP is present
        $otp = DB::table('otps')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();
    
        if (!$otp) {
            // Generate a new OTP if none exists or if the OTP has expired
            $otp_code = random_int(100000, 999999);
    
            // Store the OTP in the `otps` table
            DB::table('otps')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'otp_code' => $otp_code,
                    'attempts' => 0,
                    'expires_at' => now()->addMinutes(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    
            // Send OTP email
            \Mail::to($user->email)->send(new \App\Mail\SendOtp($user, $otp_code));
        }
    
        return view('Otp.prompt');
    }
    
    public function resend()
    {
        $user = Auth::user();

        // Generate a new OTP
        $otp_code = random_int(100000, 999999);

        // Update or insert the new OTP in the `otps` table
        DB::table('otps')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'otp_code' => $otp_code,
                'attempts' => 0,
                'expires_at' => now()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Send OTP email
        \Mail::to($user->email)->send(new \App\Mail\SendOtp($user, $otp_code));

        return redirect()->back()->with('success', 'A new OTP has been sent to your email.');
    }

    public function verify(Request $request)
    {
        // dd($request);
        $request->validate([
            'otp_code' => 'required|numeric',
        ], [
            'otp_code.required' => 'OTP Code is required.',
            'otp_code.numeric' => 'OTP Code must be numeric.',
        ]);
    
        $user = Auth::user();
        $otp = DB::table('otps')->where('user_id', $user->id)->latest('created_at')->first();
        
        // dd($request);

        if (!$otp) {
            return redirect()->back()->withErrors(['otp_code' => 'No OTP record found.']);
        }
    
        // Check if OTP is expired
        if (now()->greaterThan($otp->expires_at)) {
            return redirect()->back()->withErrors(['otp_code' => 'OTP has expired.']);
        }
    
        
        // Check OTP code
        if ($request->otp_code == $otp->otp_code) {
            // Mark OTP as verified in session
            $request->session()->put('otp_verified', true);
    
            // Delete the OTP record from the database
            DB::table('otps')->where('id', $otp->id)->delete();
    
            // Redirect to the intended page
            return redirect()->route('dashboard')->with('success', 'OTP verified successfully!');
        }
    
        // Increment OTP attempts if the code is invalid
        DB::table('otps')->where('id', $otp->id)->increment('attempts');
    
        return redirect()->back()->withErrors(['otp_code' => 'Invalid OTP code.']);
    }
    
    
}
