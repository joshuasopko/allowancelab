<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <title>AllowanceLab - Earn. Learn. Grow.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

        /* ===== HEADER ===== */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 60px;
            background: transparent;
            box-shadow: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: background 0.35s ease, box-shadow 0.35s ease;
        }

        header.scrolled {
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        }

        .logo-container img {
            height: 62px;
            width: auto;
            transition: opacity 0.2s ease;
        }

        .nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            padding: 10px 24px;
            border-radius: 8px;
            transition: all 0.25s ease;
        }

        /* Transparent state — glowing buttons on dark */
        .nav-links a.parent-login {
            background: #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.55), 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .nav-links a.parent-login:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 0 28px rgba(16, 185, 129, 0.7), 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .nav-links a.kid-login {
            background: #3b82f6;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.55), 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .nav-links a.kid-login:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 0 28px rgba(59, 130, 246, 0.7), 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        /* Scrolled state — clean solid buttons, no glow */
        header.scrolled .nav-links a.parent-login {
            box-shadow: none;
        }

        header.scrolled .nav-links a.parent-login:hover {
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        header.scrolled .nav-links a.kid-login {
            box-shadow: none;
        }

        header.scrolled .nav-links a.kid-login:hover {
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }

        /* ===== HAMBURGER ===== */
        .hamburger {
            display: none;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .hamburger:hover {
            background: rgba(255,255,255,0.1);
        }

        header.scrolled .hamburger:hover {
            background: rgba(0,0,0,0.06);
        }

        /* Staggered line widths */
        .hamburger span {
            display: block;
            height: 2px;
            background: white;
            border-radius: 2px;
            transition: width 0.25s ease, opacity 0.25s ease, transform 0.25s ease, background 0.35s ease;
        }

        .hamburger span:nth-child(1) { width: 22px; }
        .hamburger span:nth-child(2) { width: 15px; }
        .hamburger span:nth-child(3) { width: 9px; }

        header.scrolled .hamburger span {
            background: #0f172a;
        }

        /* X state when open */
        .hamburger.open span:nth-child(1) {
            width: 22px;
            transform: translateY(7px) rotate(45deg);
        }

        .hamburger.open span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }

        .hamburger.open span:nth-child(3) {
            width: 22px;
            transform: translateY(-7px) rotate(-45deg);
        }

        /* ===== MOBILE MENU DRAWER ===== */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 76px;
            left: 0;
            right: 0;
            z-index: 999;
            background: linear-gradient(to bottom, #0f172a, #022c22);
            padding: 20px 20px 24px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.35);
            flex-direction: column;
            gap: 18px;
            border-top: 1px solid rgba(16, 185, 129, 0.25);
            transform: translateY(-6px);
            opacity: 0;
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .mobile-menu.open {
            opacity: 1;
            transform: translateY(0);
        }

        .mobile-menu.scrolled {
            background: white;
            border-top: 1px solid #f1f5f9;
            box-shadow: 0 8px 24px rgba(0,0,0,0.10);
        }

        .mobile-menu-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            color: white;
            transition: all 0.2s;
        }

        .mobile-menu-btn.btn-parent {
            background: #10b981;
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.45);
        }

        .mobile-menu-btn.btn-parent:hover {
            background: #059669;
        }

        .mobile-menu-btn.btn-kid {
            background: #3b82f6;
            box-shadow: 0 0 18px rgba(59, 130, 246, 0.45);
        }

        .mobile-menu-btn.btn-kid:hover {
            background: #2563eb;
        }

        .mobile-menu.scrolled .mobile-menu-btn.btn-parent,
        .mobile-menu.scrolled .mobile-menu-btn.btn-kid {
            box-shadow: none;
        }

        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #022c22 55%, #064e3b 100%);
            padding: 160px 60px 110px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -40%;
            left: -15%;
            width: 55%;
            height: 200%;
            background: radial-gradient(ellipse, rgba(16, 185, 129, 0.14) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            right: -15%;
            width: 55%;
            height: 200%;
            background: radial-gradient(ellipse, rgba(110, 231, 183, 0.09) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.18);
            border: 1px solid rgba(16, 185, 129, 0.38);
            color: #a7f3d0;
            padding: 6px 18px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.03em;
            margin-bottom: 28px;
        }

        .hero-title {
            font-size: 72px;
            font-weight: 800;
            color: #f8fafc;
            margin-bottom: 24px;
            line-height: 1.05;
            letter-spacing: -0.025em;
        }

        .hero-title .highlight {
            background: linear-gradient(135deg, #a7f3d0, #a7f3d0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 22px;
            color: #cbd5e1;
            margin-bottom: 12px;
            font-weight: 400;
            max-width: 680px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-cta-line {
            font-size: 16px;
            color: #94a3b8;
            margin-bottom: 44px;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: #10b981;
            color: white;
            border: none;
            padding: 16px 44px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(16, 185, 129, 0.5);
        }

        .btn-secondary {
            background: transparent;
            color: #a7f3d0;
            border: 2px solid rgba(16, 185, 129, 0.45);
            padding: 14px 44px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background: rgba(16, 185, 129, 0.14);
            border-color: #10b981;
            color: #d1fae5;
            transform: translateY(-2px);
        }

        /* ===== PWA INSTALL TOAST ===== */
        .pwa-toast {
            position: fixed;
            bottom: 20px;
            left: 16px;
            right: 16px;
            max-width: 480px;
            margin: 0 auto;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(16, 185, 129, 0.28);
            border-radius: 18px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.35), 0 2px 8px rgba(0,0,0,0.2);
            transform: translateY(120px);
            opacity: 0;
            transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s ease;
            pointer-events: none;
        }

        .pwa-toast.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .pwa-toast-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .pwa-toast-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .pwa-toast-text {
            font-size: 13px;
            color: #e2e8f0;
            font-weight: 500;
            line-height: 1.35;
        }

        .pwa-toast-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            width: fit-content;
            font-family: inherit;
        }

        .pwa-toast-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .pwa-toast-close {
            background: none;
            border: none;
            color: #475569;
            font-size: 15px;
            cursor: pointer;
            padding: 4px 6px;
            flex-shrink: 0;
            transition: color 0.2s;
            line-height: 1;
            border-radius: 6px;
        }

        .pwa-toast-close:hover {
            color: #94a3b8;
        }

        /* Desktop: hide the toast entirely */
        @media (min-width: 900px) {
            .pwa-toast {
                display: none !important;
            }
        }

        /* ===== VALUE PROPS BAR ===== */
        .stat-bar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 60px;
        }

        .stat-bar-inner {
            max-width: 860px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            align-items: center;
        }

        .stat-item {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .stat-item + .stat-item {
            border-left: 1px solid #e5e7eb;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-text-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .stat-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 400;
        }

        /* ===== SECTION COMMONS ===== */
        .section {
            padding: 84px 60px;
        }

        .section-white {
            background: white;
        }

        .section-light {
            background: #f0fdf4;
        }

        .section-dark {
            background: #0f172a;
        }

        .section-label {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #10b981;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 42px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 14px;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .section-title-dark {
            color: #f8fafc;
        }

        .section-subtitle {
            font-size: 19px;
            text-align: center;
            color: #64748b;
            margin-bottom: 56px;
            max-width: 660px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-subtitle-dark {
            color: #94a3b8;
        }

        /* ===== HOW IT WORKS ===== */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .step-card {
            text-align: center;
            padding: 36px 24px 32px;
            background: white;
            border: 1px solid #d1fae5;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.06);
            transition: all 0.3s;
            position: relative;
        }

        .step-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(16, 185, 129, 0.14);
            border-color: #d1fae5;
        }

        .step-number-badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            height: 28px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        }

        .step-icon-box {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin: 0 auto 20px;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        .step-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #0f172a;
        }

        .step-description {
            font-size: 14px;
            color: #64748b;
            line-height: 1.65;
        }

        /* ===== FEATURES GRID ===== */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .feature-card {
            padding: 26px 24px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            transition: all 0.3s;
            display: flex;
            gap: 18px;
            align-items: flex-start;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(16, 185, 129, 0.10);
            border-color: #d1fae5;
        }

        .feature-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .fi-indigo  { background: linear-gradient(135deg, #10b981, #059669); }
        .fi-violet  { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .fi-blue    { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .fi-emerald { background: linear-gradient(135deg, #10b981, #059669); }
        .fi-amber   { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .fi-rose    { background: linear-gradient(135deg, #f43f5e, #e11d48); }
        .fi-teal    { background: linear-gradient(135deg, #0891b2, #0e7490); }
        .fi-orange  { background: linear-gradient(135deg, #f97316, #ea580c); }
        .fi-sky     { background: linear-gradient(135deg, #6366f1, #4f46e5); }

        /* ===== COMING SOON SECTION ===== */
        .coming-soon-section {
            background: linear-gradient(135deg, #0f172a 0%, #022c22 55%, #064e3b 100%);
            padding: 84px 60px;
            position: relative;
            overflow: hidden;
        }

        .coming-soon-section::before {
            content: '';
            position: absolute;
            top: -120px;
            left: -120px;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .coming-soon-section::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.10) 0%, transparent 70%);
            pointer-events: none;
        }

        .coming-soon-section .section-label {
            color: #6ee7b7;
        }

        .coming-soon-section .section-title {
            color: #ffffff;
        }

        .coming-soon-section .section-subtitle {
            color: #94a3b8;
        }

        .coming-soon-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .cs-card {
            padding: 28px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: all 0.3s;
            position: relative;
        }

        .cs-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(16, 185, 129, 0.3);
            transform: translateY(-3px);
        }

        .cs-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cs-badge {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #6ee7b7;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 100px;
            padding: 4px 10px;
            white-space: nowrap;
        }

        .cs-title {
            font-size: 17px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 6px;
        }

        .cs-desc {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.65;
        }

        .feature-text h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .feature-text p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.65;
        }

        /* ===== WHY IT WORKS ===== */
        .why-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .why-lead {
            font-size: 19px;
            color: #475569;
            margin-bottom: 40px;
            line-height: 1.75;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            text-align: left;
            margin-bottom: 40px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: white;
            border: 1px solid #d1fae5;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .benefit-item:hover {
            border-color: #d1fae5;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
        }

        .benefit-check {
            width: 28px;
            height: 28px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #10b981;
            font-size: 12px;
            flex-shrink: 0;
        }

        .benefit-text {
            font-size: 15px;
            color: #334155;
            font-weight: 500;
        }

        .why-tagline {
            font-size: 22px;
            font-weight: 700;
            color: #10b981;
        }

        /* ===== TWO COLUMN ===== */
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .column-card {
            background: white;
            padding: 36px;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .column-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .column-header-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
        }

        .column-header-icon.indigo {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .column-header-icon.blue {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .column-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }

        .column-list {
            list-style: none;
            padding: 0;
        }

        .column-list li {
            font-size: 15px;
            padding: 11px 0;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .column-list li:last-child {
            border-bottom: none;
        }

        .column-list li i {
            color: #10b981;
            width: 14px;
            flex-shrink: 0;
            font-size: 13px;
        }

        /* ===== TESTIMONIALS ===== */
        .founder-card {
            max-width: 780px;
            margin: 0 auto;
            background: #1e293b;
            border-radius: 20px;
            border: 1px solid #334155;
            padding: 52px 56px;
            position: relative;
        }

        .founder-quote-mark {
            font-size: 120px;
            line-height: 1;
            color: #10b981;
            opacity: 0.18;
            position: absolute;
            top: 12px;
            left: 36px;
            font-family: Georgia, serif;
            pointer-events: none;
        }

        .founder-message {
            font-size: 18px;
            color: #cbd5e1;
            line-height: 1.85;
            font-style: italic;
            margin-bottom: 36px;
            position: relative;
            z-index: 1;
        }

        .founder-footer {
            display: flex;
            align-items: center;
            gap: 16px;
            border-top: 1px solid #334155;
            padding-top: 28px;
        }

        .founder-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
            letter-spacing: -0.5px;
        }

        .founder-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .founder-name {
            font-size: 16px;
            color: #6ee7b7;
            font-weight: 700;
        }

        .founder-role {
            font-size: 13px;
            color: #64748b;
        }

        /* ===== CTA SECTION ===== */
        .cta-section {
            background: linear-gradient(135deg, #047857 0%, #10b981 50%, #059669 100%);
            padding: 84px 60px;
            text-align: center;
        }

        .cta-title {
            font-size: 44px;
            font-weight: 800;
            color: white;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .cta-subtitle {
            font-size: 19px;
            color: #d1fae5;
            margin-bottom: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-cta-white {
            background: white;
            color: #059669;
            border: none;
            padding: 16px 48px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-cta-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.28);
        }

        /* ===== FOOTER ===== */
        footer {
            background: #0a0f1e;
            color: white;
            padding: 60px 60px 32px;
            border-top: 1px solid #1e293b;
        }

        .footer-content {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 40px;
        }

        .footer-brand {
            font-size: 20px;
            font-weight: 800;
            color: #f8fafc;
            margin-bottom: 12px;
        }

        .footer-tagline {
            color: #475569;
            font-size: 14px;
            line-height: 1.75;
            max-width: 280px;
        }

        .footer-section-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 16px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #6ee7b7;
        }

        .footer-bottom {
            max-width: 1100px;
            margin: 0 auto;
            text-align: center;
            padding-top: 28px;
            border-top: 1px solid #1e293b;
            color: #334155;
            font-size: 13px;
        }

        /* ===== PWA HOME SCREEN ===== */
        #pwa-home {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #022c22 55%, #064e3b 100%);
            padding: 40px 20px;
        }

        .pwa-home-container {
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .pwa-logo {
            margin-bottom: 32px;
        }

        .pwa-logo img {
            width: 80%;
            max-width: 260px;
            height: auto;
        }

        .pwa-welcome {
            font-size: 26px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 10px;
        }

        .pwa-subtitle {
            font-size: 16px;
            color: #94a3b8;
            margin-bottom: 44px;
        }

        .pwa-buttons {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .pwa-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 22px 32px;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        }

        .pwa-btn:active {
            transform: scale(0.98);
        }

        .pwa-btn-kid {
            background: #3b82f6;
            color: white;
        }

        .pwa-btn-kid:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .pwa-btn-parent {
            background: #10b981;
            color: white;
        }

        .pwa-btn-parent:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        /* ===== DESKTOP-ONLY UTILITY ===== */
        .desktop-only {
            display: flex;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 900px) {
            header {
                padding: 12px 20px;
            }

            .logo-container img {
                height: 52px;
            }

            .nav-links {
                gap: 8px;
            }

            .nav-links a {
                font-size: 13px;
                padding: 8px 16px;
            }

            .hero {
                padding: 120px 20px 72px;
            }

            .hero-title {
                font-size: 46px;
            }

            .hero-subtitle {
                font-size: 18px;
            }

            .hero-cta-line {
                font-size: 15px;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                max-width: 340px;
                justify-content: center;
                padding: 14px 32px;
                font-size: 16px;
            }

            .stat-bar {
                padding: 20px 20px;
            }

            .stat-number {
                font-size: 26px;
            }

            .section {
                padding: 56px 20px;
            }

            .section-title {
                font-size: 30px;
            }

            .section-subtitle {
                font-size: 17px;
                margin-bottom: 40px;
            }

            .two-column {
                grid-template-columns: 1fr;
            }

            .founder-card {
                padding: 36px 28px;
            }

            .cta-section {
                padding: 64px 20px;
            }

            .coming-soon-section {
                padding: 56px 20px;
            }

            .coming-soon-grid {
                grid-template-columns: 1fr;
            }

            .cta-title {
                font-size: 32px;
            }

            .cta-subtitle {
                font-size: 17px;
            }

            footer {
                padding: 44px 20px 24px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 28px;
            }
        }

        @media (max-width: 700px) {
            .steps-grid {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .stat-bar {
                padding: 20px 16px;
            }

            .stat-bar-inner {
                gap: 0;
            }

            .stat-item {
                flex-direction: column;
                gap: 6px;
                padding: 0 8px;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 15px;
                border-radius: 10px;
            }

            .stat-title {
                font-size: 13px;
            }

            .stat-label {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .desktop-only {
                display: none;
            }

            nav.nav-links {
                display: none;
            }

            .hamburger {
                display: flex;
            }

            .mobile-menu {
                display: flex;
            }

            header {
                padding: 14px 20px;
            }

            .logo-container img {
                height: 58px;
            }
        }
    </style>
</head>

<body>
    <!-- PWA Home Screen (shown only in standalone mode) -->
    <div id="pwa-home" style="display: none;">
        <div class="pwa-home-container">
            <div class="pwa-logo">
                <img src="{{ asset('/images/Allowance-Lab-logo.png') }}" alt="AllowanceLab">
            </div>
            <h1 class="pwa-welcome">Welcome! Let's get started.</h1>
            <p class="pwa-subtitle">Choose your login</p>
            <div class="pwa-buttons">
                <a href="{{ route('kid.login') }}?pwa=1" class="pwa-btn pwa-btn-kid">
                    <i class="fas fa-star"></i>
                    Kid Login
                </a>
                <a href="{{ route('login') }}?pwa=1" class="pwa-btn pwa-btn-parent">
                    <i class="fas fa-user-shield"></i>
                    Parent Login
                </a>
            </div>
        </div>
    </div>

    <!-- Marketing Website (hidden in standalone mode) -->
    <div id="website-content">

        <!-- Header -->
        <header id="site-header">
            <div class="logo-container">
                <img id="header-logo" src="{{ asset('/images/Allowance-Lab-logo-white.png') }}" alt="AllowanceLab">
            </div>
            <!-- Desktop nav -->
            <nav class="nav-links">
                <a href="{{ route('login') }}" class="parent-login">
                    <i class="fas fa-user-shield"></i> Parent Login
                </a>
                <a href="{{ route('kid.login') }}" class="kid-login">
                    <i class="fas fa-star"></i> Kid Login
                </a>
            </nav>
            <!-- Mobile hamburger -->
            <button class="hamburger" id="hamburger-btn" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
        </header>

        <!-- Mobile menu drawer -->
        <div class="mobile-menu" id="mobile-menu">
            <a href="{{ route('login') }}" class="mobile-menu-btn btn-parent">
                <i class="fas fa-user-shield"></i> Parent Login
            </a>
            <a href="{{ route('kid.login') }}" class="mobile-menu-btn btn-kid">
                <i class="fas fa-star"></i> Kid Login
            </a>
        </div>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-heart" style="font-size:10px;"></i>
                    Free for Families
                </div>
                <h1 class="hero-title">
                    Earn. Learn.<br>
                    <span class="highlight">Grow.</span>
                </h1>
                <p class="hero-subtitle">AllowanceLab helps families build real financial habits — managing allowances, savings goals, and accountability all in one place.</p>
                <p class="hero-cta-line">Simple tools for real-world money lessons. Start your family today.</p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn-primary">
                        <i class="fas fa-rocket"></i>
                        Get Started Free
                    </a>
                    <a href="#how-it-works" class="btn-secondary">
                        <i class="fas fa-circle-question"></i>
                        Learn How It Works
                    </a>
                </div>

            </div>
        </section>

        <!-- Value Props Bar -->
        <div class="stat-bar">
            <div class="stat-bar-inner">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-text-group">
                        <div class="stat-title">Automated Allowances</div>
                        <div class="stat-label">Set it once, pays every week</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-bullseye"></i></div>
                    <div class="stat-text-group">
                        <div class="stat-title">Savings Goals</div>
                        <div class="stat-label">Kids save toward what they love</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-lock-open"></i></div>
                    <div class="stat-text-group">
                        <div class="stat-title">Free Forever</div>
                        <div class="stat-label">No plans, no paywalls, no tricks</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <section class="section section-light" id="how-it-works">
            <div class="section-label">How It Works</div>
            <h2 class="section-title">Set up in minutes. Runs on autopilot.</h2>
            <p class="section-subtitle">Built for real families, real routines, and real growth.</p>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number-badge">1</div>
                    <div class="step-icon-box">
                        <i class="fas fa-house-chimney-user"></i>
                    </div>
                    <h3 class="step-title">Create Your Family</h3>
                    <p class="step-description">Set up your family, add your kids, and customize allowance settings for each child in just a few minutes.</p>
                </div>
                <div class="step-card">
                    <div class="step-number-badge">2</div>
                    <div class="step-icon-box">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <h3 class="step-title">Configure Allowances</h3>
                    <p class="step-description">Set weekly allowance amounts and a points system tied to expectations. Allowances post automatically each week.</p>
                </div>
                <div class="step-card">
                    <div class="step-number-badge">3</div>
                    <div class="step-icon-box">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <h3 class="step-title">Kids Set Savings Goals</h3>
                    <p class="step-description">Kids choose what they're saving for, track their progress, and watch their balance grow toward something they care about.</p>
                </div>
                <div class="step-card">
                    <div class="step-number-badge">4</div>
                    <div class="step-icon-box">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="step-title">Watch Confidence Grow</h3>
                    <p class="step-description">Parents get full transparency. Kids get real motivation. Everyone benefits from a system that just works.</p>
                </div>
            </div>
        </section>

        <!-- Core Features -->
        <section class="section section-white">
            <div class="section-label">Features</div>
            <h2 class="section-title">Everything your family needs</h2>
            <p class="section-subtitle">Purpose-built tools for teaching kids real financial responsibility.</p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon-box fi-indigo">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Automated Allowance</h3>
                        <p>Allowances post automatically on the day you choose. No more forgotten paydays or manual transfers.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-box fi-violet">
                        <i class="fas fa-star-half-stroke"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Points &amp; Accountability</h3>
                        <p>Kids start each week with full points. Parents adjust based on behavior. Allowance requires points to post.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-box fi-blue">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Goal-Based Saving</h3>
                        <p>Kids pick something they want, set a target amount, and watch their goal fill up — saving with a clear purpose they actually care about.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-box fi-emerald">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Multi-Kid Dashboard</h3>
                        <p>Manage every child from one parent dashboard. Each kid gets their own secure login and personalized view.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-box fi-amber">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Full Transaction History</h3>
                        <p>Every deposit, withdrawal, and allowance is logged. Kids and parents can see the complete ledger anytime.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-box fi-rose">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Parent-Controlled</h3>
                        <p>Parents approve everything. Kids learn within guardrails. Safe, secure, and designed for family trust.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Coming Soon Features -->
        <section class="coming-soon-section">
            <div class="section-label" style="position:relative;z-index:1;">What's Coming</div>
            <h2 class="section-title" style="position:relative;z-index:1;">The roadmap is full.</h2>
            <p class="section-subtitle" style="position:relative;z-index:1;color:#94a3b8;">AllowanceLab is actively growing. Here's what's being built next.</p>
            <div class="coming-soon-grid">
                <div class="cs-card">
                    <div class="cs-card-top">
                        <div class="feature-icon-box fi-teal">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <span class="cs-badge">Coming Soon</span>
                    </div>
                    <div>
                        <h3 class="cs-title">Rainy Day Savings</h3>
                        <p class="cs-desc">A dedicated savings bucket — not tied to any goal, just smart habit-building. Kids set aside money for the unexpected and learn that saving "just because" is a superpower.</p>
                    </div>
                </div>
                <div class="cs-card">
                    <div class="cs-card-top">
                        <div class="feature-icon-box fi-orange">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <span class="cs-badge">Coming Soon</span>
                    </div>
                    <div>
                        <h3 class="cs-title">Giving Bucket</h3>
                        <p class="cs-desc">Kids allocate a portion of their allowance to giving. When they're ready, they request a donation to a church, charity, or cause they care about — generosity made into a habit.</p>
                    </div>
                </div>
                <div class="cs-card">
                    <div class="cs-card-top">
                        <div class="feature-icon-box fi-sky">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <span class="cs-badge">Coming Soon</span>
                    </div>
                    <div>
                        <h3 class="cs-title">Chore Tracking</h3>
                        <p class="cs-desc">Parents assign chores, kids check them off. A parent approval workflow keeps everyone accountable and gives kids a direct connection between effort and earning.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why It Works -->
        <section class="section section-light">
            <div class="section-label">Why It Works</div>
            <h2 class="section-title">Money skills are life skills.</h2>
            <div class="why-content">
                <p class="why-lead">AllowanceLab makes financial responsibility tangible. Kids don't just hear lessons &mdash; they experience them, week after week.</p>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-check"><i class="fas fa-check"></i></div>
                        <span class="benefit-text">Learn the value of work and accountability</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-check"><i class="fas fa-check"></i></div>
                        <span class="benefit-text">Understand saving vs. spending</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-check"><i class="fas fa-check"></i></div>
                        <span class="benefit-text">Practice patience and delayed gratification</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-check"><i class="fas fa-check"></i></div>
                        <span class="benefit-text">Build early confidence managing money</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-check"><i class="fas fa-check"></i></div>
                        <span class="benefit-text">Develop habits that last into adulthood</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-check"><i class="fas fa-check"></i></div>
                        <span class="benefit-text">Real accountability, not just rewards</span>
                    </div>
                </div>
                <p class="why-tagline">This isn't just allowance tracking. It's life training.</p>
            </div>
        </section>

        <!-- Designed for Kids and Parents -->
        <section class="section section-white">
            <div class="section-label">For Everyone</div>
            <h2 class="section-title">Designed for the whole family</h2>
            <p class="section-subtitle">Two dashboards, one system. Parents stay in control, kids stay engaged.</p>
            <div class="two-column">
                <div class="column-card">
                    <div class="column-header">
                        <div class="column-header-icon indigo">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="column-title">For Parents</h3>
                    </div>
                    <ul class="column-list">
                        <li><i class="fas fa-check-circle"></i> Manage all kids from one dashboard</li>
                        <li><i class="fas fa-check-circle"></i> Set and automate weekly allowances</li>
                        <li><i class="fas fa-check-circle"></i> Adjust points anytime</li>
                        <li><i class="fas fa-check-circle"></i> Review full transaction history</li>
                        <li><i class="fas fa-check-circle"></i> Invite co-parents to your family</li>
                        <li><i class="fas fa-check-circle"></i> Approve goal redemptions</li>
                    </ul>
                </div>
                <div class="column-card">
                    <div class="column-header">
                        <div class="column-header-icon blue">
                            <i class="fas fa-child-reaching"></i>
                        </div>
                        <h3 class="column-title">For Kids</h3>
                    </div>
                    <ul class="column-list">
                        <li><i class="fas fa-check-circle"></i> Their own secure login and dashboard</li>
                        <li><i class="fas fa-check-circle"></i> See their balance and transaction history</li>
                        <li><i class="fas fa-check-circle"></i> Create and track savings goals</li>
                        <li><i class="fas fa-check-circle"></i> Watch goal progress fill up in real time</li>
                        <li><i class="fas fa-check-circle"></i> Customizable avatar and profile color</li>
                        <li><i class="fas fa-check-circle"></i> Simple, kid-friendly interface</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Founder Message -->
        <section class="section section-dark">
            <div class="section-label" style="color: #6ee7b7;">A Message from the Founder</div>
            <h2 class="section-title section-title-dark">Built for my own family first</h2>
            <div class="founder-card">
                <div class="founder-quote-mark">"</div>
                <p class="founder-message">I built AllowanceLab because I wanted a real way to teach my kids that money requires responsibility — that allowance is earned, not guaranteed. Every week I was manually transferring money and trying to remember who got what, and I knew there had to be a better way. What started as a tool for my own household grew into something I'm genuinely proud to share. It's free, it's honest, and it works the way real families actually think about money.</p>
                <div class="founder-footer">
                    <div class="founder-avatar">JS</div>
                    <div class="founder-info">
                        <span class="founder-name">Joshua Sopko</span>
                        <span class="founder-role">Father &amp; Founder, AllowanceLab</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <h2 class="cta-title">Start your family today</h2>
            <p class="cta-subtitle">Free to set up. No credit card required. Building better money habits takes 5 minutes.</p>
            <a href="{{ route('register') }}" class="btn-cta-white">
                <i class="fas fa-rocket"></i>
                Get Started Free
            </a>
        </section>

        <!-- Footer -->
        <footer>
            <div class="footer-content">
                <div>
                    <div class="footer-brand">AllowanceLab</div>
                    <p class="footer-tagline">Simple tools for real-world money lessons. Teaching kids financial responsibility one allowance at a time.</p>
                </div>
                <div>
                    <h4 class="footer-section-title">Company</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('about') }}">About</a></li>
                        <li><a href="{{ route('faq') }}">FAQ</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-section-title">Legal</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2026 AllowanceLab. All rights reserved.
            </div>
        </footer>
    </div> <!-- End #website-content -->

    <!-- PWA Install Toast (Mobile Only) -->
    <div id="pwa-toast" class="pwa-toast" role="status" aria-live="polite">
        <div class="pwa-toast-icon">
            <i class="fas fa-mobile-screen-button"></i>
        </div>
        <div class="pwa-toast-content">
            <span class="pwa-toast-text">Add AllowanceLab to your home screen for quick access</span>
            <button id="pwa-toast-install-btn" class="pwa-toast-btn">Add to Home Screen</button>
        </div>
        <button class="pwa-toast-close" id="pwa-toast-close" aria-label="Dismiss">
            <i class="fas fa-xmark"></i>
        </button>
    </div>

    <script>
        // Scroll-aware header
        (function() {
            const header = document.getElementById('site-header');
            const menu   = document.getElementById('mobile-menu');
            const logo   = document.getElementById('header-logo');
            const logoWhite   = '{{ asset('/images/Allowance-Lab-logo-white.png') }}';
            const logoColored = '{{ asset('/images/Allowance-Lab-logo.png') }}';

            function onScroll() {
                if (window.scrollY > 80) {
                    header.classList.add('scrolled');
                    if (menu) menu.classList.add('scrolled');
                    logo.src = logoColored;
                } else {
                    header.classList.remove('scrolled');
                    if (menu) menu.classList.remove('scrolled');
                    logo.src = logoWhite;
                }
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();

        // Mobile hamburger menu
        (function() {
            const btn  = document.getElementById('hamburger-btn');
            const menu = document.getElementById('mobile-menu');
            if (!btn || !menu) return;

            function closeMenu() {
                menu.classList.remove('open');
                btn.classList.remove('open');
            }

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = menu.classList.toggle('open');
                btn.classList.toggle('open', isOpen);
            });

            // Close when tapping outside
            document.addEventListener('click', function(e) {
                if (!menu.contains(e.target) && e.target !== btn) {
                    closeMenu();
                }
            });

            // Close on scroll
            window.addEventListener('scroll', closeMenu, { passive: true });
        })();

        // PWA Mode Detection and Conditional Rendering
        const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        const isKidLoggedIn = {{ Auth::guard('kid')->check() ? 'true' : 'false' }};

        if (isPWA) {
            // If user is logged in, redirect to appropriate dashboard
            if (isLoggedIn) {
                window.location.href = '{{ route('dashboard') }}';
            } else if (isKidLoggedIn) {
                window.location.href = '{{ route('kid.dashboard') }}';
            } else {
                // Show PWA home screen
                document.getElementById('pwa-home').style.display = 'flex';
                document.getElementById('website-content').style.display = 'none';
            }
        } else {
            // Show marketing website
            document.getElementById('pwa-home').style.display = 'none';
            document.getElementById('website-content').style.display = 'block';
        }

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

        // ===== PWA INSTALL TOAST =====
        (function() {
            const DISMISSED_KEY = 'pwa_toast_dismissed';
            const toast = document.getElementById('pwa-toast');
            const installBtn = document.getElementById('pwa-toast-install-btn');
            const closeBtn = document.getElementById('pwa-toast-close');

            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            const isStandalone = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;
            let deferredPrompt = null;

            // Register service worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }

            // Don't show if already a PWA or previously dismissed
            if (isStandalone || localStorage.getItem(DISMISSED_KEY)) return;

            function showToast() {
                if (toast) toast.classList.add('show');
            }

            function hideToast(permanent) {
                if (toast) toast.classList.remove('show');
                if (permanent) localStorage.setItem(DISMISSED_KEY, '1');
            }

            // Capture Android install prompt
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
            });

            // Show after 3.5 second delay
            setTimeout(showToast, 3500);

            // Dismiss button
            if (closeBtn) {
                closeBtn.addEventListener('click', () => hideToast(true));
            }

            // Install button
            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    if (isIOS) {
                        // iOS: show a friendly inline tip instead of alert
                        installBtn.textContent = 'Tap Share → Add to Home Screen';
                        installBtn.style.background = '#6366f1';
                        setTimeout(() => hideToast(true), 4000);
                        return;
                    }
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        deferredPrompt = null;
                        if (outcome === 'accepted') hideToast(true);
                    } else {
                        installBtn.textContent = 'Use browser menu → Add to Home Screen';
                        installBtn.style.fontSize = '11px';
                        setTimeout(() => hideToast(true), 4000);
                    }
                });
            }

            // Auto-hide after install
            window.addEventListener('appinstalled', () => {
                deferredPrompt = null;
                hideToast(true);
            });
        })();
    </script>
</body>

</html>
