<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;

class PointsAdjustedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected int    $change,
        protected int    $newPoints,
        protected string $reason = ''
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('points_adjusted') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        $sign  = $this->change >= 0 ? '+' : '';
        $emoji = $this->change >= 0 ? '⭐' : '⚠️';
        $body  = 'Your points changed by ' . $sign . $this->change . ' — now at ' . $this->newPoints;
        if ($this->reason) {
            $body .= ' (' . $this->reason . ')';
        }

        return [
            'title' => $emoji . ' Points updated',
            'body'  => $body,
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'points-adjusted',
            'url'   => '/kid/dashboard',
        ];
    }
}
