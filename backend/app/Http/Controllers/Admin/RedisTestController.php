<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RedisQueueTestJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class RedisTestController extends Controller
{
    public function cacheTest()
    {
        $key = 'redis_cache_test:sample';
        $value = [
            'message' => 'Redis cache is working',
            'time' => now()->toDateTimeString(),
        ];

        Cache::put($key, $value, now()->addMinutes(10));
        $cached = Cache::get($key);

        return response()->json([
            'ok' => !empty($cached),
            'driver' => config('cache.default'),
            'key' => $key,
            'value' => $cached,
        ]);
    }

    public function queueTest(Request $request)
    {
        $message = (string) $request->input('message', 'Queue test dispatched');

        RedisQueueTestJob::dispatch($message);

        return response()->json([
            'ok' => true,
            'message' => 'Queue job dispatched.',
            'queue_connection' => config('queue.default'),
            'redis_connection' => config('queue.connections.redis.connection'),
            'queue_name' => config('queue.connections.redis.queue'),
        ]);
    }

    public function queueStatus()
    {
        $queueName = (string) config('queue.connections.redis.queue', 'default');
        $redisConnection = (string) config('queue.connections.redis.connection', 'queue');
        $queueKey = "queues:{$queueName}";

        $pending = Redis::connection($redisConnection)->llen($queueKey);
        $lastProcessed = Cache::get('redis_queue_test:last_processed');

        return response()->json([
            'ok' => true,
            'queue_name' => $queueName,
            'redis_connection' => $redisConnection,
            'pending_jobs' => (int) $pending,
            'last_processed' => $lastProcessed,
        ]);
    }
}
