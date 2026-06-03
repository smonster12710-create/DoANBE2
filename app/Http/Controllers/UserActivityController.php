<?php

namespace App\Http\Controllers;

use App\Services\UserActivityService;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function online(Request $request, UserActivityService $activityService)
    {
        return response()->json([
            'success' => true,
            'activity' => $activityService->markOnline($request->user()),
        ]);
    }

    public function offline(Request $request, UserActivityService $activityService)
    {
        return response()->json([
            'success' => true,
            'activity' => $activityService->markOffline($request->user()),
        ]);
    }
}
