<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // ─── Push Subscription — Parent ──────────────────────────────────────────

    /**
     * Save or update a push subscription for the authenticated parent.
     */
    public function parentSubscribe(Request $request)
    {
        $request->validate([
            'endpoint'                 => 'required|url',
            'keys.p256dh'              => 'required|string',
            'keys.auth'                => 'required|string',
        ]);

        $user = Auth::user();
        $user->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('keys.p256dh'),
            $request->input('keys.auth'),
        );

        return response()->json(['success' => true]);
    }

    /**
     * Remove a push subscription for the authenticated parent.
     */
    public function parentUnsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        Auth::user()->deletePushSubscription($request->input('endpoint'));

        return response()->json(['success' => true]);
    }

    // ─── Push Subscription — Kid ──────────────────────────────────────────────

    /**
     * Save or update a push subscription for the authenticated kid.
     */
    public function kidSubscribe(Request $request)
    {
        $request->validate([
            'endpoint'    => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth'   => 'required|string',
        ]);

        $kid = Auth::guard('kid')->user();
        $kid->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('keys.p256dh'),
            $request->input('keys.auth'),
        );

        return response()->json(['success' => true]);
    }

    /**
     * Remove a push subscription for the authenticated kid.
     */
    public function kidUnsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        Auth::guard('kid')->user()->deletePushSubscription($request->input('endpoint'));

        return response()->json(['success' => true]);
    }

    // ─── Notification Preferences — Parent ───────────────────────────────────

    /**
     * Return the current parent's merged notification preferences.
     */
    public function getPreferences(Request $request)
    {
        $user = Auth::user();

        return response()->json($user->mergedNotificationPreferences());
    }

    /**
     * Save updated notification preferences for the authenticated parent.
     *
     * Expected body: JSON object mapping event keys → { push, email, threshold? }
     * e.g. { "goal_created": { "push": true, "email": false } }
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
        ]);

        $user    = Auth::user();
        $allowed = array_keys(config('webpush.parent_defaults', []));
        $prefs   = [];

        foreach ($request->input('preferences') as $event => $settings) {
            if (!in_array($event, $allowed)) continue;

            $prefs[$event] = [
                'push'  => (bool) ($settings['push']  ?? false),
                'email' => (bool) ($settings['email'] ?? false),
            ];

            if (isset($settings['threshold'])) {
                $prefs[$event]['threshold'] = max(0, (float) $settings['threshold']);
            }
        }

        $user->notification_preferences = $prefs;
        $user->save();

        return response()->json(['success' => true]);
    }

    // ─── Notification Preferences — Kid ──────────────────────────────────────

    /**
     * Return the current kid's merged notification preferences.
     */
    public function getKidPreferences(Request $request)
    {
        $kid      = Auth::guard('kid')->user();
        $defaults = config('webpush.kid_defaults', []);
        $stored   = $kid->notification_preferences ?? [];

        $merged = [];
        foreach ($defaults as $event => $default) {
            $merged[$event] = array_merge($default, $stored[$event] ?? []);
        }

        return response()->json($merged);
    }

    /**
     * Save updated notification preferences for the authenticated kid.
     * Kids only have push (no email channel), so only 'push' is accepted.
     */
    public function updateKidPreferences(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
        ]);

        $kid     = Auth::guard('kid')->user();
        $allowed = array_keys(config('webpush.kid_defaults', []));
        $prefs   = [];

        foreach ($request->input('preferences') as $event => $settings) {
            if (!in_array($event, $allowed)) continue;

            $prefs[$event] = [
                'push' => (bool) ($settings['push'] ?? false),
            ];
        }

        $kid->notification_preferences = $prefs;
        $kid->save();

        return response()->json(['success' => true]);
    }
}
