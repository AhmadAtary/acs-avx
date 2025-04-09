<?php

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

if (!function_exists('saveLog')) {
    function saveLog($action, $response = null)
    {
        Log::create([
            'user_id'   => Auth::id(),
            'action'    => $action,
            'response'  => $response,
            'ip_address'=> request()->ip(),
            'created_at' => now(),
        ]);
    }
}
