<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;

class AllowanceReceivedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected float $amount
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('allowance_received') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '🎉 Allowance posted!',
            'body'  => '$' . number_format($this->amount, 2) . ' has been added to your account',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'allowance-received',
            'url'   => '/kid/dashboard',
            'requireInteraction' => true,
        ];
    }
}
