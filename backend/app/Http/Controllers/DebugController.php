<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function resetDb(Request $request)
    {
        $request->user()?->loadMissing('role');
        $role = strtolower((string) optional($request->user()->role)->name);

        if ($role !== 'admin') {
            return response()->json([
                'message' => 'Reset is restricted to admin users.',
            ], 403);
        }

        return response()->json([
            'message' => 'Reset action is disabled in API mode. Run migrate:fresh --seed from the backend CLI.',
        ]);
    }
}
