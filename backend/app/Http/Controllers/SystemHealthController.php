<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemHealthController extends Controller
{
    /**
     * System health check endpoint for monitoring uptime
     * Returns 200 if healthy, 503 if there are issues
     */
    public function health(): JsonResponse
    {
        $startTime = microtime(true);
        $checks = [];
        $isHealthy = true;
        
        // 1. Database connection check
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'healthy',
                'response_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ];
        } catch (\Exception $e) {
            $isHealthy = false;
            $checks['database'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
        
        // 2. Cache check
        try {
            $cacheStart = microtime(true);
            Cache::put('health_check', 'ok', 10);
            $cacheValue = Cache::get('health_check');
            $checks['cache'] = [
                'status' => $cacheValue === 'ok' ? 'healthy' : 'degraded',
                'response_time' => round((microtime(true) - $cacheStart) * 1000, 2) . 'ms'
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
        
        // 3. Storage check
        try {
            $storagePath = storage_path('app');
            $isWritable = File::isWritable($storagePath);
            $checks['storage'] = [
                'status' => $isWritable ? 'healthy' : 'unhealthy',
                'path' => $storagePath
            ];
            if (!$isWritable) $isHealthy = false;
        } catch (\Exception $e) {
            $checks['storage'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $isHealthy = false;
        }
        
        // 4. Check recent attendance records (system is active)
        try {
            $recentCount = DB::table('attendance_records')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->count();
            
            $checks['activity'] = [
                'status' => 'healthy',
                'records_today' => $recentCount
            ];
        } catch (\Exception $e) {
            $checks['activity'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
        
        // 5. Check student count
        try {
            $studentCount = DB::table('students')->count();
            $checks['students'] = [
                'status' => 'healthy',
                'count' => $studentCount
            ];
        } catch (\Exception $e) {
            $checks['students'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
        
        // 6. Check session status
        try {
            $now = Carbon::now()->format('H:i:s');
            $activeSession = DB::table('sessions')
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->exists();
            
            $checks['session'] = [
                'status' => 'healthy',
                'active' => $activeSession
            ];
        } catch (\Exception $e) {
            $checks['session'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
        
        $totalResponseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $response = [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => Carbon::now()->toIso8601String(),
            'uptime' => $this->getUptime(),
            'response_time_ms' => $totalResponseTime,
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks
        ];
        
        $statusCode = $isHealthy ? 200 : 503;
        
        return response()->json($response, $statusCode);
    }
    
    /**
     * Simple ping endpoint for basic uptime monitoring
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => Carbon::now()->toIso8601String()
        ]);
    }
    
    /**
     * Get system uptime (requires server monitoring)
     */
    private function getUptime(): array
    {
        // For Linux, you can read /proc/uptime
        if (PHP_OS === 'Linux' && File::exists('/proc/uptime')) {
            $uptime = (int) File::get('/proc/uptime');
            return [
                'seconds' => $uptime,
                'formatted' => $this->formatUptime($uptime)
            ];
        }
        
        // Fallback: return app start time
        return [
            'seconds' => 0,
            'formatted' => 'unknown'
        ];
    }
    
    /**
     * Format uptime seconds to human readable
     */
    private function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }
}