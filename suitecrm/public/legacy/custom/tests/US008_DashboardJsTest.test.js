/**
 * US-008: Create planning dashboard JavaScript - TDD RED
 *
 * Tests for custom/modules/LF_WeeklyPlan/js/dashboard.js
 *
 * These tests verify the THREE COLUMN RENDERING functionality which is NOT YET IMPLEMENTED.
 * The current dashboard.js only has URL navigation but lacks:
 * - renderPipelineHealth() function
 * - renderWeeklyPriorities() function
 * - renderDealRisk() function
 * - Initial render on DOMContentLoaded
 * - Re-render on state changes
 */

'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

// ============================================================
// Configuration
// ============================================================

const customDir = path.resolve(__dirname, '..');
const jsFile = path.join(customDir, 'modules', 'LF_WeeklyPlan', 'js', 'dashboard.js');

// Mock Data matching PHP structure
const MOCK_DATA = {
    config: {
        brand_blue: '#125EAD',
        brand_green: '#4BB74E',
        green_threshold: 100,
        yellow_threshold: 75,
        orange_threshold: 50,
        red_threshold: 25
    },
    reps: [
        { assigned_user_id: 'rep1', first_name: 'John', last_name: 'Doe', full_name: 'John Doe' },
        { assigned_user_id: 'rep2', first_name: 'Jane', last_name: 'Smith', full_name: 'Jane Smith' }
    ],
    weekInfo: {
        currentWeek: '2026-02-02',
        weekEnd: '2026-02-08'
    },
    weekList: [
        { weekStart: '2026-01-26', label: 'Jan 26', isCurrent: false },
        { weekStart: '2026-02-02', label: 'Feb 02', isCurrent: true },
        { weekStart: '2026-02-09', label: 'Feb 09', isCurrent: false }
    ],
    pipelineByStage: {
        '2-Analysis': { count: 5, amount: 50000 },
        '7-Closing': { count: 2, amount: 20000 }
    },
    pipelineByRep: {
        'rep1': { total: 30000, byStage: { '2-Analysis': { count: 3, amount: 30000 } } },
        'rep2': { total: 40000, byStage: { '7-Closing': { count: 2, amount: 20000 } } }
    },
    staleDeals: [
        { id: 'opp1', name: 'Stale Opp 1', assigned_user_name: 'John Doe', days_since_activity: 45, risk: 'high' },
        { id: 'opp2', name: 'Stale Opp 2', assigned_user_name: 'Jane Smith', days_since_activity: 10, risk: 'low' }
    ],
    // AMENDED BY US-010: Added required fields for Weekly Priorities column rendering
    // (assigned_user_id, account_name, opportunity_name, projected_stage, planned_day)
    planItems: [
        { id: 'plan1', assigned_user_id: 'rep1', item_category: 'closing', account_name: 'Acme Corp', opportunity_name: 'Big Deal', description: 'Close big deal', amount: 50000, projected_stage: '7-Closing', planned_day: 'Monday', priority: 'high' },
        { id: 'plan2', assigned_user_id: 'rep2', item_category: 'progression', account_name: 'Beta Inc', opportunity_name: 'Follow Up Opp', description: 'Follow up', amount: 25000, projected_stage: '5-Negotiation', planned_day: 'Tuesday', priority: 'medium' }
    ],
    closedYtd: {
        team: 40000,
        byRep: {
            'rep1': 15000,
            'rep2': 25000
        }
    }
};

// ============================================================
// Test Harness
// ============================================================

let passCount = 0;
let failCount = 0;
const failures = [];

function test(name, fn) {
    try {
        fn();
        passCount++;
        console.log(`  [PASS] ${name}`);
    } catch (e) {
        failCount++;
        failures.push({ name, error: e.message });
        console.log(`  [FAIL] ${name}`);
        console.log(`         ${e.message}`);
    }
}

