<?php

namespace App\Notifications;

use App\Services\Notifications\WebPushChannel;

class MoneyDeductedNotification extends BaseWebPushNotification
{
    public function __construct(
        protected float  $amount,
        protected string $note = ''
    ) {}

    public function via(mixed $notifiable): array
    {
        if (!$notifiable->wantsPush('money_deducted') || !$notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        $body = '$' . number_format($this->amount, 2) . ' was deducted from your account';
        if ($this->note) {
            $body .= ' — ' . $this->note;
        }

        return [
            'title' => '📉 Money deducted',
            'body'  => $body,
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'money-deducted',
            'url'   => '/kid/dashboard',
        ];
    }
}
