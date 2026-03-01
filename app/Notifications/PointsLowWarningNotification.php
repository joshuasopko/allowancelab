<?php

namespace App\Notifications;

use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PointsLowWarningNotification extends Notification
{
    public function __construct(
        protected Kid    $kid,
        protected string $allowanceDay // e.g. 'Monday'
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        if ($notifiable->wantsPush('points_low_warning') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->wantsEmail('points_low_warning')) {
            $channels[] = 'mail';
        }

        return $channels ?: [];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '⚡ Points low — ' . $this->kid->name,
            'body'  => $this->kid->name . ' has ' . $this->kid->points . '/' . $this->kid->max_points . ' points before ' . $this->allowanceDay . '\'s allowance',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'points-low-' . $this->kid->id,
            'url'   => '/dashboard',
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . '\'s points are running low')
            ->greeting('Points warning!')
            ->line($this->kid->name . ' currently has **' . $this->kid->points . ' out of ' . $this->kid->max_points . ' points**.')
            ->line('Their allowance day is **' . $this->allowanceDay . '**. They need at least 1 point to receive their allowance.')
            ->action('Manage Points', url('/dashboard'))
            ->line('You can adjust points from the parent dashboard.');
    }
}
