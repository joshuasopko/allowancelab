<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;

class MoneyAddedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected float  $amount,
        protected string $note = ''
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('money_added') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        $body = '$' . number_format($this->amount, 2) . ' was added to your account';
        if ($this->note) {
            $body .= ' — ' . $this->note;
        }

        return [
            'title' => '💰 Money added!',
            'body'  => $body,
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'money-added',
            'url'   => '/kid/dashboard',
        ];
    }
}
