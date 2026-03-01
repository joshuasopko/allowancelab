<?php

namespace App\Notifications;

use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AllowanceProcessedNotification extends Notification
{
    public function __construct(
        protected Kid   $kid,
        protected bool  $awarded,
        protected float $amount = 0
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        if ($notifiable->wantsPush('allowance_processed') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->wantsEmail('allowance_processed')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        if ($this->awarded) {
            return [
                'title' => '✅ Allowance posted for ' . $this->kid->name,
                'body'  => '$' . number_format($this->amount, 2) . ' has been added to their account',
                'icon'  => '/icon-192.png',
                'badge' => '/icon-192.png',
                'tag'   => 'allowance-' . $this->kid->id,
                'url'   => '/dashboard',
            ];
        }

        return [
            'title' => '⚠️ Allowance denied for ' . $this->kid->name,
            'body'  => $this->kid->name . ' had too few points — allowance was not awarded',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'allowance-' . $this->kid->id,
            'url'   => '/dashboard',
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        if ($this->awarded) {
            return (new MailMessage)
                ->subject('Allowance posted for ' . $this->kid->name)
                ->greeting('Allowance processed!')
                ->line('$' . number_format($this->amount, 2) . ' has been automatically deposited into ' . $this->kid->name . '\'s account.')
                ->action('View Dashboard', url('/dashboard'));
        }

        return (new MailMessage)
            ->subject('Allowance denied for ' . $this->kid->name)
            ->greeting('Allowance not awarded.')
            ->line($this->kid->name . ' had insufficient points this week, so their allowance was not posted.')
            ->line('Current points: ' . $this->kid->points . ' / ' . $this->kid->max_points)
            ->action('View Dashboard', url('/dashboard'));
    }
}
