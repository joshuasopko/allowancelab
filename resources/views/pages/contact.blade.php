@extends('layouts.page')

@section('title', 'Contact - AllowanceLab')

@section('page-hero')
<div class="page-hero">
    <div class="page-hero-label">Get In Touch</div>
    <h1 class="page-hero-title">We'd love to hear from you.</h1>
    <p class="page-hero-subtitle">Questions, feedback, or just want to say hi — we read every message.</p>
</div>
@endsection

@section('content')
@push('styles')
<style>
    .contact-section {
        max-width: 860px;
        margin: 0 auto;
        padding: 64px 40px 80px;
        display: grid;
        grid-template-columns: 1fr 1.6fr;
        gap: 60px;
        align-items: start;
    }

    /* Left info column */
    .contact-info h2 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
    }

    .contact-info p {
        font-size: 15px;
        color: #64748b;
        line-height: 1.75;
        margin-bottom: 28px;
    }

    .contact-detail {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 20px;
    }

    .contact-detail-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #10b981, #059669);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 15px;
        flex-shrink: 0;
    }

    .contact-detail-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .contact-detail-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
    }

    .contact-detail-value {
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
    }

    .contact-detail-value a {
        color: #10b981;
        text-decoration: none;
    }

    .contact-detail-value a:hover { text-decoration: underline; }

    /* Form */
    .contact-form-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 36px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }

    .contact-form-card h3 {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 24px;
    }

    .form-group { margin-bottom: 18px; }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 7px;
    }

    .form-input, .form-textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        color: #0f172a;
        background: #fafafa;
        font-family: inherit;
        transition: all 0.2s;
    }

    .form-input:focus, .form-textarea:focus {
        outline: none;
        border-color: #10b981;
        background: white;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
    }

    .form-textarea { resize: vertical; min-height: 130px; }

    .submit-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.25s;
        box-shadow: 0 4px 14px rgba(16,185,129,0.4);
        margin-top: 4px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16,185,129,0.5);
    }

    .success-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        color: #065f46;
        font-size: 15px;
        font-weight: 500;
    }

    .success-banner i { color: #10b981; font-size: 18px; }

    .error-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        color: #991b1b;
        font-size: 14px;
    }

    @media (max-width: 700px) {
        .contact-section {
            grid-template-columns: 1fr;
            padding: 40px 20px 60px;
            gap: 36px;
        }
    }
</style>
@endpush

<div class="contact-section">
    <!-- Left: info -->
    <div class="contact-info">
        <h2>Let's talk.</h2>
        <p>Whether you have a question about the app, a feature idea, or just want to share how AllowanceLab is working for your family — we want to hear it.</p>

        <div class="contact-detail">
            <div class="contact-detail-icon"><i class="fas fa-envelope"></i></div>
            <div class="contact-detail-text">
                <span class="contact-detail-label">Email</span>
                <span class="contact-detail-value"><a href="mailto:hello@allowancelab.com">hello@allowancelab.com</a></span>
            </div>
        </div>

        <div class="contact-detail">
            <div class="contact-detail-icon"><i class="fas fa-clock"></i></div>
            <div class="contact-detail-text">
                <span class="contact-detail-label">Response Time</span>
                <span class="contact-detail-value">Usually within 1–2 business days</span>
            </div>
        </div>

        <div class="contact-detail">
            <div class="contact-detail-icon"><i class="fas fa-circle-question"></i></div>
            <div class="contact-detail-text">
                <span class="contact-detail-label">Common Questions</span>
                <span class="contact-detail-value"><a href="{{ route('faq') }}">Check the FAQ first →</a></span>
            </div>
        </div>
    </div>

    <!-- Right: form -->
    <div class="contact-form-card">
        <h3>Send us a message</h3>

        @if(session('success'))
            <div class="success-banner">
                <i class="fas fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-banner">
                <i class="fas fa-circle-exclamation"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('contact.submit') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="name">Your Name</label>
                <input class="form-input" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Jane Smith" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="subject">Subject</label>
                <input class="form-input" type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder="What's this about?" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="message">Message</label>
                <textarea class="form-textarea" id="message" name="message" placeholder="Tell us what's on your mind..." required>{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>
</div>
@endsection
