<?php

namespace App\Notifications;

use App\Models\Goal;
use App\Services\Notifications\WebPushChannel;

class GoalApprovedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected Goal $goal
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('goal_approved') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '🎉 Goal redeemed!',
            'body'  => '"' . $this->goal->title . '" has been approved — enjoy your reward!',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'goal-approved-' . $this->goal->id,
            'url'   => '/kid/goals',
            'requireInteraction' => true,
        ];
    }
}
