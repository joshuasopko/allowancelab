/**
 * AllowanceLab — Web Push Notification Manager
 *
 * Handles: SW registration, VAPID key URL-safe base64 decode,
 * permission prompt, subscription save/remove via API.
 *
 * Usage:
 *   import { PushManager } from './push-notifications.js';
 *   PushManager.init({ subscribeUrl, unsubscribeUrl, userType });
 */

const PushManager = {
    subscribeUrl:   null,
    unsubscribeUrl: null,

    /**
     * Convert a URL-safe base64 string to a Uint8Array (needed by VAPID key).
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw     = window.atob(base64);
        return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
    },

    /**
     * Initialise: register service worker and optionally auto-prompt.
     */
    async init({ subscribeUrl, unsubscribeUrl }) {
        this.subscribeUrl   = subscribeUrl;
        this.unsubscribeUrl = unsubscribeUrl;

        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('[Push] Web Push not supported in this browser.');
            return false;
        }

        try {
            await navigator.serviceWorker.register('/sw.js');
            return true;
        } catch (err) {
            console.error('[Push] SW registration failed:', err);
            return false;
        }
    },

    /**
     * Current browser permission state.
     */
    permissionState() {
        return Notification.permission; // 'default' | 'granted' | 'denied'
    },

    /**
     * Request permission and subscribe this device. Returns true on success.
     */
    async subscribe() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return false;

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return false;

        const publicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
        if (!publicKey) {
            console.error('[Push] Missing VAPID public key meta tag.');
            return false;
        }

        try {
            const registration   = await navigator.serviceWorker.ready;
            const existingSub    = await registration.pushManager.getSubscription();
            const subscription   = existingSub || await registration.pushManager.subscribe({
                userVisibleOnly:      true,
                applicationServerKey: this.urlBase64ToUint8Array(publicKey),
            });

            const subJson = subscription.toJSON();

            await fetch(this.subscribeUrl, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    endpoint: subJson.endpoint,
                    keys:     subJson.keys,
                }),
            });

            return true;
        } catch (err) {
            console.error('[Push] Subscription failed:', err);
            return false;
        }
    },

    /**
     * Unsubscribe this device and delete from server.
     */
    async unsubscribe() {
        if (!('serviceWorker' in navigator)) return;

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (!subscription) return;

            await fetch(this.unsubscribeUrl, {
                method:  'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ endpoint: subscription.endpoint }),
            });

            await subscription.unsubscribe();
        } catch (err) {
            console.error('[Push] Unsubscribe failed:', err);
        }
    },

    /**
     * Check whether this device currently has an active push subscription.
     */
    async isSubscribed() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return false;
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            return !!subscription;
        } catch {
            return false;
        }
    },
};

export { PushManager };
