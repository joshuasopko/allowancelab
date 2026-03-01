@auth
{{-- Push notification permission banner — shown after 3s when browser permission not yet decided --}}
<div id="pushBanner"
     style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:9999;
            background:#10b981; color:#fff; padding:12px 20px;
            align-items:center; gap:12px; justify-content:space-between;
            box-shadow:0 -2px 12px rgba(0,0,0,0.15); font-family:inherit;">
    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
        <i class="fas fa-bell" style="font-size:18px; flex-shrink:0;"></i>
        <span style="font-size:14px; font-weight:500;">
            Get notified when your kids take action — allowances, goals, and more.
        </span>
    </div>
    <div style="display:flex; gap:8px; flex-shrink:0;">
        <button id="pushBannerEnable"
                style="background:#fff; color:#10b981; border:none; border-radius:6px;
                       padding:8px 14px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap;">
            Enable Notifications
        </button>
        <button id="pushBannerDismiss"
                style="background:transparent; color:#fff; border:1px solid rgba(255,255,255,0.6);
                       border-radius:6px; padding:8px 12px; font-size:13px; cursor:pointer; white-space:nowrap;">
            Not now
        </button>
    </div>
</div>

<script>
(function () {
    var DISMISS_KEY = 'pushBannerDismissed';
    var banner = document.getElementById('pushBanner');

    if (!banner) return;
    if (!('Notification' in window)) return;
    if (Notification.permission !== 'default') return;
    if (sessionStorage.getItem(DISMISS_KEY)) return;

    // Wait for Vite bundle (dashboard.js) to register window.PushManager, then show banner
    setTimeout(async function () {
        if (!window.PushManager) return;

        var ready = await window.PushManager.init({
            subscribeUrl:   '{{ route("notifications.subscribe") }}',
            unsubscribeUrl: '{{ route("notifications.unsubscribe") }}',
        });

        if (ready && Notification.permission === 'default') {
            banner.style.display = 'flex';
        }
    }, 3000);

    document.getElementById('pushBannerEnable').addEventListener('click', async function () {
        banner.style.display = 'none';
        if (!window.PushManager) return;
        await window.PushManager.init({
            subscribeUrl:   '{{ route("notifications.subscribe") }}',
            unsubscribeUrl: '{{ route("notifications.unsubscribe") }}',
        });
        var success = await window.PushManager.subscribe();
        if (success) {
            console.log('[Push] Parent subscribed successfully.');
        }
    });

    document.getElementById('pushBannerDismiss').addEventListener('click', function () {
        banner.style.display = 'none';
        sessionStorage.setItem(DISMISS_KEY, '1');
    });
}());
</script>
@endauth
