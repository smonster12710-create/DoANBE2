<?php

namespace App\Services;

use App\Events\UserActivityUpdated;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserActivityService
{
    public function markOnline(User $user): array
    {
        $user->forceFill([
            'is_online' => true,
            'last_activity_at' => now(),
        ])->save();

        $payload = $user->fresh()->activityStatusPayload();
        $this->broadcast($user, $payload);

        return $payload;
    }

    public function markOffline(User $user): array
    {
        $user->forceFill([
            'is_online' => false,
            'last_activity_at' => now(),
        ])->save();

        $payload = $user->fresh()->activityStatusPayload();
        $this->broadcast($user, $payload);

        return $payload;
    }

    public function broadcastCurrentStatus(User $user): array
    {
        $payload = $user->fresh()->activityStatusPayload();
        $this->broadcast($user, $payload);

        return $payload;
    }

    public function visibleFriendActivities(User $viewer): array
    {
        $friendIds = $this->friendIds($viewer);

        if (empty($friendIds)) {
            return [];
        }

        return User::whereIn('id', $friendIds)
            ->where('show_activity_status', true)
            ->get()
            ->map(fn (User $friend) => $friend->activityStatusFor($viewer))
            ->values()
            ->all();
    }

    private function broadcast(User $user, array $payload): void
    {
        $receiverIds = $this->activityReceiverIds($user);

        if (!empty($receiverIds)) {
            try {
                broadcast(new UserActivityUpdated($payload, $receiverIds))->toOthers();
            } catch (\Throwable $exception) {
                Log::warning('Khong the broadcast trang thai hoat dong.', [
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function activityReceiverIds(User $user): array
    {
        $friendIds = $this->friendIds($user);

        $receiverIds = User::whereIn('id', $friendIds)
            ->where('show_activity_status', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $receiverIds[] = (int) $user->id;

        return $receiverIds;
    }

    private function friendIds(User $user): array
    {
        return DB::table('friendships')
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend_id', $user->id);
            })
            ->get()
            ->map(function ($friendship) use ($user) {
                return (int) $friendship->user_id === (int) $user->id
                    ? (int) $friendship->friend_id
                    : (int) $friendship->user_id;
            })
            ->all();
    }
}