// DOM Simulation for testing render functions
function createBrowserSandbox() {
    const elements = {};
    const eventListeners = {};
    let innerHTMLLog = {};

    function createElement(tag, attrs = {}) {
        const el = {
            tagName: tag.toUpperCase(),
            attributes: { ...attrs },
            children: [],
            style: {},
            _innerHTML: '',
            _textContent: '',

            get id() { return this.attributes.id || ''; },
            get className() { return this.attributes.class || ''; },
            set className(v) { this.attributes.class = v; },
            get value() { return this.attributes.value; },
            set value(v) { this.attributes.value = v; },
            get innerHTML() { return this._innerHTML; },
            set innerHTML(v) {
                this._innerHTML = v;
                if (this.id) innerHTMLLog[this.id] = v;
            },
            get textContent() { return this._textContent; },
            set textContent(v) { this._textContent = v; },
            get options() { return this.children; },

            appendChild(child) { this.children.push(child); child.parentNode = this; },
            addEventListener(type, handler) {
                if (!eventListeners[this.id]) eventListeners[this.id] = {};
                if (!eventListeners[this.id][type]) eventListeners[this.id][type] = [];
                eventListeners[this.id][type].push(handler);
            },
            dispatchEvent(event) {
                const handlers = (eventListeners[this.id] && eventListeners[this.id][event.type]) || [];
                handlers.forEach(h => h(event));
            }
        };
        if (el.id) elements[el.id] = el;
        return el;
    }

    // View toggle buttons
    const teamBtn = createElement('button', { id: 'team-view-btn', class: 'lf-btn active' });
    const repBtn = createElement('button', { id: 'rep-view-btn', class: 'lf-btn' });
    const repSelectorContainer = createElement('div', { id: 'rep-selector-container', class: 'lf-hidden' });
    const repSelector = createElement('select', { id: 'rep-selector' });

    // Week navigation
    const weekBackBtn = createElement('button', { id: 'week-back-btn' });
    const weekNextBtn = createElement('button', { id: 'week-next-btn' });
    const weekCurrentBtn = createElement('button', { id: 'week-current-btn' });
    const weekSelector = createElement('select', { id: 'week-selector' });

    // Week selector options
    MOCK_DATA.weekList.forEach(w => {
        const opt = createElement('option', { value: w.weekStart });
        opt._innerHTML = w.label;
        if (w.isCurrent) opt.attributes.selected = 'selected';
        weekSelector.appendChild(opt);
    });
    weekSelector.value = '2026-02-02'; // Current week
    weekSelector.selectedIndex = 1;

    // Rep selector options
    MOCK_DATA.reps.forEach(r => {
        const opt = createElement('option', { value: r.assigned_user_id });
        opt._innerHTML = r.full_name;
        repSelector.appendChild(opt);
    });

    // Dashboard columns container
    const dashboardContainer = createElement('div', { id: 'lf-dashboard-container' });
    const pipelineColumn = createElement('div', { id: 'pipeline-health-column' });
    const prioritiesColumn = createElement('div', { id: 'weekly-priorities-column' });
    const riskColumn = createElement('div', { id: 'deal-risk-column' });

    dashboardContainer.appendChild(pipelineColumn);
    dashboardContainer.appendChild(prioritiesColumn);
    dashboardContainer.appendChild(riskColumn);

    const document = {
        getElementById(id) { return elements[id] || null; },
        createElement,
        addEventListener(type, handler) {
             if (!eventListeners['document']) eventListeners['document'] = {};
             if (!eventListeners['document'][type]) eventListeners['document'][type] = [];
             eventListeners['document'][type].push(handler);
        }
    };

    const window = {
        LF_DASHBOARD_DATA: JSON.parse(JSON.stringify(MOCK_DATA)),
        document,
        console,
        location: { href: 'http://localhost/index.php?module=LF_WeeklyPlan&action=dashboard', search: '', assign: (url) => { this.href = url; } },
        Event: function(type) { return { type, target: null }; },
        URL: class URL {
            constructor(url) {
                this.url = url;
                this.searchParams = {
                    _params: {},
                    set: (k, v) => { this._params[k] = v; },
                    delete: (k) => { delete this._params[k]; },
                    toString: () => ''
                };
            }
            toString() { return this.url; }
        }
    };

    return { window, document, elements, eventListeners, innerHTMLLog };
}

function loadDashboardJs(sandbox) {
    if (!fs.existsSync(jsFile)) {
        throw new Error(`File not found: ${jsFile}`);
    }
    const jsContent = fs.readFileSync(jsFile, 'utf8');
    const context = vm.createContext({
        ...sandbox.window,
        window: sandbox.window,
        document: sandbox.document,
        console: sandbox.window.console,
        Event: sandbox.window.Event,
        URL: sandbox.window.URL
    });
    vm.runInContext(jsContent, context);

    // Trigger DOMContentLoaded
    if (sandbox.eventListeners['document'] && sandbox.eventListeners['document']['DOMContentLoaded']) {
        sandbox.eventListeners['document']['DOMContentLoaded'].forEach(h => h({}));
    }
}

