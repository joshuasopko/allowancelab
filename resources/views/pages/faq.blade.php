@extends('layouts.page')

@section('title', 'FAQ - AllowanceLab')

@section('page-hero')
<div class="page-hero">
    <div class="page-hero-label">Frequently Asked Questions</div>
    <h1 class="page-hero-title">Got questions? We've got answers.</h1>
    <p class="page-hero-subtitle">Everything you need to know about AllowanceLab.</p>
</div>
@endsection

@section('content')
@push('styles')
<style>
    .faq-section {
        max-width: 760px;
        margin: 0 auto;
        padding: 64px 40px 80px;
    }

    .faq-group { margin-bottom: 48px; }

    .faq-group-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #10b981;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #d1fae5;
    }

    .faq-item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .faq-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); }

    .faq-question {
        width: 100%;
        background: none;
        border: none;
        padding: 20px 24px;
        text-align: left;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        font-family: inherit;
        transition: background 0.15s;
    }

    .faq-question:hover { background: #f8fafc; }

    .faq-question.open { background: #f0fdf4; color: #065f46; }

    .faq-chevron {
        color: #94a3b8;
        font-size: 13px;
        flex-shrink: 0;
        transition: transform 0.25s;
    }

    .faq-question.open .faq-chevron {
        transform: rotate(180deg);
        color: #10b981;
    }

    .faq-answer {
        display: none;
        padding: 0 24px 20px;
        font-size: 15px;
        color: #475569;
        line-height: 1.8;
        border-top: 1px solid #f1f5f9;
    }

    .faq-answer.open { display: block; }

    .faq-answer p { margin-bottom: 10px; }
    .faq-answer p:last-child { margin-bottom: 0; }

    .faq-cta {
        text-align: center;
        padding: 48px 32px;
        background: #f0fdf4;
        border: 1px solid #d1fae5;
        border-radius: 16px;
        margin-top: 48px;
    }

    .faq-cta h3 {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .faq-cta p { color: #64748b; margin-bottom: 20px; }

    .faq-cta a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #10b981;
        color: white;
        padding: 12px 28px;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        font-size: 15px;
        transition: background 0.2s;
    }

    .faq-cta a:hover { background: #059669; text-decoration: none; color: white; }

    @media (max-width: 700px) {
        .faq-section { padding: 40px 20px 60px; }
        .faq-question { font-size: 15px; padding: 16px 18px; }
        .faq-answer { padding: 0 18px 16px; }
    }
</style>
@endpush

<div class="faq-section">

    <div class="faq-group">
        <div class="faq-group-title">Getting Started</div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Is AllowanceLab really free?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Yes — completely free. No plans, no paywalls, no credit card required. AllowanceLab is free for families forever. We believe teaching kids financial responsibility shouldn't cost anything.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                How long does setup take?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Most parents are fully set up in under 5 minutes. Create your account, add your kids, set their allowance amount and day, and you're done. The system takes it from there.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                How do I add my kids?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>From your parent dashboard, create a kid profile with their name and allowance settings. You'll get a unique invite link to share with your child so they can set up their own login username and password.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Can multiple parents manage the same family?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Yes! You can invite other parents or guardians to your family from the Manage Family screen. They'll receive an email invite and, once accepted, have full access to manage your kids' accounts alongside you.</p>
            </div>
        </div>
    </div>

    <div class="faq-group">
        <div class="faq-group-title">Allowances &amp; Points</div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                How does the points system work?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Each kid starts every week with a full set of points (default: 10). Parents can deduct points for missed expectations or add bonus points for exceptional behavior at any time. When allowance day arrives, kids need at least 1 point remaining for their allowance to post.</p>
                <p>After allowance is evaluated, points reset back to full for the next week — whether they earned allowance or not.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                When does allowance post automatically?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Allowances are processed at 2:00 AM Central Time on each kid's configured allowance day. You choose the day of the week (Monday through Sunday) and the amount for each child independently.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Can I turn off the points system for a specific kid?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Yes. The points system can be disabled per child from their individual settings. When disabled, allowance posts automatically on the configured day regardless of points — no strings attached.</p>
            </div>
        </div>
    </div>

    <div class="faq-group">
        <div class="faq-group-title">Kid Accounts &amp; Logins</div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Can kids log in on their own device?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Yes! Kids log in at <strong>allowancelab.com/kid/login</strong> using their username and password from any device with a browser. You can also add AllowanceLab to your home screen as an app (PWA) for quick one-tap access — no app store required.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                What if my kid forgets their password?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Parents can reset a child's password at any time from the kid management screen. There's no email required on the kid's end — parents are the account administrators for their children's profiles.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                What can kids do in their dashboard?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Kids can view their current balance, see their full transaction history, track savings goals, and record deposits or spending they've initiated. They can also customize their profile avatar color. Everything is age-appropriate and designed to be easy to use independently.</p>
            </div>
        </div>
    </div>

    <div class="faq-group">
        <div class="faq-group-title">Savings Goals</div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                How do savings goals work?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Kids (or parents) create a savings goal with a target amount — like "New Bike: $120." They can manually add funds to the goal, or set up an auto-allocation percentage that automatically moves a portion of each allowance payment into the goal.</p>
                <p>When the goal is fully funded, the kid can request redemption and a parent approves it, releasing the funds.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Can parents create goals for kids?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>Yes. Both parents and kids can create savings goals. Parents can set up goals from the kid management screen, set auto-allocation percentages, and manage fund transfers into or out of any goal.</p>
            </div>
        </div>
    </div>

    <div class="faq-group">
        <div class="faq-group-title">Privacy &amp; Security</div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Is my family's data safe?
                <i class="fas fa-chevron-down faq-chavron"></i>
            </button>
            <div class="faq-answer">
                <p>AllowanceLab uses industry-standard security practices including encrypted passwords and HTTPS. We never sell your data or share it with third parties for advertising. See our <a href="{{ route('privacy') }}">Privacy Policy</a> for full details.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Does AllowanceLab move real money?
                <i class="fas fa-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-answer">
                <p>No. AllowanceLab is a ledger system — it tracks balances and transactions digitally, but no real money moves through the platform. Parents act as "the bank" and handle actual cash or transfers outside the app. This keeps things simple and age-appropriate.</p>
            </div>
        </div>
    </div>

    <div class="faq-cta">
        <h3>Still have questions?</h3>
        <p>We're happy to help. Reach out and we'll get back to you quickly.</p>
        <a href="{{ route('contact') }}"><i class="fas fa-envelope"></i> Contact Us</a>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function toggleFaq(btn) {
        const answer = btn.nextElementSibling;
        const isOpen = btn.classList.contains('open');
        // Close all
        document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('open'));
        document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('open'));
        // Toggle clicked
        if (!isOpen) {
            btn.classList.add('open');
            answer.classList.add('open');
        }
    }
</script>
@endpush
