@extends('layouts.kid')

@section('title', 'My Dashboard - AllowanceLab')

@section('content')
    @php
        // Convert hex to RGB and create lighter shade
        $hex = ltrim($kid->color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        // Mix with white (85% white + 15% color for light background)
        $lightR = round($r * 0.15 + 255 * 0.85);
        $lightG = round($g * 0.15 + 255 * 0.85);
        $lightB = round($b * 0.15 + 255 * 0.85);
        $lightShade = "rgb($lightR, $lightG, $lightB)";

        // Create medium shade for parent transactions (70% white + 30% color)
        $mediumR = round($r * 0.28 + 255 * 0.72);
        $mediumG = round($g * 0.28 + 255 * 0.72);
        $mediumB = round($b * 0.28 + 255 * 0.72);
        $mediumShade = "rgb($mediumR, $mediumG, $mediumB)";
    @endphp

    <style>
        /* Dynamic theme color based on kid's selection */
        .kid-header::after {
            background-color:
                {{ $kid->color }}
                !important;
        }

        .kid-birthday-countdown {
            color:
                {{ $kid->color }}
                !important;
            border-bottom-color:
                {{ $kid->color }}
                !important;
        }

        .kid-birthday-icon {
            background-color:
                {{ $kid->color }}
                !important;
        }

        .kid-menu-divider {
            background-color:
                {{ $kid->color }}
                !important;
        }

        .kid-next-allowance .days-away {
            color:
                {{ $kid->color }}
                !important;
        }

        .kid-ledger-entry {
            border-left-color:
                {{ $kid->color }}
                !important;
            background:
                {{ $lightShade }}
                !important;
        }

        .kid-ledger-entry.parent-initiated {
            background:
                {{ $mediumShade }}
                !important;
        }

        .kid-ledger-entry.parent-initiated {
            background:
                {{ $mediumShade }}
                !important;
        }

        .kid-modal-ledger-entry.parent-initiated {
            background:
                {{ $mediumShade }}
                !important;
        }

        .kid-modal-ledger-entry.denied-allowance {
            background: #ffebee !important;
            border-left-color: #ef5350 !important;
        }

        .kid-parent-icon {
            font-size: 22px;
            font-weight: 900;
            color:
                {{ $kid->color }}
                !important;
            line-height: 1;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Menu active state with theme color */
        .kid-sidebar .kid-menu-item.active {
            background:
                {{ $lightShade }}
                !important;
            color:
                {{ $kid->color }}
                !important;
            border-left-color:
                {{ $kid->color }}
                !important;
        }

        /* Coming soon badge with theme color */
        .kid-sidebar .kid-coming-soon-badge {
            background:
                {{ $lightShade }}
                !important;
            color:
                {{ $kid->color }}
                !important;
        }

        /* Dynamic theme color based on kid's selection */
        .kid-header::after {
            background-color:
                {{ $kid->color }}
                !important;
        }

        .kid-modal-ledger-entry {
            border-left-color:
                {{ $kid->color }}
                !important;
            background:
                {{ $lightShade }}
                !important;
        }

        .kid-modal-ledger-entry:hover {
            background: color-mix(in srgb,
                    {{ $kid->color }}
                    20%, white) !important;
        }

        /* Theme colored border and shadow for main card */
        .kid-card {
            /* border: 1px solid
                                                                                                                                                                                                                {{ $kid->color }}
            !important;
            */ box-shadow: 0 4px 16px rgba({{ $r }},
                    {{ $g }}
                    ,
                    {{ $b }}
                    , 0.50) !important;
        }

        /* Parent legend styling */
        .kid-ledger-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .kid-filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .kid-parent-legend {
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kid-parent-icon-sample {
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
        }

        .kid-ledger-entry.denied-allowance {
            background: #ffebee !important;
            border-left-color: #ef5350 !important;
        }

        @media (max-width: 900px) {
            .kid-parent-legend {
                width: 100%;
                justify-content: center;
                margin-top: 8px;
            }
        }

        /* Override absolute positioning of points pill - use in-flow within header */
        .kid-overview-header .kid-points-pill {
            position: static;
            top: auto;
            right: auto;
            margin-left: auto;
        }

        /* Overview Header */
        .kid-overview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 0 4px;
        }

        .kid-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Dashboard Tabs */
        .kid-dashboard-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .kid-dashboard-tab {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            background: white;
            border-color: #e5e7eb;
        }

        .kid-dashboard-tab:hover {
            border-color: {{ $kid->color }};
            color: {{ $kid->color }};
        }

        .kid-dashboard-tab.active {
            background: {{ $kid->color }};
            border-color: {{ $kid->color }};
            color: white;
        }

        .kid-dashboard-tab.coming-soon {
            opacity: 0.6;
            cursor: default;
        }

        .kid-tab-badge {
            font-size: 10px;
            background: #e5e7eb;
            color: #9ca3af;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 500;
        }

        .kid-dashboard-tab.active .kid-tab-badge {
            background: rgba(255,255,255,0.3);
            color: white;
        }

        .kid-tab-pending-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 9px;
            background: #f59e0b;
            color: white;
            font-size: 11px;
            font-weight: 700;
            margin-left: 5px;
            line-height: 1;
        }
        .kid-tab-ready-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 7px;
            border-radius: 9px;
            background: #10b981;
            color: white;
            font-size: 11px;
            font-weight: 700;
            margin-left: 5px;
            line-height: 1;
            animation: kid-tab-ready-pulse 2s ease-in-out infinite;
        }
        @keyframes kid-tab-ready-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .kid-tab-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 60px 20px;
            color: #9ca3af;
            font-size: 16px;
        }

        .kid-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            background: #e5e7eb;
            border-radius: 10px;
        }

        .kid-dashboard-tab.active .kid-tab-count {
            background: rgba(255,255,255,0.3);
            color: white;
        }

        /* Overview Grid */
        .kid-overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .kid-overview-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .kid-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .kid-card-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kid-card-header h3 i {
            color: {{ $kid->color }};
        }

        .kid-view-all {
            color: #3b82f6;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
        }

        .kid-view-all:hover {
            text-decoration: underline;
        }

        /* Balance Card */
        .kid-balance-amount {
            font-size: 48px;
            font-weight: 700;
            color: {{ $kid->color }};
            text-align: center;
            margin: 20px 0;
        }

        .kid-balance-actions {
            display: flex;
            gap: 12px;
        }

        .kid-inline-form { display: flex; flex-direction: column; gap: 8px; }
        .kid-inline-form-note {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .kid-inline-form-note:focus { border-color: {{ $kid->color }}; }
        .kid-inline-form-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .kid-inline-form-row .kid-form-input {
            flex: 1;
            min-width: 80px;
            padding: 10px 12px;
            font-size: 14px;
        }
        .kid-inline-form-row .kid-btn-submit-inline {
            flex: 0;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            white-space: nowrap;
        }
        .kid-inline-cancel-btn {
            flex: 0;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 18px;
            padding: 6px 8px;
            border-radius: 6px;
            line-height: 1;
            transition: color 0.15s, background 0.15s;
        }
        .kid-inline-cancel-btn:hover { color: #ef4444; background: #fee2e2; }

        /* Inline form spinner & success states */
        #kidDepositInline, #kidSpendInline {
            transition: opacity 0.4s ease;
        }
        #kidDepositInline.fading-out, #kidSpendInline.fading-out {
            opacity: 0;
        }
        .kid-btn-submit-inline.loading {
            opacity: 0.9;
            pointer-events: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .kid-btn-submit-inline.success {
            background: #10b981 !important;
        }
        @keyframes kid-spin {
            to { transform: rotate(360deg); }
        }
        .kid-spinner {
            display: inline-block;
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: kid-spin 0.6s linear infinite;
            vertical-align: middle;
        }

        .kid-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .kid-btn-deposit {
            background: #10b981;
            color: white;
        }

        .kid-btn-deposit:hover {
            background: #059669;
        }

        .kid-btn-spend {
            background: #ef4444;
            color: white;
        }

        .kid-btn-spend:hover {
            background: #dc2626;
        }

        .kid-btn-secondary {
            background: #e5e7eb;
            color: #4b5563;
        }

        .kid-btn-secondary:hover {
            background: #d1d5db;
        }

        /* Allowance Card */
        .kid-allowance-amount {
            font-size: 36px;
            font-weight: 700;
            color: #3b82f6;
            text-align: center;
            margin: 16px 0 8px;
        }

        .kid-allowance-schedule {
            text-align: center;
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .kid-next-allowance {
            text-align: center;
            font-size: 14px;
            color: #9ca3af;
        }

        /* Goals List - Overview Card (ledger style, mirrors wishes) */
        .kid-goals-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .kid-goal-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid #f0f0f0;
        }

        .kid-goal-item-thumb {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            background: #e5e7eb;
        }
        .kid-goal-item-thumb img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .kid-goal-item-thumb-placeholder {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            color: #9ca3af; font-size: 20px;
        }

        .kid-goal-item-info {
            flex: 1;
            min-width: 0;
        }

        .kid-goal-title {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .kid-goal-progress {
            margin-bottom: 4px;
        }

        .kid-progress-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 3px;
        }

        .kid-progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .kid-goal-amount {
            font-size: 12px;
            color: #6b7280;
        }

        .kid-goal-item-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
        }
        .kid-goal-item-add-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 10px; border-radius: 6px; border: none;
            font-size: 11px; font-weight: 600; color: white; cursor: pointer;
            white-space: nowrap; transition: opacity 0.15s;
        }
        .kid-goal-item-add-btn:hover { opacity: 0.88; }
        .kid-goal-item-view-btn {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 10px; border-radius: 6px;
            font-size: 11px; font-weight: 600; color: #374151;
            background: #e5e7eb; text-decoration: none;
            white-space: nowrap; transition: background 0.15s;
        }
        .kid-goal-item-view-btn:hover { background: #d1d5db; }
        .kid-goal-item-complete {
            background: #f0fdf4;
            border-color: #86efac;
        }
        .kid-goal-item-complete-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 10px; border-radius: 6px;
            font-size: 11px; font-weight: 700;
            color: #059669; background: #d1fae5;
            white-space: nowrap;
        }
        .kid-goal-item-denied-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 10px; border-radius: 6px;
            font-size: 11px; font-weight: 700;
            color: #dc2626; background: #fee2e2;
            white-space: nowrap;
        }

        /* Wishes List */
        .kid-wishes-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .kid-wish-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid #f0f0f0;
        }

        .kid-wish-item-thumb {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            background: #e5e7eb;
        }

        .kid-wish-item-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .kid-wish-item-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 20px;
        }

        .kid-wish-item-info {
            flex: 1;
            min-width: 0;
        }

        .kid-wish-title {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .kid-wish-price {
            font-size: 15px;
            font-weight: 700;
            color: {{ $kid->color }};
            margin-bottom: 4px;
        }

        .kid-wish-item-pending {
            font-size: 11px;
            font-weight: 600;
            color: #d97706;
            background: #fef3c7;
            border-radius: 6px;
            padding: 2px 7px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 6px;
        }

        .kid-wish-item-declined {
            font-size: 11px;
            font-weight: 600;
            color: #991b1b;
            background: #fee2e2;
            border-radius: 6px;
            padding: 2px 7px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 6px;
        }

        .kid-wish-item-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .kid-wish-item-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .kid-wish-item-btn-ask {
            color: white;
        }

        .kid-wish-item-btn-view {
            background: #e5e7eb;
            color: #374151;
        }

        .kid-wish-create-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            background: transparent;
            border: 1.5px dashed;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .kid-wish-create-btn:hover {
            opacity: 0.75;
        }

        /* Activity List */
        .kid-activity-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .kid-activity-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: white;
            border-radius: 14px;
            border: 1px solid #f0f0f0;
            transition: box-shadow 0.15s;
        }
        .kid-activity-item:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.07);
        }

        .kid-activity-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .kid-activity-icon.deposit {
            background: #d1fae5;
            color: #059669;
        }

        .kid-activity-icon.withdrawal {
            background: #fee2e2;
            color: #dc2626;
        }

        .kid-activity-icon.points {
            background: #dbeafe;
            color: #3b82f6;
        }

        .kid-activity-details {
            flex: 1;
            min-width: 0;
        }

        .kid-activity-note {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 6px;
        }

        .kid-activity-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .kid-activity-date {
            font-size: 12px;
            color: #9ca3af;
        }

        .kid-activity-type-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .kid-activity-type-badge.deposit { background: #d1fae5; color: #059669; }
        .kid-activity-type-badge.withdrawal { background: #fee2e2; color: #dc2626; }
        .kid-activity-type-badge.points { background: #dbeafe; color: #3b82f6; }
        .kid-activity-type-badge.allowance { background: #ede9fe; color: #7c3aed; }
        .kid-activity-type-badge.denied { background: #fee2e2; color: #dc2626; }

        .kid-activity-amount {
            font-size: 17px;
            font-weight: 800;
            white-space: nowrap;
        }

        .kid-activity-amount.deposit { color: #059669; }
        .kid-activity-amount.withdrawal { color: #dc2626; }
        .kid-activity-amount.points { color: #3b82f6; }

        /* Pagination */
        .kid-activity-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }
        .kid-activity-page-btn {
            padding: 8px 18px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            background: white;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s;
        }
        .kid-activity-page-btn:hover:not(:disabled) {
            border-color: {{ $kid->color }};
            color: {{ $kid->color }};
        }
        .kid-activity-page-btn:disabled {
            opacity: 0.35;
            cursor: default;
        }
        .kid-activity-page-info {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        /* Empty State */
        .kid-empty-state {
            text-align: center;
            padding: 32px 16px;
            color: #9ca3af;
        }

        .kid-empty-state p {
            margin-bottom: 16px;
        }

        .kid-btn-create {
            display: inline-block;
            padding: 10px 20px;
            background: {{ $kid->color }};
            color: white;
            text-decoration: none;
            border: none;
            outline: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .kid-btn-create:hover {
            opacity: 0.9;
        }

        /* Form Modals */
        .kid-form-modal,
        .kid-ledger-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .kid-form-modal.active,
        .kid-ledger-modal.active {
            display: flex;
        }

        .kid-modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }

        .kid-modal-content {
            position: relative;
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .kid-modal-large {
            max-width: 800px;
        }

        .kid-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .kid-modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
        }

        .kid-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #9ca3af;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
        }

        .kid-modal-close:hover {
            color: #4b5563;
        }

        .kid-form {
            padding: 24px;
        }

        .kid-form-group {
            margin-bottom: 20px;
        }

        .kid-form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .kid-form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
        }

        .kid-form-input:focus {
            outline: none;
            border-color: {{ $kid->color }};
        }

        .kid-error-message {
            display: none;
            color: #ef4444;
            font-size: 13px;
            margin-top: 4px;
        }

        .kid-form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        /* Mobile Responsive */
        /* Tablet/mobile range: sidebar is hidden (hamburger), full content width available */
        @media (min-width: 601px) and (max-width: 820px) {
            .kid-overview-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .kid-overview-header {
                flex-wrap: nowrap;
                gap: 12px;
            }

            .kid-header-stats {
                justify-content: flex-end;
                flex-shrink: 0;
            }
            .kid-header-stat-card {
                flex: 0 0 auto;
            }

            .kid-balance-amount {
                font-size: 40px;
                margin: 14px 0;
            }

            .kid-allowance-amount {
                font-size: 30px;
            }
        }

        @media (max-width: 768px) {
            .kid-dashboard-tabs {
                gap: 6px;
            }

            .kid-dashboard-tab {
                padding: 7px 12px;
                font-size: 13px;
            }
        }

        /* Phone - small screens */
        @media (max-width: 480px) {
            /* Grid & card sizing */
            .kid-overview-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .kid-overview-card {
                padding: 16px;
            }

            /* Balance card */
            .kid-balance-amount {
                font-size: 40px;
                margin: 12px 0;
            }
            .kid-balance-actions {
                gap: 8px;
            }
            .kid-btn {
                padding: 10px 14px;
                font-size: 14px;
            }

            /* Allowance card */
            .kid-allowance-amount {
                font-size: 32px;
            }

            /* Tabs - allow wrapping, smaller text */
            .kid-dashboard-tabs {
                gap: 5px;
                flex-wrap: wrap;
            }
            .kid-dashboard-tab {
                padding: 6px 10px;
                font-size: 12px;
                gap: 4px;
            }
            .kid-tab-ready-badge,
            .kid-tab-pending-badge,
            .kid-tab-count,
            .kid-tab-badge {
                font-size: 10px;
            }

            /* Header */
            .kid-overview-header {
                margin-bottom: 16px;
            }
            .kid-header-stats {
                gap: 8px;
            }
            .kid-header-stat-card {
                padding: 10px 12px;
            }
            .kid-header-stat-label {
                font-size: 10px;
            }
            .kid-header-stat-value {
                font-size: 18px;
            }

            /* Goal items in overview */
            .kid-goal-item {
                gap: 10px;
                padding: 10px;
            }
            .kid-goal-item-thumb {
                width: 44px;
                height: 44px;
                flex-shrink: 0;
            }
            .kid-goal-title {
                font-size: 13px;
            }
            .kid-goal-item-actions {
                gap: 6px;
            }
            .kid-goal-item-complete-badge,
            .kid-goal-item-denied-badge {
                font-size: 10px;
                padding: 4px 8px;
            }
            .kid-goal-item-view-btn,
            .kid-goal-item-add-btn {
                font-size: 12px;
                padding: 6px 10px;
            }

            /* Wish items */
            .kid-wish-item {
                gap: 10px;
                padding: 10px;
            }
            .kid-wish-thumb {
                width: 44px;
                height: 44px;
                flex-shrink: 0;
            }
            .kid-wish-title {
                font-size: 13px;
            }

            /* Activity items */
            .kid-activity-item {
                gap: 10px;
                padding: 10px 0;
            }
            .kid-activity-icon {
                width: 36px;
                height: 36px;
                font-size: 14px;
                flex-shrink: 0;
            }
            .kid-activity-desc {
                font-size: 13px;
            }
            .kid-activity-amount {
                font-size: 13px;
                white-space: nowrap;
            }

            /* Card headers */
            .kid-card-header h3 {
                font-size: 14px;
            }
            .kid-view-all {
                font-size: 12px;
            }
        }
    </style>

    <!-- Mobile Welcome Section -->
    <div class="mobile-kid-welcome">
        <h1 class="mobile-kid-welcome-title">
            Step into the lab,<br><span class="kid-name-colored" style="color: {{ $kid->color }};">{{ $kid->name }}</span>.
        </h1>
        <p class="mobile-kid-welcome-subtitle">Let's grow that allowance beaker!</p>
    </div>

    <!-- Goals Feature Launch Banner (One-time) -->
    <div class="kid-goals-launch-banner"
         style="--kid-color: {{ $kid->color }};"
         x-data="{
             show: true,
             init() {
                 // Check if user has already dismissed the launch banner
                 const dismissed = localStorage.getItem('goalsLaunchBannerDismissed');
                 if (dismissed === 'true') {
                     this.show = false;
                 }
             },
             dismiss() {
                 localStorage.setItem('goalsLaunchBannerDismissed', 'true');
                 this.show = false;
             },
             exploreGoals() {
                 localStorage.setItem('goalsLaunchBannerDismissed', 'true');
                 this.show = false;
                 if (window.kidSwitchTab) { kidSwitchTab('goals'); } else { window.location.href = '{{ route('kid.goals.index') }}'; }
             }
         }"
         x-show="show"
         x-transition>
        <div class="kid-goals-launch-content">
            <div class="kid-goals-launch-icon">🎯✨</div>
            <div class="kid-goals-launch-text">
                <strong>NEW FEATURE: Goals Are Here!</strong>
                <p>Start saving for something special! Set goals, track progress, and redeem when ready.</p>
            </div>
            <button class="kid-goals-launch-btn-inline" @click="exploreGoals()">Explore Goals →</button>
            <button class="kid-goals-launch-close" @click="dismiss()" aria-label="Dismiss">✕</button>
        </div>
    </div>

    <!-- Goal Completion Notification Banner -->
    @if($kid->hasReadyToRedeemGoals())
        <div class="kid-goals-notification-banner"
             style="--kid-color: {{ $kid->color }}; --kid-color-dark: {{ $kid->color }}dd; cursor: pointer;"
             x-data="{
                 show: true,
                 init() {
                     // Check if notification was dismissed
                     const dismissedUntil = sessionStorage.getItem('dashboardGoalNotificationDismissedUntil');
                     if (dismissedUntil) {
                         const dismissedDate = new Date(parseInt(dismissedUntil));
                         const now = new Date();
                         if (now < dismissedDate) {
                             this.show = false;
                         } else {
                             sessionStorage.removeItem('dashboardGoalNotificationDismissedUntil');
                         }
                     }
                 },
                 dismiss() {
                     const dismissUntil = new Date();
                     dismissUntil.setDate(dismissUntil.getDate() + 7);
                     sessionStorage.setItem('dashboardGoalNotificationDismissedUntil', dismissUntil.getTime().toString());
                     this.show = false;
                 },
                 navigate() {
                     if (window.kidSwitchTab) { kidSwitchTab('goals'); } else { window.location.href = '{{ route('kid.goals.index') }}'; }
                 }
             }"
             @click="navigate()"
             x-show="show"
             x-transition>
            <div class="kid-goals-notification-content">
                <div class="kid-goals-notification-icon">🎉</div>
                <div class="kid-goals-notification-text">
                    You reached your goal! Click here to redeem!
                </div>
            </div>
            <button class="kid-goals-notification-close"
                    @click.stop="dismiss()"
                    aria-label="Dismiss notification">
                ✕
            </button>
        </div>
    @endif

    <!-- Kid Header -->
    <div class="kid-overview-header">
        <div class="kid-header-left">
            <div class="kid-avatar" style="background: {{ $kid->color }};">{{ strtoupper(substr($kid->name, 0, 1)) }}</div>
            <div class="kid-info">
                <h2 class="kid-name">{{ $kid->name }}</h2>
                <div class="kid-age">Age {{ \Carbon\Carbon::parse($kid->birthday)->age }}</div>
            </div>
        </div>

        <div class="kid-header-stats">
            <div class="kid-header-stat-card" style="border-top: 3px solid {{ $kid->color }};">
                <div class="kid-header-stat-label"><i class="fas fa-wallet"></i> Balance</div>
                <div class="kid-header-stat-value" style="color: {{ $kid->color }};">
                    <span id="kidBalanceBadge">${{ number_format($kid->balance, 2) }}</span>
                </div>
            </div>
            @if($kid->points_enabled)
                <div class="kid-header-stat-card" style="border-top: 3px solid {{ $kid->color }};">
                    <div class="kid-header-stat-label"><i class="fas fa-star"></i> Points</div>
                    <div class="kid-header-stat-value" style="color: {{ $kid->color }};">
                        <span id="kidPointsDisplay">{{ $kid->points }}</span><span style="font-size: 14px; font-weight: 500; color: #9ca3af;"> / {{ $kid->max_points }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Dashboard Navigation Tabs -->
    <div class="kid-dashboard-tabs">
        <button class="kid-dashboard-tab active" onclick="kidSwitchTab('overview')">
            <i class="fas fa-th-large"></i> Overview
        </button>
        @php
            $pendingGoalsCount = $kid->getPendingRedemptionGoalsCount();
            $readyGoalsCount = $kid->getReadyToRedeemGoalsCount();
        @endphp
        <button class="kid-dashboard-tab" onclick="kidSwitchTab('goals')">
            <i class="fas fa-bullseye"></i> Goals
            @if($readyGoalsCount > 0)
                <span class="kid-tab-ready-badge"><i class="fas fa-check-circle"></i> {{ $readyGoalsCount }}</span>
            @elseif($pendingGoalsCount > 0)
                <span class="kid-tab-pending-badge">{{ $pendingGoalsCount }}</span>
            @endif
        </button>
        <button class="kid-dashboard-tab" onclick="kidSwitchTab('wishes')">
            <i class="fas fa-heart"></i> Wishes
        </button>
        <button class="kid-dashboard-tab" onclick="kidSwitchTab('activity')">
            <i class="fas fa-history"></i> Activity
        </button>
        <button class="kid-dashboard-tab coming-soon" onclick="kidShowToast('Chores are coming soon! We\'re working on this feature.')">
            <i class="fas fa-tasks"></i> Chores <span class="kid-tab-badge">Soon</span>
        </button>
    </div>

    @php
        $daysOfWeek = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
        $targetDay = $daysOfWeek[$kid->allowance_day] ?? 5;
        $today = now();
        $daysUntil = ($targetDay - $today->dayOfWeek + 7) % 7;
        if ($daysUntil === 0) $daysUntil = 7;
        $nextAllowance = $today->copy()->addDays($daysUntil);
    @endphp

    <!-- ===== OVERVIEW TAB ===== -->
    <div class="kid-tab-panel" id="kid-tab-overview">
        <div class="kid-overview-grid">

            <!-- Current Balance Card -->
            <div class="kid-overview-card" id="kidBalanceCard">
                <div class="kid-card-header">
                    <h3><i class="fas fa-wallet"></i> Current Balance</h3>
                </div>
                <div class="kid-card-body">
                    <div class="kid-balance-amount" id="kidBalance">${{ number_format($kid->balance, 2) }}</div>
                    <div class="kid-balance-actions">
                        <button class="kid-btn kid-btn-deposit" id="kidDepositToggle" onclick="kidToggleInlineForm('deposit')">
                            <i class="fas fa-plus"></i> Add Money
                        </button>
                        <button class="kid-btn kid-btn-spend" id="kidSpendToggle" onclick="kidToggleInlineForm('spend')">
                            <i class="fas fa-minus"></i> Spend
                        </button>
                    </div>
                    <!-- Inline Deposit Form -->
                    <div id="kidDepositInline" style="display:none; margin-top:16px; padding-top:16px; border-top:1px solid #e5e7eb;">
                        <form onsubmit="kidSubmitDeposit(event)" class="kid-inline-form">
                            <input type="text" id="kidDepositNote" class="kid-inline-form-note" placeholder="What's this for?" autocomplete="off">
                            <div id="kidDepositNoteError" class="kid-error-message">Please add a note</div>
                            <div class="kid-inline-form-row">
                                <input type="text" id="kidDepositAmount" class="kid-form-input" placeholder="$0.00" oninput="kidFormatCurrency(this)" autocomplete="off">
                                <button type="submit" class="kid-inline-form-row kid-btn-submit-inline" style="background: {{ $kid->color }};">+ Record</button>
                                <button type="button" class="kid-inline-cancel-btn" onclick="kidToggleInlineForm(null)" title="Cancel">✕</button>
                            </div>
                            <div id="kidDepositAmountError" class="kid-error-message">Please enter an amount</div>
                        </form>
                    </div>
                    <!-- Inline Spend Form -->
                    <div id="kidSpendInline" style="display:none; margin-top:16px; padding-top:16px; border-top:1px solid #e5e7eb;">
                        <form onsubmit="kidSubmitSpend(event)" class="kid-inline-form">
                            <input type="text" id="kidSpendNote" class="kid-inline-form-note" placeholder="What did you buy?" autocomplete="off">
                            <div id="kidSpendNoteError" class="kid-error-message">Please add a note</div>
                            <div class="kid-inline-form-row">
                                <input type="text" id="kidSpendAmount" class="kid-form-input" placeholder="$0.00" oninput="kidFormatCurrency(this)" autocomplete="off">
                                <button type="submit" class="kid-inline-form-row kid-btn-submit-inline" style="background: #ef4444;">− Record</button>
                                <button type="button" class="kid-inline-cancel-btn" onclick="kidToggleInlineForm(null)" title="Cancel">✕</button>
                            </div>
                            <div id="kidSpendAmountError" class="kid-error-message">Please enter an amount</div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Allowance Card -->
            <div class="kid-overview-card">
                <div class="kid-card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Allowance</h3>
                </div>
                <div class="kid-card-body">
                    <div class="kid-allowance-amount">${{ number_format($kid->allowance_amount, 2) }}</div>
                    <div class="kid-allowance-schedule">Every {{ ucfirst($kid->allowance_day) }}</div>
                    <div class="kid-next-allowance">
                        @if($kid->points_enabled && $kid->points === 0)
                            <span style="color: #ef4444; font-weight: 600;">⚠️ No allowance - 0 points</span>
                        @else
                            Next: {{ $nextAllowance->format('l, M j') }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Active Goals Card (preview) -->
            <div class="kid-overview-card">
                <div class="kid-card-header">
                    <h3><i class="fas fa-bullseye"></i> Active Goals @if($allActiveGoalsCount > 0)<span style="font-size: 12px; font-weight: 600; color: #9ca3af; background: #f3f4f6; border-radius: 10px; padding: 2px 8px; margin-left: 4px;">{{ $allActiveGoalsCount }}</span>@endif</h3>
                    <button class="kid-view-all" onclick="kidSwitchTab('goals')">View All</button>
                </div>
                <div class="kid-card-body">
                    @if($previewGoals->count() > 0)
                        <div class="kid-goals-list">
                            @foreach($previewGoals as $goal)
                                @php
                                    $progressPercent = $goal->target_amount > 0 ? min(100, ($goal->current_amount / $goal->target_amount) * 100) : 0;
                                    $isGoalComplete = $progressPercent >= 100 || $goal->status === 'ready_to_redeem' || $goal->status === 'pending_redemption';
                                    $barColor = $isGoalComplete ? '#10b981' : $kid->color;
                                @endphp
                                <div class="kid-goal-item {{ $isGoalComplete ? 'kid-goal-item-complete' : '' }}">
                                    {{-- Thumbnail --}}
                                    <div class="kid-goal-item-thumb">
                                        @if($goal->photo_path)
                                            <img src="{{ asset('storage/' . $goal->photo_path) }}" alt="{{ $goal->title }}">
                                        @else
                                            <div class="kid-goal-item-thumb-placeholder">
                                                <i class="fas {{ $isGoalComplete ? 'fa-check-circle' : 'fa-bullseye' }}" style="color: {{ $barColor }};"></i>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Info --}}
                                    <div class="kid-goal-item-info">
                                        <div class="kid-goal-title">{{ Str::limit($goal->title, 45) }}</div>
                                        <div class="kid-goal-progress">
                                            <div class="kid-progress-bar">
                                                <div class="kid-progress-fill" style="width: {{ $progressPercent }}%; background: {{ $barColor }};"></div>
                                            </div>
                                            <span class="kid-goal-amount">${{ number_format($goal->current_amount, 2) }} / ${{ number_format($goal->target_amount, 2) }}</span>
                                        </div>
                                        <div class="kid-goal-item-actions">
                                            @if($goal->denied_at && $goal->denial_reason)
                                                <span class="kid-goal-item-denied-badge">
                                                    <i class="fas fa-ban"></i> Denied
                                                </span>
                                            @elseif($isGoalComplete)
                                                <span class="kid-goal-item-complete-badge">
                                                    <i class="fas fa-check-circle"></i> Complete!
                                                </span>
                                            @else
                                                <button onclick="kidSwitchTab('goals')" class="kid-goal-item-add-btn" style="background: {{ $kid->color }};">
                                                    <i class="fas fa-plus"></i> Add Funds
                                                </button>
                                            @endif
                                            <a href="{{ route('kid.goals.show', $goal) }}" class="kid-goal-item-view-btn">
                                                <i class="fas fa-eye"></i> View Goal
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="kid-empty-state">
                            <p>No active goals yet!</p>
                            <button class="kid-btn-create" onclick="kidSwitchTab('goals'); setTimeout(() => kidOpenCreateGoalModal(), 300);">Create Your First Goal</button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Wish List Card (preview) -->
            <div class="kid-overview-card">
                <div class="kid-card-header">
                    <h3><i class="fas fa-heart"></i> Wish List</h3>
                    <button class="kid-view-all" onclick="kidSwitchTab('wishes')">View All</button>
                </div>
                <div class="kid-card-body">
                    @if($previewWishes->count() > 0)
                        <div class="kid-wishes-list">
                            @foreach($previewWishes as $wish)
                                <div class="kid-wish-item">
                                    {{-- Thumbnail --}}
                                    <div class="kid-wish-item-thumb">
                                        @if($wish->image_path)
                                            <img src="{{ asset('storage/' . $wish->image_path) }}" alt="{{ $wish->item_name }}">
                                        @else
                                            <div class="kid-wish-item-thumb-placeholder">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Info --}}
                                    <div class="kid-wish-item-info">
                                        <div class="kid-wish-title">{{ Str::limit($wish->item_name, 45) }}</div>
                                        <div class="kid-wish-price">${{ number_format($wish->price, 2) }}</div>
                                        @if($wish->isPendingApproval())
                                            <div class="kid-wish-item-pending"><i class="fas fa-clock"></i> Waiting for parent</div>
                                        @elseif($wish->isDeclined())
                                            <div class="kid-wish-item-declined"><i class="fas fa-times-circle"></i> Declined</div>
                                        @endif
                                        <div class="kid-wish-item-actions">
                                            @if($wish->isSaved() && $wish->canBeRequested())
                                                <button class="kid-wish-item-btn kid-wish-item-btn-ask"
                                                        style="background: {{ $kid->color }};"
                                                        onclick="kidSwitchTab('wishes'); setTimeout(() => kidWishAskToBuy({{ $wish->id }}, '{{ addslashes($wish->item_name) }}', {{ $wish->price }}), 600);">
                                                    <i class="fas fa-paper-plane"></i> Ask Parent
                                                </button>
                                            @endif
                                            <a href="{{ route('kid.wishes.show', $wish) }}" class="kid-wish-item-btn kid-wish-item-btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{-- Create Wish CTA --}}
                        <div style="text-align: center; margin-top: 16px;">
                            <button class="kid-wish-create-btn" style="border-color: {{ $kid->color }}; color: {{ $kid->color }};"
                                    onclick="kidSwitchTab('wishes'); setTimeout(() => kidOpenCreateWishModal(), 400);">
                                <i class="fas fa-plus"></i> Create a Wish
                            </button>
                        </div>
                    @else
                        <div class="kid-empty-state">
                            <p>No wishes yet!</p>
                            <button class="kid-btn-create" onclick="kidSwitchTab('wishes'); setTimeout(() => kidOpenCreateWishModal(), 400);">Add Your First Wish</button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity Card (preview) -->
            <div class="kid-overview-card">
                <div class="kid-card-header">
                    <h3><i class="fas fa-history"></i> Recent Activity</h3>
                    <button class="kid-view-all" onclick="kidSwitchTab('activity')">View All</button>
                </div>
                <div class="kid-card-body">
                    @if($transactions->count() > 0)
                        <div class="kid-activity-list">
                            @foreach($transactions->take(5) as $transaction)
                                <div class="kid-activity-item">
                                    <div class="kid-activity-icon {{ $transaction['type'] }}">
                                        @if($transaction['type'] === 'deposit') <i class="fas fa-plus"></i>
                                        @elseif($transaction['type'] === 'withdrawal') <i class="fas fa-minus"></i>
                                        @else <i class="fas fa-star"></i>
                                        @endif
                                    </div>
                                    <div class="kid-activity-details">
                                        <div class="kid-activity-note">{{ $transaction['note'] }}</div>
                                        <div class="kid-activity-date">{{ \Carbon\Carbon::createFromTimestamp($transaction['timestamp'])->diffForHumans() }}</div>
                                    </div>
                                    <div class="kid-activity-amount {{ $transaction['type'] }}">
                                        @if($transaction['type'] === 'deposit') +${{ number_format($transaction['amount'], 2) }}
                                        @elseif($transaction['type'] === 'withdrawal') -${{ number_format(abs($transaction['amount']), 2) }}
                                        @else {{ $transaction['amount'] > 0 ? '+' : '' }}{{ $transaction['amount'] }} pts
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="kid-empty-state"><p>No activity yet!</p></div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- ===== GOALS TAB (lazy loaded) ===== -->
    <div class="kid-tab-panel" id="kid-tab-goals" style="display: none;"></div>

    <!-- ===== WISHES TAB (lazy loaded) ===== -->
    <div class="kid-tab-panel" id="kid-tab-wishes" style="display: none;"></div>

    <!-- ===== ACTIVITY TAB (lazy loaded) ===== -->
    <div class="kid-tab-panel" id="kid-tab-activity" style="display: none;"></div>

    {{-- Deposit/Spend forms are now inline in the balance card above --}}

    <!-- Ledger Modal -->
    <div class="kid-ledger-modal" id="kidLedgerSection">
        <div class="kid-modal-backdrop" onclick="kidToggleLedger()"></div>
        <div class="kid-modal-content kid-modal-large">
            <div class="kid-modal-header">
                <h3>All Transactions</h3>
                <button class="kid-modal-close" onclick="kidToggleLedger()">×</button>
            </div>
            <div class="kid-ledger-filters">
                <div class="kid-filter-buttons">
                    <button class="kid-filter-btn active" onclick="kidFilterLedger('all')">All</button>
                    <button class="kid-filter-btn" onclick="kidFilterLedger('deposit')">Deposits</button>
                    <button class="kid-filter-btn" onclick="kidFilterLedger('spend')">Spends</button>
                    @if($kid->points_enabled)
                        <button class="kid-filter-btn" onclick="kidFilterLedger('points')">Points</button>
                    @endif
                </div>
            </div>
            <div class="kid-ledger-table" id="kidLedgerTable">
                <!-- Transactions will be rendered here by JavaScript -->
            </div>
        </div>
    </div>



    <script>
        // Initialize kid data from Laravel - SET BEFORE JS MODULE LOADS
        window.kidBalance = {{ $kid->balance }};
        @if($kid->points_enabled)
            window.kidPoints = {{ $kid->points }};
        @endif
        window.kidLedgerData = @json($transactions);

        // Wait for DOMContentLoaded to ensure functions are loaded
        document.addEventListener('DOMContentLoaded', function () {
            // Force update balance display on load
            if (window.kidBalance !== undefined) {
                kidBalance = window.kidBalance;
                const balanceEl = document.getElementById('kidBalance');
                if (balanceEl && kidBalance !== 0) {
                    balanceEl.textContent = '$' + kidBalance.toFixed(2);
                }
            }

            // Load ledger data
            if (window.kidLedgerData !== undefined) {
                kidLedgerData = window.kidLedgerData;
            }

            if (typeof window.kidUpdatePointsDisplay === 'function') {
                window.kidUpdatePointsDisplay();
            }
            if (typeof window.kidRenderLedger === 'function') {
                window.kidRenderLedger();
            }

            @if(session('active_tab'))
            if (typeof window.kidSwitchTab === 'function') {
                window.kidSwitchTab('{{ session('active_tab') }}');
            }
            @endif

            // Handle ?tab= query param (e.g. from back links on detail pages)
            // NOTE: The main DOMContentLoaded in kid-dashboard.js also handles this - this is a fallback only
            // Do NOT strip params here; let kid-dashboard.js handle it with full options support
        });
    </script>

    <!-- Transaction Modal -->
    <div class="kid-transaction-modal" id="kidTransactionModal">
        <div class="kid-modal-backdrop" onclick="kidCloseTransactionModal()"></div>
        <div class="kid-modal-content">
            <div class="kid-modal-header">
                <h2>All Transactions</h2>
                <button class="kid-modal-close" onclick="kidCloseTransactionModal()">×</button>
            </div>

            <div class="kid-modal-filters">
                <div class="kid-modal-filter-tabs">
                    <button class="kid-modal-filter-btn active" onclick="kidModalFilter('all')">All</button>
                    <button class="kid-modal-filter-btn" onclick="kidModalFilter('deposit')">Deposits</button>
                    <button class="kid-modal-filter-btn" onclick="kidModalFilter('spend')">Spends</button>
                    <button class="kid-modal-filter-btn" onclick="kidModalFilter('points')">Points</button>
                </div>
                <select class="kid-modal-time-filter" id="kidModalTimeFilter" onchange="kidModalTimeFilter()">
                    <option value="all">All Time</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>

            <div class="kid-modal-body" id="kidModalBody">
                <!-- Transactions will be rendered here -->
            </div>
        </div>
    </div>

    <!-- Alpine.js for notification banner -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@endsection