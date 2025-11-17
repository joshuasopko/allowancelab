<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - AllowanceLab</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* Header */
        .top-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-nav {
            display: flex;
            gap: 32px;
            margin-left: 48px;
        }

        .header-nav a {
            color: #555;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .header-nav a:hover {
            color: #4CAF50;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 40px;
            height: 40px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 8px;
        }

        .hamburger span {
            width: 24px;
            height: 3px;
            background: #444;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .add-kid-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .add-kid-btn:hover {
            background: #45a049;
        }

        /* Layout Container */
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 73px);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: #f8f9fa;
            border-right: 2px solid #e8eaed;
            padding: 32px 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-welcome {
            padding: 0 24px 24px 24px;
            font-size: 18px;
            font-weight: 600;
            color: #444;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 16px;
        }

        .sidebar-features {
            display: none;
            flex-direction: column;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 16px;
        }

        .sidebar-features .menu-item {
            padding: 12px 24px;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
        }

        .menu-item {
            padding: 14px 24px;
            color: #555;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }

        .menu-item.has-subtext {
            padding-bottom: 8px;
        }

        .menu-subtext {
            font-size: 13px;
            color: #4CAF50;
            font-weight: 400;
            margin-top: 4px;
        }

        .menu-item:hover {
            background: #e8eaed;
            color: #1a1a1a;
        }

        .menu-item.active {
            background: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
            padding-left: 20px;
        }

        .menu-divider {
            height: 1px;
            background: #e0e0e0;
            margin: 12px 0;
        }

        .menu-item.sign-out {
            margin-top: auto;
            color: #ef5350;
        }

        .menu-item.sign-out:hover {
            background: #ffebee;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }

        .content-wrapper {
            max-width: 960px;
            margin: 0 auto;
        }

        /* Kid Card */
        .kid-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .kid-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #80d4b0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 600;
            color: white;
        }

        .kid-details h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .kid-age {
            font-size: 16px;
            color: #666;
        }

        .points-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 16px;
        }

        .points-high {
            background: #d4edda;
            color: #155724;
        }

        .points-medium {
            background: #fff3cd;
            color: #856404;
        }

        .points-low {
            background: #f8d7da;
            color: #721c24;
        }

        .balance-section {
            text-align: center;
            margin-bottom: 12px;
        }

        .balance {
            font-size: 56px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .balance.negative {
            color: #ef5350;
        }

        .next-allowance {
            font-size: 14px;
            color: #888;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 0;
        }

        .action-btn {
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: white;
        }

        .btn-deposit {
            background: #4CAF50;
            order: 0;
        }

        .btn-deposit:hover {
            background: #45a049;
        }

        .btn-spend {
            background: #ef5350;
            order: 0;
        }

        .btn-spend:hover {
            background: #e53935;
        }

        .btn-points {
            background: #42a5f5;
            order: 0;
        }

        .btn-points:hover {
            background: #1e88e5;
        }

        .btn-ledger {
            background: #78909c;
            order: 0;
        }

        .btn-ledger:hover {
            background: #607d8b;
        }

        .btn-ledger.active {
            background: #546e7a;
        }

        /* Dropdown Forms */
        .dropdown-form {
            display: none;
            grid-column: 1 / -1;
            order: 5;
        }

        .dropdown-form.open {
            display: block;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            margin-top: 0;
            animation: slideDownFadeIn 0.4s ease-out forwards;
        }

        @keyframes slideDownFadeIn {
            to {
                max-height: 600px;
                opacity: 1;
                margin-top: 16px;
            }
        }

        .dropdown-form.closing {
            display: block;
            max-height: 600px;
            opacity: 1;
            margin-top: 16px;
            animation: slideUpFadeOut 0.4s ease-in forwards;
        }

        @keyframes slideUpFadeOut {
            to {
                max-height: 0;
                opacity: 0;
                margin-top: 0;
            }
        }

        #depositForm {
            order: 1;
        }

        #spendForm {
            order: 2;
        }

        #pointsForm {
            order: 3;
        }

        #ledgerForm {
            order: 4;
        }

        .form-content {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 24px;
            margin-top: 12px;
        }

        .inline-form {
            display: flex;
            gap: 12px;
            align-items: end;
        }

        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .inline-form .form-group {
            margin-bottom: 0;
            flex: 1;
            position: relative;
        }

        .inline-form .form-group:first-child {
            flex: 0 0 140px;
        }

        .inline-form .submit-btn {
            flex: 0 0 160px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .form-input.error {
            border-color: #ef5350;
            animation: shake 0.4s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-8px);
            }

            75% {
                transform: translateX(8px);
            }
        }

        .error-message {
            color: #ef5350;
            font-size: 13px;
            position: absolute;
            top: 100%;
            left: 5px;
            margin-top: 4px;
            display: none;
            white-space: nowrap;
        }

        .error-message.show {
            display: block;
        }

        .currency-input {
            font-size: 18px;
            font-weight: 600;
            text-align: center;
        }

        .points-control {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
        }

        .points-adjust-group {
            flex: 0 0 200px;
        }

        .current-points {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
        }

        .points-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #42a5f5;
            background: white;
            color: #42a5f5;
            border-radius: 8px;
            font-size: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .points-btn:hover {
            background: #42a5f5;
            color: white;
        }

        .points-input {
            flex: 1;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            min-width: 80px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
            position: relative;
        }

        .submit-btn.loading {
            cursor: not-allowed;
            opacity: 0.9;
        }

        .submit-btn .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .submit-btn.success {
            transform: scale(1.02);
        }

        .submit-deposit {
            background: #4CAF50;
        }

        .submit-deposit:hover {
            background: #45a049;
        }

        .submit-deposit.success {
            background: #2e7d32;
        }

        .submit-spend {
            background: #ef5350;
        }

        .submit-spend:hover {
            background: #e53935;
        }

        .submit-spend.success {
            background: #2e7d32;
        }

        .submit-points {
            background: #42a5f5;
        }

        .submit-points:hover {
            background: #1e88e5;
        }

        .submit-points.success {
            background: #2e7d32;
        }

        /* Ledger */
        .ledger-filters {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            color: #555;
        }

        .filter-btn:hover {
            border-color: #4CAF50;
        }

        .filter-btn.active {
            background: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }

        .ledger-table {
            max-height: 350px;
            overflow-y: auto;
        }

        .ledger-entry {
            padding: 16px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .ledger-entry:last-child {
            border-bottom: none;
        }

        .entry-details {
            flex: 1;
        }

        .entry-type {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .entry-type.deposit {
            color: #4CAF50;
        }

        .entry-type.spend {
            color: #ef5350;
        }

        .entry-type.points {
            color: #42a5f5;
        }

        .entry-note {
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }

        .entry-date {
            font-size: 12px;
            color: #999;
        }

        .entry-amount {
            font-size: 18px;
            font-weight: 600;
            text-align: right;
        }

        .view-all-btn {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            border: 2px solid #4CAF50;
            background: white;
            color: #4CAF50;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-all-btn:hover {
            background: #4CAF50;
            color: white;
        }

        .card-footer {
            text-align: right;
            padding-top: 16px;
            border-top: 1px solid #e0e0e0;
            margin-top: 20px;
        }

        .manage-link {
            color: #888;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .manage-link:hover {
            color: #4CAF50;
        }

        /* Transaction Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 900px;
            height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 24px 32px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #888;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #f0f0f0;
            color: #333;
        }

        .modal-filters {
            padding: 20px 32px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .modal-filter-tabs {
            display: flex;
            gap: 8px;
            flex: 1;
        }

        .modal-filter-btn {
            padding: 8px 16px;
            border: 1px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #555;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-filter-btn:hover {
            background: #f5f5f5;
        }

        .modal-filter-btn.active {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }

        .modal-time-filter {
            padding: 8px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #555;
            background: white;
            cursor: pointer;
        }

        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 32px;
        }

        .modal-ledger-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .modal-ledger-entry:last-child {
            border-bottom: none;
        }

        .modal-entry-details {
            flex: 1;
        }

        .modal-entry-type {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .modal-entry-type.deposit {
            color: #4CAF50;
        }

        .modal-entry-type.spend {
            color: #ef5350;
        }

        .modal-entry-type.points {
            color: #42a5f5;
        }

        .modal-entry-note {
            font-size: 14px;
            color: #555;
            margin-bottom: 4px;
        }

        .modal-entry-date {
            font-size: 13px;
            color: #888;
        }

        .modal-entry-amount {
            font-size: 18px;
            font-weight: 700;
            white-space: nowrap;
            margin-left: 16px;
        }

        .modal-entry-amount.deposit {
            color: #4CAF50;
        }

        .modal-entry-amount.spend {
            color: #ef5350;
        }

        .modal-entry-amount.points {
            color: #42a5f5;
        }

        /* Mobile Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 73px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .header-nav {
                display: none;
            }

            .sidebar-features {
                display: flex;
            }

            .top-header {
                padding: 16px 20px;
            }

            .add-kid-btn {
                font-size: 13px;
                padding: 8px 16px;
            }

            .sidebar {
                position: fixed;
                top: 73px;
                left: -280px;
                height: calc(100vh - 73px);
                z-index: 100;
                transition: left 0.3s;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                padding: 20px;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .balance {
                font-size: 42px;
            }

            .inline-form {
                flex-direction: column;
                align-items: stretch;
            }

            .inline-form .form-group {
                margin-bottom: 12px;
            }

            .inline-form .form-group:first-child {
                flex: 1;
            }

            .inline-form .submit-btn {
                flex: 1;
                margin-top: 4px;
            }

            .points-control {
                margin-bottom: 0 !important;
            }

            .points-adjust-group {
                flex: 1 !important;
            }

            .action-btn,
            .dropdown-form {
                order: 0 !important;
            }
        }
    </style>
</head>

<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="header-left">
            <div class="hamburger" onclick="toggleSidebar()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="logo-section">
                <div class="logo">💰</div>
                <div class="brand-name">AllowanceLab</div>
            </div>
            <nav class="header-nav">
                <a href="#chore-list">Chore List</a>
                <a href="#goals">Goals</a>
                <a href="#loans">Loans</a>
                <a href="#jobs">Jobs</a>
            </nav>
        </div>
        <div class="header-right">
            <button class="add-kid-btn">+ Add Kid</button>
        </div>
    </header>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-welcome">Welcome, Joshua!</div>
            <div class="sidebar-features">
                <a href="#chore-list" class="menu-item">Chore List</a>
                <a href="#goals" class="menu-item">Goals</a>
                <a href="#loans" class="menu-item">Loans</a>
                <a href="#jobs" class="menu-item">Jobs</a>
            </div>
            <nav class="sidebar-menu">
                <a href="#account-info" class="menu-item has-subtext">
                    Account Info
                    <div class="menu-subtext">Sopko Family</div>
                </a>
                <a href="#dashboard" class="menu-item active">Dashboard</a>
                <a href="#settings" class="menu-item">Settings</a>
                <a href="#billing" class="menu-item">Billing</a>
                <a href="#help" class="menu-item">Help</a>
                <div class="menu-divider"></div>
                <a href="#family-settings" class="menu-item">Family Settings</a>
                <a href="#preferences" class="menu-item">Preferences</a>
                <div class="menu-divider"></div>
                <a href="#sign-out" class="menu-item sign-out">Sign Out</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-wrapper">
                <!-- Briella Card -->
                <div class="kid-card">
                    <div class="card-header">
                        <div class="kid-info">
                            <div class="avatar" style="background: #80d4b0;">B</div>
                            <div class="kid-details">
                                <h2>Briella</h2>
                                <div class="kid-age">Age 11</div>
                            </div>
                        </div>
                        <div class="points-badge points-medium" id="pointsBadge">7 / 10</div>
                    </div>

                    <div class="balance-section">
                        <div class="balance" id="balance">$47.50</div>
                        <div class="next-allowance">Next allowance: $11.00 on Friday, Nov 15</div>
                    </div>

                    <div class="action-buttons">
                        <button class="action-btn btn-deposit" onclick="toggleForm('deposit')">Deposit Money</button>

                        <!-- Deposit Form -->
                        <div class="dropdown-form" id="depositForm">
                            <div class="form-content">
                                <div class="inline-form">
                                    <div class="form-group">
                                        <label class="form-label">Amount</label>
                                        <input type="text" class="form-input currency-input" id="depositAmount"
                                            placeholder="$0.00" oninput="formatCurrency(this)">
                                        <div class="error-message" id="depositAmountError">Please enter at least $0.01
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Note:</label>
                                        <input type="text" class="form-input" id="depositNote"
                                            placeholder="What was this for?">
                                        <div class="error-message" id="depositNoteError">Please add a note</div>
                                    </div>
                                    <button class="submit-btn submit-deposit" onclick="submitDeposit()">Record
                                        Deposit</button>
                                </div>
                            </div>
                        </div>

                        <button class="action-btn btn-spend" onclick="toggleForm('spend')">Record Spend</button>

                        <!-- Spend Form -->
                        <div class="dropdown-form" id="spendForm">
                            <div class="form-content">
                                <div class="inline-form">
                                    <div class="form-group">
                                        <label class="form-label">Amount</label>
                                        <input type="text" class="form-input currency-input" id="spendAmount"
                                            placeholder="$0.00" oninput="formatCurrency(this)">
                                        <div class="error-message" id="spendAmountError">Please enter at least $0.01
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Note:</label>
                                        <input type="text" class="form-input" id="spendNote"
                                            placeholder="What did they buy?">
                                        <div class="error-message" id="spendNoteError">Please add a note</div>
                                    </div>
                                    <button class="submit-btn submit-spend" onclick="submitSpend()">Record
                                        Spend</button>
                                </div>
                            </div>
                        </div>

                        <button class="action-btn btn-points" onclick="toggleForm('points')">Adjust Points</button>

                        <!-- Points Form -->
                        <div class="dropdown-form" id="pointsForm">
                            <div class="form-content">
                                <div class="current-points">Current: 7 / 10 points</div>
                                <div class="inline-form">
                                    <div class="form-group points-adjust-group">
                                        <label class="form-label">Adjust</label>
                                        <div class="points-control">
                                            <button class="points-btn" onclick="adjustPoints(-1)">−</button>
                                            <input type="text" class="form-input points-input" id="pointsInput"
                                                placeholder="+2 or -1" value="7">
                                            <button class="points-btn" onclick="adjustPoints(1)">+</button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Reason:</label>
                                        <input type="text" class="form-input" id="pointsReason"
                                            placeholder="Why are you adjusting points?">
                                        <div class="error-message" id="pointsReasonError">Please add a reason</div>
                                    </div>
                                    <button class="submit-btn submit-points" onclick="submitPoints()">Adjust
                                        Points</button>
                                </div>
                            </div>
                        </div>

                        <button class="action-btn btn-ledger" id="ledgerBtn" onclick="toggleForm('ledger')">View
                            Ledger</button>

                        <!-- Ledger -->
                        <div class="dropdown-form" id="ledgerForm">
                            <div class="form-content">
                                <div class="ledger-filters">
                                    <button class="filter-btn active" onclick="filterLedger('all')">All</button>
                                    <button class="filter-btn" onclick="filterLedger('deposit')">Deposits</button>
                                    <button class="filter-btn" onclick="filterLedger('spend')">Spends</button>
                                    <button class="filter-btn" onclick="filterLedger('points')">Point
                                        Adjustments</button>
                                </div>
                                <div class="ledger-table" id="ledgerTable">
                                    <!-- Ledger entries will be populated by JavaScript -->
                                </div>
                                <button class="view-all-btn" onclick="viewAllTransactions()">View All
                                    Transactions</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="#manage" class="manage-link">Manage Kid</a>
                    </div>
                </div>

                <!-- Beckett Card -->
                <div class="kid-card">
                    <div class="card-header">
                        <div class="kid-info">
                            <div class="avatar" style="background: #ffd54f;">B</div>
                            <div class="kid-details">
                                <h2>Beckett</h2>
                                <div class="kid-age">Age 10</div>
                            </div>
                        </div>
                        <div class="points-badge points-high">9 / 10</div>
                    </div>

                    <div class="balance-section">
                        <div class="balance">$32.75</div>
                        <div class="next-allowance">Next allowance: $10.00 on Friday, Nov 15</div>
                    </div>

                    <div class="action-buttons">
                        <button class="action-btn btn-deposit">Deposit Money</button>
                        <button class="action-btn btn-spend">Record Spend</button>
                        <button class="action-btn btn-points">Adjust Points</button>
                        <button class="action-btn btn-ledger">View Ledger</button>
                    </div>

                    <div class="card-footer">
                        <a href="#manage" class="manage-link">Manage Kid</a>
                    </div>
                </div>

                <!-- Freya Card -->
                <div class="kid-card">
                    <div class="card-header">
                        <div class="kid-info">
                            <div class="avatar" style="background: #9fa8da;">F</div>
                            <div class="kid-details">
                                <h2>Freya</h2>
                                <div class="kid-age">Age 8</div>
                            </div>
                        </div>
                        <div class="points-badge points-low">4 / 10</div>
                    </div>

                    <div class="balance-section">
                        <div class="balance">$18.50</div>
                        <div class="next-allowance">Next allowance: $8.00 on Friday, Nov 15</div>
                    </div>

                    <div class="action-buttons">
                        <button class="action-btn btn-deposit">Deposit Money</button>
                        <button class="action-btn btn-spend">Record Spend</button>
                        <button class="action-btn btn-points">Adjust Points</button>
                        <button class="action-btn btn-ledger">View Ledger</button>
                    </div>

                    <div class="card-footer">
                        <a href="#manage" class="manage-link">Manage Kid</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sample ledger data
        let ledgerData = [
            { type: 'deposit', amount: 10.00, note: 'Weekly allowance', date: '2025-11-08' },
            { type: 'spend', amount: -5.25, note: 'Ice cream at school', date: '2025-11-07' },
            { type: 'points', amount: '+2', note: 'Extra help with dishes', date: '2025-11-06' },
            { type: 'deposit', amount: 15.00, note: 'Birthday money from grandma', date: '2025-11-05' },
            { type: 'spend', amount: -12.50, note: 'New book from bookstore', date: '2025-11-04' },
            { type: 'points', amount: '-1', note: 'Forgot to feed the dog', date: '2025-11-03' },
            { type: 'deposit', amount: 10.00, note: 'Weekly allowance', date: '2025-11-01' },
            { type: 'spend', amount: -7.75, note: 'Movie ticket', date: '2025-10-30' }
        ];

        let currentFilter = 'all';
        let activeForm = null;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function toggleForm(formName) {
            const forms = ['deposit', 'spend', 'points', 'ledger'];
            const ledgerBtn = document.getElementById('ledgerBtn');

            // If clicking the same form that's open, close it with animation
            if (activeForm === formName) {
                const form = document.getElementById(formName + 'Form');
                form.classList.add('closing');
                setTimeout(() => {
                    form.classList.remove('open');
                    form.classList.remove('closing');
                }, 400);
                if (formName === 'ledger') {
                    ledgerBtn.textContent = 'View Ledger';
                    ledgerBtn.classList.remove('active');
                }
                activeForm = null;
                return;
            }

            // Close all forms instantly (no animation when switching)
            forms.forEach(name => {
                const form = document.getElementById(name + 'Form');
                form.classList.remove('open');
                form.classList.remove('closing');
                if (name === 'ledger') {
                    ledgerBtn.textContent = 'View Ledger';
                    ledgerBtn.classList.remove('active');
                }
            });

            // Open the requested form with animation
            const form = document.getElementById(formName + 'Form');
            form.classList.add('open');
            activeForm = formName;
            if (formName === 'ledger') {
                ledgerBtn.textContent = 'Close Ledger';
                ledgerBtn.classList.add('active');
                renderLedger();
            }
        }

        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            let num = parseInt(value) / 100;
            input.value = '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function adjustPoints(delta) {
            const input = document.getElementById('pointsInput');
            let current = parseInt(input.value) || 7;
            let newValue = Math.max(0, Math.min(10, current + delta));
            input.value = newValue;
        }

        function updatePointsBadge(points) {
            const badge = document.getElementById('pointsBadge');
            badge.textContent = points + ' / 10';

            if (points >= 8) {
                badge.className = 'points-badge points-high';
            } else if (points >= 5) {
                badge.className = 'points-badge points-medium';
            } else {
                badge.className = 'points-badge points-low';
            }
        }

        function updateBalance(amount) {
            const balanceEl = document.getElementById('balance');
            let current = parseFloat(balanceEl.textContent.replace(/[$,]/g, ''));
            let newBalance = current + amount;
            balanceEl.textContent = '$' + newBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Add or remove negative class
            if (newBalance < 0) {
                balanceEl.classList.add('negative');
            } else {
                balanceEl.classList.remove('negative');
            }
        }

        function submitDeposit() {
            const amount = document.getElementById('depositAmount').value;
            const note = document.getElementById('depositNote').value;
            const amountInput = document.getElementById('depositAmount');
            const amountError = document.getElementById('depositAmountError');
            const noteInput = document.getElementById('depositNote');
            const noteError = document.getElementById('depositNoteError');
            const btn = document.querySelector('.submit-deposit');

            if (!amount || amount === '$0.00' || parseFloat(amount.replace(/[$,]/g, '')) < 0.01) {
                amountInput.classList.add('error');
                amountError.classList.add('show');
                setTimeout(() => {
                    amountInput.classList.remove('error');
                }, 400);
                return;
            }

            if (!note.trim()) {
                amountError.classList.remove('show');  // Clear amount error if it was showing
                noteInput.classList.add('error');
                noteError.classList.add('show');
                setTimeout(() => {
                    noteInput.classList.remove('error');
                }, 400);
                return;
            }

            // Clear any previous errors
            amountError.classList.remove('show');
            noteError.classList.remove('show');

            const numAmount = parseFloat(amount.replace(/[$,]/g, ''));

            // Show loading spinner
            btn.innerHTML = '<span class="spinner"></span>';
            btn.classList.add('loading');
            btn.disabled = true;

            setTimeout(() => {
                updateBalance(numAmount);

                ledgerData.unshift({
                    type: 'deposit',
                    amount: numAmount,
                    note: note,
                    date: new Date().toISOString().split('T')[0]
                });

                // Show success feedback
                btn.classList.remove('loading');
                btn.textContent = '✓ Recorded!';
                btn.classList.add('success');

                // Wait, then close form with animation
                setTimeout(() => {
                    const form = document.getElementById('depositForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        activeForm = null;

                        // Reset button and form
                        document.getElementById('depositAmount').value = '';
                        document.getElementById('depositNote').value = '';
                        amountError.classList.remove('show');
                        noteError.classList.remove('show');
                        btn.textContent = 'Record Deposit';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }, 800);
        }

        function submitSpend() {
            const amount = document.getElementById('spendAmount').value;
            const note = document.getElementById('spendNote').value;
            const amountInput = document.getElementById('spendAmount');
            const amountError = document.getElementById('spendAmountError');
            const noteInput = document.getElementById('spendNote');
            const noteError = document.getElementById('spendNoteError');
            const btn = document.querySelector('.submit-spend');

            if (!amount || amount === '$0.00' || parseFloat(amount.replace(/[$,]/g, '')) < 0.01) {
                amountInput.classList.add('error');
                amountError.classList.add('show');
                setTimeout(() => {
                    amountInput.classList.remove('error');
                }, 400);
                return;
            }

            if (!note.trim()) {
                amountError.classList.remove('show');  // Clear amount error if it was showing
                noteInput.classList.add('error');
                noteError.classList.add('show');
                setTimeout(() => {
                    noteInput.classList.remove('error');
                }, 400);
                return;
            }

            // Clear any previous errors
            amountError.classList.remove('show');
            noteError.classList.remove('show');

            const numAmount = parseFloat(amount.replace(/[$,]/g, ''));

            // Show loading spinner
            btn.innerHTML = '<span class="spinner"></span>';
            btn.classList.add('loading');
            btn.disabled = true;

            setTimeout(() => {
                updateBalance(-numAmount);

                ledgerData.unshift({
                    type: 'spend',
                    amount: -numAmount,
                    note: note,
                    date: new Date().toISOString().split('T')[0]
                });

                // Show success feedback
                btn.classList.remove('loading');
                btn.textContent = '✓ Recorded!';
                btn.classList.add('success');

                // Wait, then close form with animation
                setTimeout(() => {
                    const form = document.getElementById('spendForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        activeForm = null;

                        // Reset button and form
                        document.getElementById('spendAmount').value = '';
                        document.getElementById('spendNote').value = '';
                        amountError.classList.remove('show');
                        noteError.classList.remove('show');
                        btn.textContent = 'Record Spend';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }, 800);
        }

        function submitPoints() {
            const input = document.getElementById('pointsInput').value;
            const reason = document.getElementById('pointsReason').value;
            const reasonInput = document.getElementById('pointsReason');
            const reasonError = document.getElementById('pointsReasonError');
            const btn = document.querySelector('.submit-points');

            if (!reason.trim()) {
                reasonInput.classList.add('error');
                reasonError.classList.add('show');
                setTimeout(() => {
                    reasonInput.classList.remove('error');
                }, 400);
                return;
            }

            // Clear any previous errors
            reasonError.classList.remove('show');

            let newPoints;
            if (input.startsWith('+') || input.startsWith('-')) {
                const delta = parseInt(input);
                newPoints = Math.max(0, Math.min(10, 7 + delta));
            } else {
                newPoints = Math.max(0, Math.min(10, parseInt(input)));
            }

            // Show loading spinner
            btn.innerHTML = '<span class="spinner"></span>';
            btn.classList.add('loading');
            btn.disabled = true;

            setTimeout(() => {
                updatePointsBadge(newPoints);

                ledgerData.unshift({
                    type: 'points',
                    amount: input.startsWith('+') || input.startsWith('-') ? input : (newPoints - 7 > 0 ? '+' : '') + (newPoints - 7),
                    note: reason,
                    date: new Date().toISOString().split('T')[0]
                });

                // Show success feedback
                btn.classList.remove('loading');
                btn.textContent = '✓ Adjusted!';
                btn.classList.add('success');

                // Wait, then close form with animation
                setTimeout(() => {
                    const form = document.getElementById('pointsForm');
                    form.classList.add('closing');

                    setTimeout(() => {
                        form.classList.remove('open');
                        form.classList.remove('closing');
                        activeForm = null;

                        // Reset button and form
                        document.getElementById('pointsInput').value = newPoints;
                        document.getElementById('pointsReason').value = '';
                        reasonError.classList.remove('show');
                        btn.textContent = 'Adjust Points';
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 400);
                }, 1500);
            }, 800);
        }

        function filterLedger(filter) {
            currentFilter = filter;
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            renderLedger();
        }

        function renderLedger() {
            const table = document.getElementById('ledgerTable');
            let filtered = currentFilter === 'all' ? ledgerData : ledgerData.filter(entry => entry.type === currentFilter);
            let displayed = filtered.slice(0, 8);

            table.innerHTML = displayed.map(entry => `
                <div class="ledger-entry">
                    <div class="entry-details">
                        <div class="entry-type ${entry.type}">
                            ${entry.type === 'deposit' ? 'Deposit' : entry.type === 'spend' ? 'Spend' : 'Point Adjustment'}
                        </div>
                        <div class="entry-note">${entry.note}</div>
                        <div class="entry-date">${formatDate(entry.date)}</div>
                    </div>
                    <div class="entry-amount ${entry.type}">
                        ${entry.type === 'points' ? entry.amount + ' pts' : (entry.amount >= 0 ? '+$' : '-$') + Math.abs(entry.amount).toFixed(2)}
                    </div>
                </div>
            `).join('');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        function viewAllTransactions() {
            openTransactionModal();
        }

        // Modal Functions
        function openTransactionModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.add('active');
            renderModalLedger();
        }

        function closeTransactionModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.remove('active');
        }

        let modalTypeFilter = 'all';
        let modalTimeFilter = 'all';

        function filterModalByType(type) {
            modalTypeFilter = type;
            const buttons = document.querySelectorAll('.modal-filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            renderModalLedger();
        }

        function filterModalByTime() {
            const select = document.getElementById('modalTimeFilter');
            modalTimeFilter = select.value;
            renderModalLedger();
        }

        function filterByTimeRange(entry) {
            if (modalTimeFilter === 'all') return true;

            const entryDate = new Date(entry.date);
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();

            if (modalTimeFilter === 'thisMonth') {
                return entryDate.getMonth() === currentMonth && entryDate.getFullYear() === currentYear;
            } else if (modalTimeFilter === 'lastMonth') {
                const lastMonth = new Date(currentYear, currentMonth - 1);
                return entryDate.getMonth() === lastMonth.getMonth() && entryDate.getFullYear() === lastMonth.getFullYear();
            } else if (modalTimeFilter === 'last3Months') {
                const threeMonthsAgo = new Date(currentYear, currentMonth - 3);
                return entryDate >= threeMonthsAgo;
            }
            return true;
        }

        function renderModalLedger() {
            const tbody = document.getElementById('modalLedgerBody');
            let filtered = ledgerData;

            // Filter by type
            if (modalTypeFilter !== 'all') {
                filtered = filtered.filter(entry => entry.type === modalTypeFilter);
            }

            // Filter by time
            filtered = filtered.filter(filterByTimeRange);

            if (filtered.length === 0) {
                tbody.innerHTML = '<div style="text-align: center; padding: 40px; color: #888;">No transactions found</div>';
                return;
            }

            tbody.innerHTML = filtered.map(entry => `
                <div class="modal-ledger-entry">
                    <div class="modal-entry-details">
                        <div class="modal-entry-type ${entry.type}">
                            ${entry.type === 'deposit' ? 'Deposit' : entry.type === 'spend' ? 'Spend' : 'Point Adjustment'}
                        </div>
                        <div class="modal-entry-note">${entry.note}</div>
                        <div class="modal-entry-date">${formatDate(entry.date)}</div>
                    </div>
                    <div class="modal-entry-amount ${entry.type}">
                        ${entry.type === 'points' ? entry.amount + ' pts' : (entry.amount >= 0 ? '+$' : '-$') + Math.abs(entry.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </div>
                </div>
            `).join('');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('transactionModal');
            if (e.target === modal) {
                closeTransactionModal();
            }
        });
    </script>

    <!-- Transaction Modal -->
    <div id="transactionModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">All Transactions</h2>
                <button class="modal-close" onclick="closeTransactionModal()">&times;</button>
            </div>
            <div class="modal-filters">
                <div class="modal-filter-tabs">
                    <button class="modal-filter-btn active" onclick="filterModalByType('all')">All</button>
                    <button class="modal-filter-btn" onclick="filterModalByType('deposit')">Deposits</button>
                    <button class="modal-filter-btn" onclick="filterModalByType('spend')">Spends</button>
                    <button class="modal-filter-btn" onclick="filterModalByType('points')">Points</button>
                </div>
                <select id="modalTimeFilter" class="modal-time-filter" onchange="filterModalByTime()">
                    <option value="all">All Time</option>
                    <option value="thisMonth">This Month</option>
                    <option value="lastMonth">Last Month</option>
                    <option value="last3Months">Last 3 Months</option>
                </select>
            </div>
            <div class="modal-body">
                <div id="modalLedgerBody"></div>
            </div>
        </div>
    </div>
</body>

</html>