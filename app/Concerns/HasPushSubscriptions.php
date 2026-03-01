<?php

namespace App\Concerns;

use App\Models\PushSubscription;

trait HasPushSubscriptions
{
    /**
     * All push subscriptions for this notifiable (one per device).
     */
    public function pushSubscriptions()
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }

    /**
     * Save or update a push subscription for this device endpoint.
     */
    public function updatePushSubscription(string $endpoint, ?string $publicKey = null, ?string $authToken = null, string $contentEncoding = 'aesgcm'): PushSubscription
    {
        return $this->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'public_key'       => $publicKey,
                'auth_token'       => $authToken,
                'content_encoding' => $contentEncoding,
            ]
        );
    }

    /**
     * Remove a push subscription by endpoint.
     */
    public function deletePushSubscription(string $endpoint): void
    {
        $this->pushSubscriptions()->where('endpoint', $endpoint)->delete();
    }

    /**
     * Retrieve all active push subscriptions as objects compatible with minishlink/web-push.
     */
    public function routeNotificationForWebPush(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->pushSubscriptions;
    }

    /**
     * Merge stored notification preferences with the config defaults.
     * Returns the effective preference for a given event key.
     *
     * @param  string  $event  e.g. 'goal_created', 'allowance_processed'
     * @return array           e.g. ['push' => true, 'email' => false]
     */
    public function getNotificationPreference(string $event): array
    {
        $configKey = $this instanceof \App\Models\Kid ? 'webpush.kid_defaults' : 'webpush.parent_defaults';
        $defaults  = config($configKey . '.' . $event, ['push' => false, 'email' => false]);
        $stored    = $this->notification_preferences[$event] ?? [];

        return array_merge($defaults, $stored);
    }

    /**
     * Check whether push is enabled for a given event.
     */
    public function wantsPush(string $event): bool
    {
        return (bool) ($this->getNotificationPreference($event)['push'] ?? false);
    }

    /**
     * Check whether email is enabled for a given event (parents only).
     */
    public function wantsEmail(string $event): bool
    {
        return (bool) ($this->getNotificationPreference($event)['email'] ?? false);
    }

    /**
     * Check whether a money amount meets the user's threshold for a given event.
     */
    public function meetsThreshold(string $event, float $amount): bool
    {
        $pref      = $this->getNotificationPreference($event);
        $threshold = $pref['threshold'] ?? null;

        if ($threshold === null) {
            return true; // no threshold set — always fire
        }

        return $amount >= (float) $threshold;
    }
}
