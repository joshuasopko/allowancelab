@extends('layouts.page')

@section('title', 'Terms of Service - AllowanceLab')

@section('page-hero')
<div class="page-hero">
    <div class="page-hero-label">Legal</div>
    <h1 class="page-hero-title">Terms of Service</h1>
    <p class="page-hero-subtitle">Last updated: March 2026</p>
</div>
@endsection

@section('content')
<div class="page-content">

    <p>Please read these Terms of Service ("Terms") carefully before using AllowanceLab. By creating an account or using the service, you agree to be bound by these Terms.</p>

    <hr class="content-divider">

    <h2>1. Acceptance of Terms</h2>

    <p>By accessing or using AllowanceLab ("the Service"), you confirm that you are at least 18 years of age, have the authority to enter into this agreement, and agree to comply with these Terms and all applicable laws and regulations.</p>

    <hr class="content-divider">

    <h2>2. Description of Service</h2>

    <p>AllowanceLab is a family allowance management platform that helps parents track children's allowances, savings goals, and points-based accountability systems. The Service is a digital ledger — it tracks balances and transactions but does not process or move real money.</p>

    <p>AllowanceLab is provided free of charge. We reserve the right to introduce optional paid features in the future, but core family management features will remain free.</p>

    <hr class="content-divider">

    <h2>3. Account Responsibilities</h2>

    <p>You are responsible for:</p>
    <ul>
        <li>Maintaining the confidentiality of your account credentials</li>
        <li>All activity that occurs under your account</li>
        <li>Ensuring the accuracy of information you provide</li>
        <li>Notifying us promptly of any unauthorized use of your account</li>
    </ul>

    <p>Parent accounts must be created by adults (18+). You may create child accounts for minors in your care using the invite system.</p>

    <hr class="content-divider">

    <h2>4. Children's Accounts</h2>

    <p>Children's accounts are created and managed by parent/guardian account holders. By creating a child account, you represent that you are the parent or legal guardian of that child and consent to their use of the Service under your supervision.</p>

    <p>Children should use the Service only with parental guidance and under the terms of the parent's account. AllowanceLab is not intended for unsupervised use by children under 13.</p>

    <hr class="content-divider">

    <h2>5. Acceptable Use</h2>

    <p>You agree not to:</p>
    <ul>
        <li>Use the Service for any unlawful purpose</li>
        <li>Attempt to gain unauthorized access to any part of the Service</li>
        <li>Interfere with or disrupt the integrity or performance of the Service</li>
        <li>Upload or transmit malicious code</li>
        <li>Impersonate any person or entity</li>
        <li>Use the Service to harass, abuse, or harm another person</li>
    </ul>

    <hr class="content-divider">

    <h2>6. No Real Financial Transactions</h2>

    <p>AllowanceLab does not process real money transactions. The balances displayed in the app are for tracking and educational purposes only. Any actual transfer of money between parents and children occurs outside of the platform and is solely the responsibility of the users involved.</p>

    <hr class="content-divider">

    <h2>7. Intellectual Property</h2>

    <p>AllowanceLab and its original content, features, and functionality are owned by Joshua Sopko and are protected by applicable intellectual property laws. You may not copy, modify, distribute, or reverse-engineer any part of the Service without express written permission.</p>

    <hr class="content-divider">

    <h2>8. Disclaimer of Warranties</h2>

    <p>The Service is provided "as is" and "as available" without warranties of any kind, either express or implied. We do not warrant that the Service will be uninterrupted, error-free, or free from harmful components. Use of the Service is at your own risk.</p>

    <hr class="content-divider">

    <h2>9. Limitation of Liability</h2>

    <p>To the fullest extent permitted by law, AllowanceLab and its creator shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or related to your use of the Service, even if advised of the possibility of such damages.</p>

    <hr class="content-divider">

    <h2>10. Termination</h2>

    <p>We reserve the right to suspend or terminate your account at any time for violation of these Terms or for any other reason at our discretion. You may delete your account at any time from Account Settings. Upon termination, your data will be permanently deleted from our systems.</p>

    <hr class="content-divider">

    <h2>11. Changes to Terms</h2>

    <p>We may update these Terms from time to time. When we do, we will update the "Last updated" date at the top of this page. Continued use of the Service after changes are posted constitutes acceptance of the revised Terms. We encourage you to review these Terms periodically.</p>

    <hr class="content-divider">

    <h2>12. Governing Law</h2>

    <p>These Terms shall be governed by and construed in accordance with the laws of the State of Illinois, without regard to its conflict of law provisions.</p>

    <hr class="content-divider">

    <h2>13. Contact</h2>

    <p>If you have questions about these Terms, please contact us:</p>
    <ul>
        <li>Email: <a href="mailto:hello@allowancelab.com">hello@allowancelab.com</a></li>
        <li>Contact form: <a href="{{ route('contact') }}">allowancelab.com/contact</a></li>
    </ul>

</div>
@endsection
