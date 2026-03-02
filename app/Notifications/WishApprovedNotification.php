<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;

/**
 * Fired when a parent approves a wish purchase request.
 */
class WishApprovedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected string $wishTitle,
        protected int    $wishId
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('wish_approved') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '🛒 Wish approved!',
            'body'  => '"' . $this->wishTitle . '" has been approved — enjoy!',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'wish-approved-' . $this->wishId,
            'url'   => '/kid/wishes',
        ];
    }
}
