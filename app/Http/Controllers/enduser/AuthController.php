<?php

namespace App\Http\Controllers\enduser;

use App\Http\Controllers\Controller;
use App\Models\EndUserLink;
use App\Http\Controllers\LogController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin($token)
    {
        $link = EndUserLink::where('token', $token)->first();

        if (!$link || $link->is_used || Carbon::now()->gte($link->expires_at)) {
            // LogController::saveLog('end_user_login_failed', "Invalid or expired link accessed with token: {$token}");
            // Clear session if link is expired
            if ($link && Carbon::now()->gte($link->expires_at)) {
                session()->forget(['end_user_authenticated', 'end_user_username']);
            }
            return view('End-user-link.login', ['token' => $token, 'serialNumber' => null])
                ->with('error', 'Invalid or expired link');
        }

        // LogController::saveLog('end_user_login_page_access', "User accessed end-user login page with token: {$token}");
        return view('End-user-link.login', ['token' => $token, 'serialNumber' => $link->username]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $token = $validated['token'];
        $username = $validated['username'];
        $password = $validated['password'];

        $link = EndUserLink::where('token', $token)->first();

        if (!$link || $link->is_used || Carbon::now()->gte($link->expires_at)) {
            // LogController::saveLog('end_user_login_failed', "Invalid or expired link for username: {$username}");
            // Clear session if link is expired
            if ($link && Carbon::now()->gte($link->expires_at)) {
                session()->forget(['end_user_authenticated', 'end_user_username']);
            }
            return redirect()->view('End-user-link.login', ['token' => $token])
                ->with('error', 'Invalid or expired link');
        }

        if ($link->username === $username && Hash::check($password, $link->password)) {
            $link->update(['is_used' => true]);
            session(['end_user_authenticated' => true, 'end_user_username' => $username]);
            // LogController::saveLog('end_user_login_success', "End-user login successful for username: {$username}");
            return redirect()->route('device.show', ['url_Id' => $username]);
        }

        // LogController::saveLog('end_user_login_failed', "Invalid credentials for username: {$username}");
        return redirect()->back()->with('error', 'Invalid credentials');
    }
}
