@extends('layouts.page')

@section('title', 'About - AllowanceLab')

@section('page-hero')
<div class="page-hero">
    <div class="page-hero-label">Our Story</div>
    <h1 class="page-hero-title">Built for real families, with real purpose.</h1>
    <p class="page-hero-subtitle">AllowanceLab started at a kitchen table — not in a boardroom.</p>
</div>
@endsection

@section('content')
@push('styles')
<style>
    .about-section {
        max-width: 860px;
        margin: 0 auto;
        padding: 64px 40px 80px;
    }

    .about-lead {
        font-size: 21px;
        color: #334155;
        line-height: 1.8;
        margin-bottom: 32px;
        font-weight: 400;
    }

    .about-story p {
        font-size: 17px;
        color: #475569;
        line-height: 1.85;
        margin-bottom: 20px;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin: 48px 0;
    }

    .value-card {
        padding: 28px 24px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        text-align: center;
    }

    .value-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #10b981, #059669);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        margin: 0 auto 16px;
    }

    .value-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .value-desc {
        font-size: 14px;
        color: #64748b;
        line-height: 1.65;
    }

    .founder-strip {
        background: linear-gradient(135deg, #0f172a 0%, #022c22 55%, #064e3b 100%);
        border-radius: 20px;
        padding: 40px 44px;
        display: flex;
        align-items: center;
        gap: 24px;
        margin: 48px 0;
    }

    .founder-avatar-lg {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        color: white;
        flex-shrink: 0;
    }

    .founder-strip-text p {
        font-size: 16px;
        color: #cbd5e1;
        line-height: 1.75;
        margin-bottom: 0;
    }

    .founder-strip-name {
        font-size: 14px;
        color: #6ee7b7;
        font-weight: 700;
        margin-top: 10px !important;
    }

    .about-cta {
        text-align: center;
        padding: 48px 0 0;
        border-top: 1px solid #e5e7eb;
    }

    .about-cta h2 {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
    }

    .about-cta p {
        color: #64748b;
        margin-bottom: 28px;
    }

    .btn-cta-green {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        padding: 14px 36px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(16,185,129,0.4);
        transition: all 0.25s;
    }

    .btn-cta-green:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16,185,129,0.5);
        color: white;
        text-decoration: none;
    }

    @media (max-width: 700px) {
        .about-section { padding: 40px 20px 60px; }
        .values-grid { grid-template-columns: 1fr; }
        .founder-strip { flex-direction: column; text-align: center; padding: 28px 24px; }
        .about-lead { font-size: 18px; }
    }
</style>
@endpush

<div class="about-section">
    <p class="about-lead">AllowanceLab was created because managing a family's finances — even at the allowance level — deserved a real system. Not a spreadsheet. Not a whiteboard. A tool built around how families actually work.</p>

    <div class="about-story">
        <p>Every week, Joshua Sopko was manually transferring money to his kids, trying to remember who got what, and wondering whether the kids actually appreciated it or just expected it. He wanted his kids to understand that money was <em>earned</em> — that responsibility mattered, and that saving toward something meaningful was worth the wait.</p>

        <p>AllowanceLab was built to answer that problem. What started as an internal tool for one family's kitchen-table conversations became something worth sharing with every family trying to do the same thing.</p>

        <p>It's free. It's honest. And it's designed around the way real families think about money — not the way software companies think about subscriptions.</p>
    </div>

    <div class="values-grid">
        <div class="value-card">
            <div class="value-icon"><i class="fas fa-hammer"></i></div>
            <div class="value-title">Earn</div>
            <p class="value-desc">Allowance isn't automatic. Kids learn that effort and responsibility connect directly to reward.</p>
        </div>
        <div class="value-card">
            <div class="value-icon"><i class="fas fa-book-open"></i></div>
            <div class="value-title">Learn</div>
            <p class="value-desc">Real financial habits form through real decisions — saving, spending, and giving with intention.</p>
        </div>
        <div class="value-card">
            <div class="value-icon"><i class="fas fa-seedling"></i></div>
            <div class="value-title">Grow</div>
            <p class="value-desc">The habits kids build today shape the adults they'll become. This is life training, not just allowance tracking.</p>
        </div>
    </div>

    <div class="founder-strip">
        <div class="founder-avatar-lg">JS</div>
        <div class="founder-strip-text">
            <p>"I built this because I wanted my kids to understand that money is earned, not given. AllowanceLab is what I wish had existed when I started."</p>
            <p class="founder-strip-name">— Joshua Sopko, Father &amp; Founder</p>
        </div>
    </div>

    <div class="about-cta">
        <h2>Ready to get started?</h2>
        <p>Set up your family in 5 minutes. Free forever — no credit card required.</p>
        <a href="{{ route('register') }}" class="btn-cta-green">
            <i class="fas fa-rocket"></i> Create Your Family
        </a>
    </div>
</div>
@endsection
