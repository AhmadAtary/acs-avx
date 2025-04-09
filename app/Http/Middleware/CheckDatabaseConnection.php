<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\Client as MongoClient;
use Exception;

class CheckDatabaseConnection
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check MySQL database connection
            DB::connection('mysql')->getPdo();
        } catch (Exception $e) {
            Log::error('MySQL Database Connection Failed: ' . $e->getMessage());
            return response()->view('Errors.503', [], 503);
        }

        try {
            // Check MongoDB database connection
            $mongoClient = new MongoClient(config('database.connections.mongodb.dsn'));
            $mongoClient->listDatabases(); // Attempt to list databases as a health check
        } catch (Exception $e) {
            Log::error('MongoDB Connection Failed: ' . $e->getMessage());
            return response()->view('Errors.503', [], 503);
        }

        return $next($request);
    }
}