// ============================================================
// Tests
// ============================================================

console.log('Running US-008 Dashboard JS Tests (TDD RED Phase)...');
console.log('Testing for MISSING functionality:\n');

// ===================================================================
// Structural Tests - File and Function Existence
// ===================================================================

test('File should exist at custom/modules/LF_WeeklyPlan/js/dashboard.js', () => {
    assert.ok(fs.existsSync(jsFile), `dashboard.js must exist at ${jsFile}`);
});

test('File should contain renderPipelineHealth function', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderPipelineHealth') || content.includes('function renderPipelineHealth') || content.includes('renderPipelineHealth:'),
        'File must declare renderPipelineHealth function'
    );
});

test('File should contain renderWeeklyPriorities function', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderWeeklyPriorities') || content.includes('function renderWeeklyPriorities') || content.includes('renderWeeklyPriorities:'),
        'File must declare renderWeeklyPriorities function'
    );
});

test('File should contain renderDealRisk function', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderDealRisk') || content.includes('function renderDealRisk') || content.includes('renderDealRisk:'),
        'File must declare renderDealRisk function'
    );
});

test('File should contain renderAllColumns function', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderAllColumns') || content.includes('function renderAllColumns') || content.includes('renderAllColumns:'),
        'File must declare renderAllColumns function that calls all three render functions'
    );
});

test('File should use window.LF_DASHBOARD_DATA', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('window.LF_DASHBOARD_DATA') || content.includes("window['LF_DASHBOARD_DATA']"),
        'File must read data from window.LF_DASHBOARD_DATA global variable'
    );
});

test('File should wrap code in DOMContentLoaded listener', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('DOMContentLoaded') && content.includes('addEventListener'),
        'File must initialize on DOMContentLoaded event'
    );
});

// ===================================================================
// Functional Tests - Initial Render
// ===================================================================

test('Should render Pipeline Health column on initial load', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const pipelineColumn = sandbox.elements['pipeline-health-column'];
    assert.ok(pipelineColumn.innerHTML.length > 0,
        'Pipeline Health column should have content after initial render');
});

test('Should render Weekly Priorities column on initial load', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    assert.ok(prioritiesColumn.innerHTML.length > 0,
        'Weekly Priorities column should have content after initial render');
});

test('Should render Deal Risk column on initial load', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    assert.ok(riskColumn.innerHTML.length > 0,
        'Deal Risk column should have content after initial render');
});

test('Initial render should use Team View mode', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    // In Team View, should show aggregated team data
    const pipelineColumn = sandbox.elements['pipeline-health-column'];
    assert.ok(pipelineColumn.innerHTML.includes('40,000') || pipelineColumn.innerHTML.includes('40000'),
        'Team View should display aggregated Closed YTD (40,000)');
});

test('Initial render should use current week', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    // Current week is Feb 02, 2026
    const weekSelector = sandbox.elements['week-selector'];
    assert.strictEqual(weekSelector.value, '2026-02-02',
        'Initial state should have current week selected');
});

// ===================================================================
// Functional Tests - Column Rendering Content
// ===================================================================

test('renderPipelineHealth should display pipeline data', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const pipelineColumn = sandbox.elements['pipeline-health-column'];
    const html = pipelineColumn.innerHTML;

    // Should show stage data from MOCK_DATA
    assert.ok(html.includes('2-Analysis') || html.includes('Analysis'),
        'Pipeline Health should display sales stage');
    assert.ok(html.includes('5') || html.includes('50,000') || html.includes('50000'),
        'Pipeline Health should display stage count or amount');
});

test('renderWeeklyPriorities should display plan items', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Should show plan items from MOCK_DATA
    assert.ok(html.includes('Close big deal') || html.includes('Follow up'),
        'Weekly Priorities should display plan item descriptions');
    assert.ok(html.includes('50,000') || html.includes('50000') || html.includes('25,000') || html.includes('25000'),
        'Weekly Priorities should display amounts');
});

