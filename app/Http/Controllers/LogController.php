<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public static function saveLog($action, $response = null)
    {
        Log::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'response'   => $response,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function getLogs($user_id)
    {
        // Fetch logs related to the given user ID
        $logs = Log::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
    
        // Return the logs as a JSON response
        return response()->json($logs);
    }
}
