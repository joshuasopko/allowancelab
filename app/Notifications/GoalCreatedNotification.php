<?php

namespace App\Notifications;

use App\Models\Goal;
use App\Models\Kid;
use App\Services\Notifications\WebPushChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalCreatedNotification extends Notification
{
    public function __construct(
        protected Kid  $kid,
        protected Goal $goal
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = [];

        if ($notifiable->wantsPush('goal_created') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        if ($notifiable->wantsEmail('goal_created')) {
            $channels[] = 'mail';
        }

        return $channels ?: []; // silently skip if all channels disabled
    }

    public function toWebPush(mixed $notifiable, mixed $notification): array
    {
        return [
            'title' => '🎯 ' . $this->kid->name . ' created a new goal',
            'body'  => '"' . $this->goal->title . '" — $' . number_format($this->goal->target_amount, 2) . ' goal',
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
            'tag'   => 'goal-created-' . $this->goal->id,
            'url'   => '/kids/' . $this->kid->id . '/goals',
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->kid->name . ' created a new savings goal')
            ->greeting('New goal alert!')
            ->line($this->kid->name . ' just created a new savings goal:')
            ->line('**' . $this->goal->title . '** — $' . number_format($this->goal->target_amount, 2))
            ->action('View Goal', url('/kids/' . $this->kid->id . '/goals'))
            ->line('Keep encouraging them to save!');
    }
}
