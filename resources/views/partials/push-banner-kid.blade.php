@auth('kid')
{{-- Push notification permission banner for kids — shown after 3s when permission not yet decided --}}
<div id="kidPushBanner"
     style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:9999;
            background:#6366f1; color:#fff; padding:12px 20px;
            align-items:center; gap:12px; justify-content:space-between;
            box-shadow:0 -2px 12px rgba(0,0,0,0.15); font-family:inherit;">
    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
        <i class="fas fa-bell" style="font-size:18px; flex-shrink:0;"></i>
        <span style="font-size:14px; font-weight:500;">
            Get notified when your allowance posts, goals are approved, and more!
        </span>
    </div>
    <div style="display:flex; gap:8px; flex-shrink:0;">
        <button id="kidPushBannerEnable"
                style="background:#fff; color:#6366f1; border:none; border-radius:6px;
                       padding:8px 14px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap;">
            Enable
        </button>
        <button id="kidPushBannerDismiss"
                style="background:transparent; color:#fff; border:1px solid rgba(255,255,255,0.6);
                       border-radius:6px; padding:8px 12px; font-size:13px; cursor:pointer; white-space:nowrap;">
            Not now
        </button>
    </div>
</div>

<script>
(function () {
    var DISMISS_KEY = 'kidPushBannerDismissed';
    var banner = document.getElementById('kidPushBanner');

    if (!banner) return;
    if (!('Notification' in window)) return;
    if (Notification.permission !== 'default') return;
    if (sessionStorage.getItem(DISMISS_KEY)) return;

    // Wait for Vite bundle (kid-dashboard.js) to register window.PushManager
    setTimeout(async function () {
        if (!window.PushManager) return;

        var ready = await window.PushManager.init({
            subscribeUrl:   '/kid/notifications/subscribe',
            unsubscribeUrl: '/kid/notifications/subscribe',
        });

        if (ready && Notification.permission === 'default') {
            banner.style.display = 'flex';
        }
    }, 3500);

    document.getElementById('kidPushBannerEnable').addEventListener('click', async function () {
        banner.style.display = 'none';
        if (!window.PushManager) return;
        await window.PushManager.init({
            subscribeUrl:   '/kid/notifications/subscribe',
            unsubscribeUrl: '/kid/notifications/subscribe',
        });
        var success = await window.PushManager.subscribe();
        if (success) {
            console.log('[Push] Kid subscribed successfully.');
        }
    });

    document.getElementById('kidPushBannerDismiss').addEventListener('click', function () {
        banner.style.display = 'none';
        sessionStorage.setItem(DISMISS_KEY, '1');
    });
}());
</script>
@endauth('kid')