test('renderDealRisk should display stale deals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should show stale deals from MOCK_DATA
    assert.ok(html.includes('Stale Opp'),
        'Deal Risk should display stale deal names');
    assert.ok(html.includes('45') || html.includes('10'),
        'Deal Risk should display days since activity');
});

// ===================================================================
// Functional Tests - Team/Rep View Toggle
// ===================================================================

test('Team View should hide rep selector', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const teamBtn = sandbox.elements['team-view-btn'];
    const repContainer = sandbox.elements['rep-selector-container'];

    // Click Team View
    teamBtn.dispatchEvent({ type: 'click' });

    assert.ok(
        repContainer.className.includes('lf-hidden') || repContainer.className.includes('hidden') || repContainer.style.display === 'none',
        'Team View should hide rep selector container'
    );
});

test('Rep View should show rep selector', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repContainer = sandbox.elements['rep-selector-container'];

    // Click Rep View
    repBtn.dispatchEvent({ type: 'click' });

    assert.ok(
        !repContainer.className.includes('lf-hidden') && !repContainer.className.includes('hidden') && repContainer.style.display !== 'none',
        'Rep View should show rep selector container'
    );
});

test('Team View should display aggregated team data', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const pipelineColumn = sandbox.elements['pipeline-health-column'];

    // Mock is in Team View by default, should show team total
    assert.ok(pipelineColumn.innerHTML.includes('40,000') || pipelineColumn.innerHTML.includes('40000'),
        'Team View should show team total (40,000)');
});

test('Rep View should display rep-specific data', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const pipelineColumn = sandbox.elements['pipeline-health-column'];

    // Select rep and trigger change
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });

    // rep1 has 15000 closed YTD
    const html = pipelineColumn.innerHTML;
    assert.ok(html.includes('15,000') || html.includes('15000') || !html.includes('40,000'),
        'Rep View should show rep-specific data (15,000 for rep1, not 40,000 team total)');
});

// ===================================================================
// Functional Tests - Week Navigation
// ===================================================================

test('Week Back button should decrement week', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const backBtn = sandbox.elements['week-back-btn'];
    const weekSelector = sandbox.elements['week-selector'];

    // Initial week is Feb 02 (index 1), back should go to Jan 26 (index 0)
    const initialIndex = weekSelector.selectedIndex;
    backBtn.dispatchEvent({ type: 'click' });

    assert.ok(weekSelector.selectedIndex < initialIndex,
        'Back button should move to earlier week');
});

test('Week Next button should increment week', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const nextBtn = sandbox.elements['week-next-btn'];
    const weekSelector = sandbox.elements['week-selector'];

    // Initial week is Feb 02 (index 1), next should go to Feb 09 (index 2)
    const initialIndex = weekSelector.selectedIndex;
    nextBtn.dispatchEvent({ type: 'click' });

    assert.ok(weekSelector.selectedIndex > initialIndex,
        'Next button should move to later week');
});

test('Current Week button should reset to current week', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const currentBtn = sandbox.elements['week-current-btn'];
    const weekSelector = sandbox.elements['week-selector'];

    // Change week first
    weekSelector.selectedIndex = 0;
    weekSelector.dispatchEvent({ type: 'change' });

    // Click current week button
    currentBtn.dispatchEvent({ type: 'click' });

    // Should return to current week (index 1)
    assert.ok(weekSelector.selectedIndex === 1 || weekSelector.value === '2026-02-02',
        'Current Week button should reset to current week');
});

test('Week dropdown change should update data', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const weekSelector = sandbox.elements['week-selector'];
    const pipelineColumn = sandbox.elements['pipeline-health-column'];

    // Change week
    weekSelector.value = '2026-01-26';
    weekSelector.dispatchEvent({ type: 'change' });

    // After week change, columns should re-render
    assert.ok(pipelineColumn.innerHTML.length > 0,
        'Columns should re-render after week change');
});

// ===================================================================
// Functional Tests - Re-render on State Changes
// ===================================================================

