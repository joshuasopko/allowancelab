<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;

/**
 * Fired when a parent denies a wish purchase request.
 * Wired in when the Wish feature ships.
 */
class WishDeniedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected string $wishTitle,
        protected int    $wishId
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('wish_denied') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '❌ Wish denied',
            'body'  => 'Your request for "' . $this->wishTitle . '" was not approved',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'wish-denied-' . $this->wishId,
            'url'   => '/kid/dashboard',
        ];
    }
}
