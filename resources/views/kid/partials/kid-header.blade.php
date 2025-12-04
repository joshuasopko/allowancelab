<!-- Kid Header -->
<header class="kid-header">
    <div class="kid-logo-section">
        <img src="{{ asset('/images/Allowance-Lab-logo.png') }}" alt="AllowanceLab" class="logo-img">
    </div>

    <!-- Desktop Family Badge -->
    <div class="kid-family-badge">
        You're part of the&nbsp;<span
            style="color: {{ $kid->color }}; font-weight: 600;">{{ explode(' ', $kid->family->owner->name)[1] ?? 'Family' }}</span>
        &nbsp;family!
    </div>

    <button class="kid-hamburger" id="kidHamburger" onclick="kidToggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>