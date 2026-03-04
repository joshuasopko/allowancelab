@extends('layouts.page')

@section('title', 'Privacy Policy - AllowanceLab')

@section('page-hero')
<div class="page-hero">
    <div class="page-hero-label">Legal</div>
    <h1 class="page-hero-title">Privacy Policy</h1>
    <p class="page-hero-subtitle">Last updated: March 2026</p>
</div>
@endsection

@section('content')
<div class="page-content">

    <p>AllowanceLab ("we," "us," or "our") is committed to protecting the privacy of your family. This Privacy Policy explains what information we collect, how we use it, and your rights regarding that information.</p>

    <hr class="content-divider">

    <h2>Information We Collect</h2>

    <h3>Parent Accounts</h3>
    <p>When you create a parent account, we collect:</p>
    <ul>
        <li>First and last name</li>
        <li>Email address</li>
        <li>Password (stored as a one-way encrypted hash — we cannot see it)</li>
        <li>Time zone (auto-detected to schedule allowances correctly)</li>
    </ul>

    <h3>Children's Accounts</h3>
    <p>When a child account is created via parent invite, we collect:</p>
    <ul>
        <li>First name and display name</li>
        <li>Username (chosen by the child or parent)</li>
        <li>Password (hashed; the plain-text version is visible to parents only for account recovery purposes)</li>
        <li>Allowance settings, balance, points, and transaction history</li>
    </ul>

    <h3>Usage Data</h3>
    <p>We may collect basic usage information such as page visits and feature interactions to help us improve the app. We do not use third-party advertising trackers or sell analytics data.</p>

    <hr class="content-divider">

    <h2>Children's Privacy (COPPA)</h2>

    <p>AllowanceLab is designed to be used by parents on behalf of their children. Children's accounts are created by parents and require a parent-provided invite link. We do not knowingly collect personal information directly from children under 13 without verifiable parental consent.</p>

    <p>If you are a parent and believe your child has submitted personal information without your consent, please contact us at <a href="mailto:hello@allowancelab.com">hello@allowancelab.com</a> and we will promptly remove it.</p>

    <hr class="content-divider">

    <h2>How We Use Your Information</h2>

    <p>We use the information we collect to:</p>
    <ul>
        <li>Operate and maintain your family's AllowanceLab account</li>
        <li>Process and schedule automated allowance payments</li>
        <li>Send transactional emails (account creation, invites, allowance confirmations)</li>
        <li>Respond to support requests and questions</li>
        <li>Improve the app's features and user experience</li>
    </ul>

    <p>We do not sell your personal information to third parties. We do not use your data for targeted advertising.</p>

    <hr class="content-divider">

    <h2>Third-Party Services</h2>

    <p>AllowanceLab uses the following third-party services to operate:</p>
    <ul>
        <li><strong>Resend</strong> — Transactional email delivery (invite links, welcome emails)</li>
        <li><strong>Railway</strong> — Application hosting and database infrastructure</li>
    </ul>

    <p>These services receive only the minimum information necessary to perform their function and are bound by their own privacy policies.</p>

    <hr class="content-divider">

    <h2>Data Retention and Deletion</h2>

    <p>We retain your account data for as long as your account is active. You may delete your parent account at any time from the Account Settings page. Deleting your account will permanently remove your family data, including all children's accounts and transaction history, from our systems.</p>

    <p>To request manual deletion or data export, contact us at <a href="mailto:hello@allowancelab.com">hello@allowancelab.com</a>.</p>

    <hr class="content-divider">

    <h2>Security</h2>

    <p>We take reasonable technical and organizational measures to protect your information, including HTTPS encryption for all data in transit and hashed password storage. No system is 100% secure, and we encourage you to use a strong, unique password for your account.</p>

    <hr class="content-divider">

    <h2>Changes to This Policy</h2>

    <p>We may update this Privacy Policy from time to time. When we do, we will update the "Last updated" date at the top of this page. Continued use of AllowanceLab after changes are posted constitutes your acceptance of the updated policy.</p>

    <hr class="content-divider">

    <h2>Contact Us</h2>

    <p>If you have questions or concerns about this Privacy Policy or how we handle your data, please contact us:</p>
    <ul>
        <li>Email: <a href="mailto:hello@allowancelab.com">hello@allowancelab.com</a></li>
        <li>Contact form: <a href="{{ route('contact') }}">allowancelab.com/contact</a></li>
    </ul>

</div>
@endsection