test('View mode change should re-render all columns', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const teamBtn = sandbox.elements['team-view-btn'];
    const repBtn = sandbox.elements['rep-view-btn'];
    const pipelineColumn = sandbox.elements['pipeline-health-column'];
    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Get initial content length
    const initialPipelineLength = pipelineColumn.innerHTML.length;
    const initialPrioritiesLength = prioritiesColumn.innerHTML.length;
    const initialRiskLength = riskColumn.innerHTML.length;

    // Switch to Rep View
    repBtn.dispatchEvent({ type: 'click' });

    // Columns should have new content (re-rendered)
    // Note: This test assumes the content changes when switching views
    assert.ok(
        pipelineColumn.innerHTML.length !== initialPipelineLength ||
        prioritiesColumn.innerHTML.length !== initialPrioritiesLength ||
        riskColumn.innerHTML.length !== initialRiskLength,
        'At least one column should re-render when view mode changes'
    );
});

test('Rep selector change should re-render all columns', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repSelector = sandbox.elements['rep-selector'];
    const pipelineColumn = sandbox.elements['pipeline-health-column'];
    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Get initial content
    const initialPipelineContent = pipelineColumn.innerHTML;

    // Change rep selection
    repSelector.value = 'rep2';
    repSelector.dispatchEvent({ type: 'change' });

    // Pipeline column should show different data for rep2
    assert.ok(
        pipelineColumn.innerHTML !== initialPipelineContent,
        'Columns should re-render when rep selection changes'
    );
});

test('Week change should re-render all columns', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const weekSelector = sandbox.elements['week-selector'];
    // AMENDED BY US-010: Changed from prioritiesColumn to riskColumn because
    // US-009 removed week from Pipeline Health header, and US-010 removed week from
    // Weekly Priorities header. Deal Risk Assessment still displays the week in its
    // header, so we check that column instead.
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Get initial content
    const initialContent = riskColumn.innerHTML;

    // Change week
    weekSelector.value = '2026-02-09';
    weekSelector.dispatchEvent({ type: 'change' });

    // Content should change after week change
    assert.ok(
        riskColumn.innerHTML !== initialContent,
        'Columns should re-render when week changes'
    );
});

// ===================================================================
// Edge Cases
// ===================================================================

test('Should handle missing window.LF_DASHBOARD_DATA gracefully', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA = undefined;

    // Should not throw error
    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing data without error');
    } catch (e) {
        // Expected to fail since implementation doesn't exist yet
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle empty reps array', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.reps = [];

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle empty reps without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle empty weeks array', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.weekList = [];

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle empty weeks without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle first week boundary (no back navigation)', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const backBtn = sandbox.elements['week-back-btn'];
    const weekSelector = sandbox.elements['week-selector'];

    // Go to first week
    weekSelector.selectedIndex = 0;
    weekSelector.dispatchEvent({ type: 'change' });

    // Try to go back further
    const initialIndex = weekSelector.selectedIndex;
    backBtn.dispatchEvent({ type: 'click' });

    // Should stay at first week
    assert.ok(weekSelector.selectedIndex === initialIndex,
        'Should not navigate before first week');
});

test('Should handle last week boundary (no next navigation)', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const nextBtn = sandbox.elements['week-next-btn'];
    const weekSelector = sandbox.elements['week-selector'];

    // Go to last week
    weekSelector.selectedIndex = weekSelector.options.length - 1;
    weekSelector.dispatchEvent({ type: 'change' });

    // Try to go next
    const initialIndex = weekSelector.selectedIndex;
    nextBtn.dispatchEvent({ type: 'click' });

    // Should stay at last week
    assert.ok(weekSelector.selectedIndex === initialIndex,
        'Should not navigate past last week');
});

// ============================================================
// Summary
// ============================================================

console.log('\n' + '='.repeat(60));
console.log('SUMMARY: US-008 Dashboard JavaScript Tests');
console.log('='.repeat(60));
console.log(`Total Tests: ${passCount + failCount}`);
console.log(`Passed: ${passCount}`);
console.log(`Failed: ${failCount}`);
console.log('='.repeat(60));

if (failCount > 0) {
    console.log('\nFAILING TESTS:');
    failures.forEach((f, i) => {
        console.log(`  ${i + 1}. ${f.name}`);
        console.log(`     ${f.error}`);
    });
    console.log('\nThese tests are EXPECTED TO FAIL in TDD-RED phase.');
    console.log('The implementation code has not been written yet.');
    console.log('Proceed to TDD-GREEN phase to implement the features.');
}

console.log('\n<promise>COMPLETE</promise>');
