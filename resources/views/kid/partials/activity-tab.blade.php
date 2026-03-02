{{-- Activity Tab - Full transaction history --}}
<div class="kid-activity-tab-container">

    {{-- Filters --}}
    <div class="kid-activity-tab-filters">
        <div class="kid-activity-filter-group">
            <button class="kid-activity-filter-btn active" onclick="kidActivityTabFilter('all')">All</button>
            <button class="kid-activity-filter-btn" onclick="kidActivityTabFilter('deposit')">Deposits</button>
            <button class="kid-activity-filter-btn" onclick="kidActivityTabFilter('spend')">Spends</button>
            @if($kid->points_enabled)
                <button class="kid-activity-filter-btn" onclick="kidActivityTabFilter('points')">Points</button>
            @endif
        </div>
        <div class="kid-activity-right-controls">
            <span class="kid-activity-p-legend"><span class="kid-parent-badge">P</span> = Parent</span>
            <select id="kidActivityTimeFilter" class="kid-activity-time-select" onchange="kidActivityTabTimeFilter()">
                <option value="all">All Time</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>
        </div>
    </div>

    {{-- Count summary bar --}}
    <div id="kidActivityCountBar" class="kid-activity-count-bar"></div>

    {{-- Transaction List (rendered by JS) --}}
    <div id="kidActivityTabList" class="kid-activity-full-list">
        {{-- Populated by kidRenderActivityTab() --}}
        <div class="kid-empty-state"><p>Loading activity...</p></div>
    </div>

    {{-- Pagination (rendered by JS) --}}
    <div id="kidActivityPagination"></div>

</div>

<style>
.kid-activity-tab-container {
    max-width: 100%;
}
.kid-activity-tab-filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.kid-activity-filter-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.kid-activity-filter-btn {
    padding: 7px 14px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}
.kid-activity-filter-btn:hover {
    border-color: {{ $kid->color }};
    color: {{ $kid->color }};
}
.kid-activity-filter-btn.active {
    background: {{ $kid->color }};
    border-color: {{ $kid->color }};
    color: white;
}
.kid-activity-right-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}
.kid-activity-time-select {
    padding: 7px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    color: #374151;
    background: white;
    cursor: pointer;
}
.kid-activity-p-legend {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #9ca3af;
    white-space: nowrap;
}
.kid-activity-count-bar {
    font-size: 12px;
    color: #9ca3af;
    margin-bottom: 12px;
    min-height: 16px;
}
.kid-activity-full-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.kid-parent-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    font-size: 11px;
    font-weight: 900;
    color: {{ $kid->color }};
    background: {{ $kid->color }}20;
    border-radius: 50%;
    vertical-align: middle;
}
</style>
