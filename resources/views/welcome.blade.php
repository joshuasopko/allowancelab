<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4CAF50">
    <title>AllowanceLab - Earn. Learn. Grow.</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" href="/images/Allowance-Lab-logo.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AllowanceLab">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 60px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-container img {
            height: 70px;
            width: auto;
        }

        .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            padding: 12px 28px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav-links a.parent-login {
            background: #4CAF50;
        }

        .nav-links a.parent-login:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .nav-links a.kid-login {
            background: #42a5f5;
        }

        .nav-links a.kid-login:hover {
            background: #1e88e5;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #e8f5e9 0%, #f3e5f5 100%);
            padding: 100px 60px;
            text-align: center;
        }

        .hero-title {
            font-size: 72px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 24px;
            line-height: 1.1;
        }

        .hero-subtitle {
            font-size: 28px;
            color: #444;
            margin-bottom: 16px;
            font-weight: 400;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-cta-line {
            font-size: 20px;
            color: #666;
            margin-bottom: 48px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 18px 48px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #4CAF50;
            border: 2px solid #4CAF50;
            padding: 16px 48px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: #4CAF50;
            color: white;
            transform: translateY(-2px);
        }

        /* Section Container */
        .section {
            padding: 80px 60px;
        }

        .section-white {
            background: white;
        }

        .section-light {
            background: #f8f9fa;
        }

        .section-title {
            font-size: 48px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            color: #1a1a1a;
        }

        .section-subtitle {
            font-size: 22px;
            text-align: center;
            color: #666;
            margin-bottom: 60px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        /* How It Works */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .step-card {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .section-light .step-card {
            background: white;
        }

        .section-white .step-card {
            background: #f8f9fa;
        }

        .step-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            margin: 0 auto 20px;
        }

        .step-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .step-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1a1a1a;
        }

        .step-description {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
        }

        .section-white .feature-card {
            background: #f8f9fa;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 40px;
            margin-bottom: 16px;
        }

        .feature-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .feature-description {
            font-size: 15px;
            color: #666;
            line-height: 1.6;
        }

        /* Why It Works */
        .why-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
        }

        .benefits-list li {
            font-size: 20px;
            padding: 16px 0;
            padding-left: 40px;
            position: relative;
            color: #444;
        }

        .benefits-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: 700;
            font-size: 24px;
        }

        .why-tagline {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            color: #4CAF50;
            margin-top: 40px;
        }

        /* Two Column Section */
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .column-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .section-white .column-card {
            background: #f8f9fa;
        }

        .column-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .column-title .icon {
            font-size: 36px;
        }

        .column-list {
            list-style: none;
            padding: 0;
        }

        .column-list li {
            font-size: 17px;
            padding: 12px 0;
            padding-left: 32px;
            position: relative;
            color: #555;
        }

        .column-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: 700;
            font-size: 24px;
        }

        /* Testimonials */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .section-white .testimonial-card {
            background: #f8f9fa;
        }

        .testimonial-quote {
            font-size: 18px;
            color: #444;
            line-height: 1.7;
            margin-bottom: 20px;
            font-style: italic;
        }

        .testimonial-author {
            font-size: 16px;
            color: #4CAF50;
            font-weight: 600;
        }

        .quote-icon {
            font-size: 48px;
            color: #4CAF50;
            opacity: 0.2;
            position: absolute;
            top: 20px;
            right: 30px;
        }

        /* Footer */
        footer {
            background: #1a1a1a;
            color: white;
            padding: 60px 60px 30px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 40px;
        }

        .footer-brand {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .footer-tagline {
            color: #999;
            font-size: 16px;
            line-height: 1.6;
        }

        .footer-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #999;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #4CAF50;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #333;
            color: #666;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 16px 20px;
            }

            .logo-container img {
                height: 80px;
            }

            .nav-links {
                gap: 10px;
            }

            .nav-links a {
                font-size: 14px;
                padding: 10px 20px;
            }

            .hero {
                padding: 60px 20px;
            }

            .hero-title {
                font-size: 42px;
            }

            .hero-subtitle {
                font-size: 20px;
            }

            .hero-cta-line {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                padding: 16px 32px;
                font-size: 18px;
            }

            .section {
                padding: 50px 20px;
            }

            .section-title {
                font-size: 32px;
            }

            .section-subtitle {
                font-size: 18px;
            }

            .steps-grid,
            .features-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .two-column {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            footer {
                padding: 40px 20px 20px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        /* Hide desktop buttons on mobile */
        .desktop-only {
            display: flex;
        }

        @media (max-width: 550px) {
            .desktop-only {
                display: none;
            }
        }

        /* Mobile login buttons below header */
        .mobile-login-buttons {
            display: none;
            gap: 12px;
            padding: 16px 20px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
        }

        .mobile-login-btn {
            flex: 1;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .mobile-login-btn.btn-parent {
            background: #10b981;
            color: white;
        }

        .mobile-login-btn.btn-kid {
            background: #3b82f6;
            color: white;
        }

        @media (max-width: 550px) {
            .mobile-login-buttons {
                display: flex;
            }

            nav.nav-links {
                display: none;
            }
        }

        /* PWA Install Button */
        .pwa-install-button {
            margin-top: 24px;
            background: white;
            color: #4CAF50;
            border: 2px solid #4CAF50;
            padding: 14px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: none;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
        }

        .pwa-install-button:hover {
            background: #4CAF50;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .pwa-install-icon {
            font-size: 20px;
        }

        /* Only show PWA button on mobile devices */
        @media (max-width: 768px) {
            .pwa-install-button {
                display: inline-flex;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="logo-container">
            <img src="{{ asset('/images/Allowance-Lab-logo.png') }}" alt="AllowanceLab">
        </div>
        <nav class="nav-links">
            <a href="{{ route('login') }}" class="parent-login">Parent Login</a>
            <a href="{{ route('kid.login') }}" class="kid-login">Kid Login</a>
        </nav>
    </header>

    <!-- Mobile Login Buttons (below header) -->
    <div class="mobile-login-buttons">
        <a href="{{ route('login') }}" class="mobile-login-btn btn-parent">Parent Login</a>
        <a href="{{ route('kid.login') }}" class="mobile-login-btn btn-kid">Kid Login</a>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <h1 class="hero-title">Earn. Learn. Grow.</h1>
        <p class="hero-subtitle">AllowanceLab helps families build real financial habits by tracking chores, allowances,
            savings goals, loans, and rewards all in one place.</p>
        <p class="hero-cta-line">Simple tools for real-world money lessons. Start your family's dashboard today.</p>
        <div class="hero-buttons">
            <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
            <a href="#how-it-works" class="btn-secondary">See How It Works</a>
        </div>

        <!-- PWA Install Button (Mobile Only) -->
        <button id="pwa-install-btn" class="pwa-install-button" style="display: none;">
            <span class="pwa-install-icon">📱</span>
            Add to Home Screen
        </button>
    </section>

    <!-- How It Works -->
    <section class="section section-light" id="how-it-works">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Built for real families, real routines, and real growth.</p>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon">👨‍👩‍👧‍👦</div>
                <h3 class="step-title">Create Your Family Dashboard</h3>
                <p class="step-description">Add your kids, set their weekly allowances, define expectations, and
                    customize settings for each child.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon">✅</div>
                <h3 class="step-title">Assign Chores and Jobs</h3>
                <p class="step-description">Track daily chores, create specialty paid jobs, and keep everything
                    organized with an easy parent view.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon">💰</div>
                <h3 class="step-title">Build Smart Money Habits</h3>
                <p class="step-description">Kids can set savings goals, request loans, track progress, earn rewards, and
                    learn responsibility by managing their own balances.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-icon">🌟</div>
                <h3 class="step-title">Watch Their Confidence Grow</h3>
                <p class="step-description">Parents get transparency. Kids get motivation. Everyone gets a simple system
                    that works.</p>
            </div>
        </div>
    </section>

    <!-- Core Features -->
    <section class="section section-white">
        <h2 class="section-title">Core Features</h2>
        <p class="section-subtitle">Everything you need to raise money-smart kids.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3 class="feature-title">Chore Tracking</h3>
                <p class="feature-description">Assign daily, weekly, or one-time tasks. Kids mark them complete, parents
                    approve with a tap.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💵</div>
                <h3 class="feature-title">Automated Allowance Management</h3>
                <p class="feature-description">No more forgetting payday. Allowance can run automatically or be tied to
                    chores.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3 class="feature-title">Savings Goals</h3>
                <p class="feature-description">Kids choose something they want, set a target, and see their progress in
                    real time.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏦</div>
                <h3 class="feature-title">Loans (Parent Bank)</h3>
                <p class="feature-description">Teach borrowing and repayment responsibly. Parents control limits and
                    terms.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3 class="feature-title">Specialty Jobs</h3>
                <p class="feature-description">Create extra earning opportunities for bigger chores and
                    responsibilities.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎁</div>
                <h3 class="feature-title">Rewards & Bonuses</h3>
                <p class="feature-description">Motivate good behavior and extra effort with customizable rewards.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👶</div>
                <h3 class="feature-title">Multiple Kids, One Clean Dashboard</h3>
                <p class="feature-description">Each child has their own login and PIN with secure kid-friendly screens.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Fully Parent-Controlled</h3>
                <p class="feature-description">Parents approve everything. Kids learn within guardrails.</p>
            </div>
        </div>
    </section>

    <!-- Why It Works -->
    <section class="section section-light">
        <h2 class="section-title">Why It Works</h2>
        <p class="section-subtitle">Because money skills are life skills.</p>
        <div class="why-content">
            <p style="text-align: center; font-size: 20px; color: #444; margin-bottom: 40px;">AllowanceLab makes
                financial responsibility tangible. Kids don't just hear lessons. They experience them.</p>
            <ul class="benefits-list">
                <li>Learn the value of work</li>
                <li>Understand saving vs spending</li>
                <li>Practice delayed gratification</li>
                <li>Build early confidence managing money</li>
                <li>Develop habits that last into adulthood</li>
            </ul>
            <p class="why-tagline">This isn't just allowance tracking. It's life training.</p>
        </div>
    </section>

    <!-- Designed for Kids and Parents -->
    <section class="section section-white">
        <h2 class="section-title">Designed for Kids and Parents</h2>
        <div class="two-column">
            <div class="column-card">
                <h3 class="column-title"><span class="icon">👨‍👩‍👧</span> For Parents</h3>
                <ul class="column-list">
                    <li>Clear reporting</li>
                    <li>Easy approvals</li>
                    <li>Customization for each child</li>
                    <li>Weekly or monthly summaries</li>
                    <li>Private notes and reminders</li>
                </ul>
            </div>
            <div class="column-card">
                <h3 class="column-title"><span class="icon">👧</span> For Kids</h3>
                <ul class="column-list">
                    <li>Simple buttons and big visuals</li>
                    <li>Progress bars that motivate</li>
                    <li>Savings goals that feel achievable</li>
                    <li>Fun, game-like earning experience</li>
                    <li>Their own login and dashboard</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section section-light">
        <h2 class="section-title">Families Love AllowanceLab</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="quote-icon">"</div>
                <p class="testimonial-quote">"AllowanceLab is the first system our kids actually stick with. They beg to
                    check their goals."</p>
                <p class="testimonial-author">— Sarah M., Mom of 3</p>
            </div>
            <div class="testimonial-card">
                <div class="quote-icon">"</div>
                <p class="testimonial-quote">"Finally, a simple way to manage allowance without forgetting payouts."</p>
                <p class="testimonial-author">— Mike T., Dad of 2</p>
            </div>
            <div class="testimonial-card">
                <div class="quote-icon">"</div>
                <p class="testimonial-quote">"Our kids understand saving better after two months with this app than we
                    ever expected."</p>
                <p class="testimonial-author">— Jessica R., Mom of 4</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div>
                <div class="footer-brand">AllowanceLab</div>
                <p class="footer-tagline">Simple tools for real-world money lessons. Teaching kids financial
                    responsibility one allowance at a time.</p>
            </div>
            <div>
                <h4 class="footer-section-title">Company</h4>
                <ul class="footer-links">
                    <li><a href="#about">About</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-section-title">Legal</h4>
                <ul class="footer-links">
                    <li><a href="#privacy">Privacy</a></li>
                    <li><a href="#terms">Terms</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            © 2025 AllowanceLab. All rights reserved.
        </div>
    </footer>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // PWA Service Worker Registration and Install Prompt
        let deferredPrompt;
        const installButton = document.getElementById('pwa-install-btn');

        // Register service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('Service Worker registered:', registration);
                    })
                    .catch(error => {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }

        // Capture the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the default mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show the install button
            if (installButton) {
                installButton.style.display = 'inline-flex';
            }
        });

        // Handle install button click
        if (installButton) {
            installButton.addEventListener('click', async () => {
                if (!deferredPrompt) {
                    return;
                }
                // Show the install prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                // Clear the deferredPrompt
                deferredPrompt = null;
                // Hide the install button
                installButton.style.display = 'none';
            });
        }

        // Hide install button if app is already installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            deferredPrompt = null;
            if (installButton) {
                installButton.style.display = 'none';
            }
        });
    </script>
</body>

</html>