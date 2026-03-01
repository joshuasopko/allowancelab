<?php

namespace App\Notifications;

use App\Models\Goal;
use App\Services\Notifications\WebPushChannel;

class GoalDeniedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected Goal $goal
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('goal_denied') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '❌ Redemption denied',
            'body'  => 'Your parent denied the redemption for "' . $this->goal->title . '"',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'goal-denied-' . $this->goal->id,
            'url'   => '/kid/goals',
        ];
    }
}
