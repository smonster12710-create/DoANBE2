<?php

namespace App\Http\Controllers;

use App\Services\UserActivityService;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function online(Request $request, UserActivityService $activityService)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'activity' => $activityService->markOnline($user),
            'activities' => $activityService->visibleFriendActivities($user),
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
