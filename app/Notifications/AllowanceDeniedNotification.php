<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;

class AllowanceDeniedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected int $points,
        protected int $maxPoints
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('allowance_denied') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '😔 No allowance this week',
            'body'  => 'Your points were too low (' . $this->points . '/' . $this->maxPoints . '). Keep earning points!',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'allowance-denied',
            'url'   => '/kid/dashboard',
        ];
    }
}
