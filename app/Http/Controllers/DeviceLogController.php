<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceLog;
use Illuminate\Support\Facades\Auth;

class DeviceLogController extends Controller
{
    public static function saveDeviceLog($action, $response = null, $device_id)
    {
        DeviceLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'response'   => $response,
            'device_id'  => $device_id,
            'created_at' => now(),
        ]);
    }

    public function getLogs($device_id, Request $request)
    {
        $perPage = 10;
        $logs = DeviceLog::with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->where('device_id', $device_id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Map logs to extract only required data
        $logsData = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'username' => $log->user->name ?? 'Unknown',
                'action' => $log->action,
                'response' => $log->response,
                'device_id' => $log->device_id,
                'created_at' => $log->created_at,
            ];
        });

        return response()->json([
            'logs' => $logsData,
            'totalPages' => $logs->lastPage(),
            'currentPage' => $logs->currentPage(),
        ]);
    }

    
}
