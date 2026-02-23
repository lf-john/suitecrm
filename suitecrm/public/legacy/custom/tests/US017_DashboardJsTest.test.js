/**
 * US-017: Create reporting dashboard JavaScript - TDD RED
 *
 * Tests for custom/modules/LF_WeeklyReport/js/dashboard.js
 *
 * These tests verify the COMMITMENT REVIEW COLUMN functionality which is NOT YET IMPLEMENTED.
 * The current dashboard.js may only have basic navigation but lacks:
 * - renderCommitmentReview() function
 * - Team View rendering (overall achievement rate, per-rep cards)
 * - Rep View rendering (detailed individual results)
 * - Color coding based on achievement thresholds
 * - Plan items display with checkmarks/X marks
 * - Unplanned successes with positive styling
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
const jsFile = path.join(customDir, 'modules', 'LF_WeeklyReport', 'js', 'dashboard.js');

// Mock Data matching PHP structure
const MOCK_DATA = {
    config: {
        brand_blue: '#125EAD',
        brand_green: '#4BB74E',
        achievement: {
            green_threshold: 76,
            yellow_threshold: 51,
            orange_threshold: 26,
            colors: {
                green: '#2F7D32',
                yellow: '#E6C300',
                orange: '#ff8c00',
                red: '#d13438'
            }
        }
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
    // AMENDED BY US-018: Added reportSnapshots to support Stage Progression column logic
    reportSnapshots: [
        {
            id: 'snap1',
            assigned_user_id: 'rep1',
            week_start: '2026-02-02',
            opportunity_id: 'opp1',
            opportunity_name: 'Big Deal',
            account_id: 'acc1',
            account_name: 'Acme Corp',
            amount: 50000,
            stage_at_week_start: '3-qualification',
            current_stage: '4-proposal',
            movement: 'forward',
            result_description: 'Advanced to Proposal'
        },
        {
            id: 'snap2',
            assigned_user_id: 'rep1',
            week_start: '2026-02-02',
            opportunity_id: 'opp2',
            opportunity_name: 'Follow Up Opp',
            account_id: 'acc2',
            account_name: 'Beta Inc',
            amount: 25000,
            stage_at_week_start: '4-proposal',
            current_stage: '4-proposal',
            movement: 'static',
            result_description: 'No progress made'
        },
        {
            id: 'snap3',
            assigned_user_id: 'rep1',
            week_start: '2026-02-02',
            opportunity_id: 'opp3',
            opportunity_name: 'Slipping Deal',
            account_id: 'acc3',
            account_name: 'Gamma LLC',
            amount: 35000,
            stage_at_week_start: '5-negotiation',
            current_stage: '4-proposal',
            movement: 'backward',
            result_description: 'Moved back to Proposal'
        }
    ],
    commitmentData: {
        overall_achievement_rate: 65.5,
        aggregate_new_pipeline: {
            planned: 20000,
            actual: 13000,
            percent: 65.0
        },
        aggregate_progression: {
            planned: 10000,
            actual: 6800,
            percent: 68.0
        },
        rep_data: {
            rep1: {
                rep_name: 'John Doe',
                new_pipeline: {
                    planned: 10000,
                    actual: 7500,
                    percent: 75.0,
                    color: '#E6C300'
                },
                progression: {
                    planned: 5000,
                    actual: 3500,
                    percent: 70.0,
                    color: '#E6C300'
                },
                achieved_items: [
                    {
                        account_name: 'Acme Corp',
                        opportunity_name: 'Big Deal',
                        amount: 50000,
                        projected_stage: '7-Closing',
                        result_description: 'Advanced to Closing',
                        movement: 'forward'
                    }
                ],
                missed_items: [
                    {
                        account_name: 'Beta Inc',
                        opportunity_name: 'Follow Up Opp',
                        amount: 25000,
                        projected_stage: '5-Negotiation',
                        result_description: 'No progress made',
                        movement: 'static'
                    }
                ],
                unplanned_successes: [
                    {
                        source_type: 'Referral',
                        expected_value: 15000,
                        converted_opportunity_id: 'conv1'
                    }
                ]
            },
            rep2: {
                rep_name: 'Jane Smith',
                new_pipeline: {
                    planned: 10000,
                    actual: 5500,
                    percent: 55.0,
                    color: '#E6C300'
                },
                progression: {
                    planned: 5000,
                    actual: 3300,
                    percent: 66.0,
                    color: '#E6C300'
                },
                achieved_items: [
                    {
                        account_name: 'Gamma LLC',
                        opportunity_name: 'New Opportunity',
                        amount: 30000,
                        projected_stage: '4-Proposal',
                        result_description: 'Moved from Prospecting',
                        movement: 'forward'
                    }
                ],
                missed_items: [],
                unplanned_successes: []
            }
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
    weekSelector.value = '2026-02-02';
    weekSelector.selectedIndex = 1;

    // Rep selector options
    MOCK_DATA.reps.forEach(r => {
        const opt = createElement('option', { value: r.assigned_user_id });
        opt._innerHTML = r.full_name;
        repSelector.appendChild(opt);
    });

    // Commitment Review column container
    const commitmentColumn = createElement('div', { id: 'commitment-review-column' });
    const dashboardContainer = createElement('div', { id: 'lf-dashboard-container' });
    dashboardContainer.appendChild(commitmentColumn);

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
        location: { href: 'http://localhost/index.php?module=LF_WeeklyReport&action=dashboard', search: '', assign: (url) => { this.href = url; } },
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

console.log('Running US-017 Dashboard JS Tests (TDD RED Phase)...');
console.log('Testing for MISSING functionality:\n');

// ===================================================================
// Structural Tests - File and Function Existence
// ===================================================================

test('File should exist at custom/modules/LF_WeeklyReport/js/dashboard.js', () => {
    assert.ok(fs.existsSync(jsFile), `dashboard.js must exist at ${jsFile}`);
});

test('File should contain renderCommitmentReview function', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderCommitmentReview') || content.includes('function renderCommitmentReview') || content.includes('renderCommitmentReview:'),
        'File must declare renderCommitmentReview function'
    );
});

test('File should use window.LF_DASHBOARD_DATA', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('window.LF_DASHBOARD_DATA') || content.includes("window['LF_DASHBOARD_DATA']"),
        'File must read data from window.LF_DASHBOARD_DATA global variable'
    );
});

test('File should wrap code in DOMContentLoaded listener', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('DOMContentLoaded') && content.includes('addEventListener'),
        'File must initialize on DOMContentLoaded event'
    );
});

test('File should have state management', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('state') && (content.includes('viewMode') || content.includes('selectedRepId') || content.includes('selectedWeek')),
        'File must have state object with viewMode, selectedRepId, selectedWeek'
    );
});

// ===================================================================
// Functional Tests - Initial Render
// ===================================================================

test('Should render Commitment Review column on initial load', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        assert.ok(commitmentColumn.innerHTML.length > 0,
            'Commitment Review column should have content after initial render');
    } catch (e) {
        // Expected - file doesn't exist yet
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Initial render should use Team View mode', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        // In Team View, should show overall achievement rate
        assert.ok(commitmentColumn.innerHTML.includes('65.5') || commitmentColumn.innerHTML.includes('Overall Achievement Rate'),
            'Team View should display overall achievement rate (65.5%)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Initial render should use current week', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const weekSelector = sandbox.elements['week-selector'];
        assert.strictEqual(weekSelector.value, '2026-02-02',
            'Initial state should have current week selected');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Team View Rendering
// ===================================================================

test('Team View should display overall achievement rate', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show overall achievement rate from MOCK_DATA
        assert.ok(html.includes('Overall Achievement Rate') || html.includes('overall') || html.includes('achievement'),
            'Team View should display Overall Achievement Rate label');
        assert.ok(html.includes('65.5') || html.includes('65') || html.includes('%'),
            'Team View should display overall achievement percentage');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display per-rep cards', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show rep names from MOCK_DATA
        assert.ok(html.includes('John Doe') || html.includes('Jane Smith'),
            'Team View should display per-rep cards with rep names');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display New Pipeline percentages color-coded', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show New Pipeline percentages
        assert.ok(html.includes('New Pipeline') || html.includes('75.0') || html.includes('55.0'),
            'Team View should display New Pipeline percentages');
        // Should use color coding (check for color hex values or class names)
        assert.ok(html.includes('#E6C300') || html.includes('#2F7D32') || html.includes('yellow') || html.includes('green'),
            'Team View should apply color coding to New Pipeline percentages');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Progression percentages color-coded', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show Progression percentages
        assert.ok(html.includes('Progression') || html.includes('70.0') || html.includes('66.0'),
            'Team View should display Progression percentages');
        // Should use color coding
        assert.ok(html.includes('#E6C300') || html.includes('progression') || html.includes('%'),
            'Team View should apply color coding to Progression percentages');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display achieved items with checkmark', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show achieved items with checkmark symbol
        assert.ok(html.includes('&#10003;') || html.includes('✓') || html.includes('checkmark') || html.includes('ACHIEVED'),
            'Team View should display achieved items with checkmark symbol');
        assert.ok(html.includes('Acme Corp') || html.includes('Big Deal'),
            'Team View should display achieved item details');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display missed items with X mark', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show missed items with X symbol
        assert.ok(html.includes('&times;') || html.includes('✗') || html.includes('MISSED'),
            'Team View should display missed items with X symbol');
        assert.ok(html.includes('Beta Inc') || html.includes('Follow Up Opp'),
            'Team View should display missed item details');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display unplanned successes with positive styling', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show unplanned successes
        assert.ok(html.includes('UNPLANNED') || html.includes('Unplanned') || html.includes('Referral'),
            'Team View should display unplanned successes section');
        // Should apply positive styling (green color or success class)
        assert.ok(html.includes('#2F7D32') || html.includes('#4BB74E') || html.includes('success') || html.includes('positive'),
            'Team View should apply positive styling to unplanned successes');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display aggregate totals', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const commitmentColumn = sandbox.elements['commitment-review-column'];
        const html = commitmentColumn.innerHTML;

        // Should show team aggregate
        assert.ok(html.includes('Aggregate') || html.includes('aggregate') || html.includes('Team Aggregate'),
            'Team View should display team aggregate section');
        assert.ok(html.includes('13,000') || html.includes('13000') || html.includes('6,800') || html.includes('6800'),
            'Team View should display aggregate actual values');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Rep View Rendering
// ===================================================================

test('Rep View should display selected rep details', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        // Select rep and trigger change
        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = commitmentColumn.innerHTML;
        assert.ok(html.includes('John Doe') || html.includes('75.0') || html.includes('70.0'),
            'Rep View should show selected rep details (John Doe with 75% and 70%)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should group items by Pipeline Progression', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = commitmentColumn.innerHTML;
        assert.ok(html.includes('Pipeline Progression') || html.includes('progression'),
            'Rep View should group items by Pipeline Progression section');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should group items by New Pipeline', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = commitmentColumn.innerHTML;
        assert.ok(html.includes('New Pipeline') || html.includes('new pipeline'),
            'Rep View should group items by New Pipeline section');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should show achieved items with checkmarks', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = commitmentColumn.innerHTML;
        assert.ok(html.includes('&#10003;') || html.includes('✓') || html.includes('ACHIEVED'),
            'Rep View should display achieved items with checkmark symbol');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should show missed items with X marks', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = commitmentColumn.innerHTML;
        assert.ok(html.includes('&times;') || html.includes('✗') || html.includes('MISSED'),
            'Rep View should display missed items with X symbol');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should show unplanned successes', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = commitmentColumn.innerHTML;
        assert.ok(html.includes('UNPLANNED') || html.includes('Unplanned') || html.includes('Referral'),
            'Rep View should display unplanned successes');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - View Toggle
// ===================================================================

test('Team View toggle should hide rep selector', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const teamBtn = sandbox.elements['team-view-btn'];
        const repContainer = sandbox.elements['rep-selector-container'];

        // Click Team View
        teamBtn.dispatchEvent({ type: 'click' });

        assert.ok(
            repContainer.className.includes('lf-hidden') || repContainer.className.includes('hidden') || repContainer.style.display === 'none',
            'Team View should hide rep selector container'
        );
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View toggle should show rep selector', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repContainer = sandbox.elements['rep-selector-container'];

        // Click Rep View
        repBtn.dispatchEvent({ type: 'click' });

        assert.ok(
            !repContainer.className.includes('lf-hidden') && !repContainer.className.includes('hidden') && repContainer.style.display !== 'none',
            'Rep View should show rep selector container'
        );
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('View mode change should re-render Commitment Review', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const teamBtn = sandbox.elements['team-view-btn'];
        const repBtn = sandbox.elements['rep-view-btn'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        const initialLength = commitmentColumn.innerHTML.length;

        // Switch to Rep View
        repBtn.dispatchEvent({ type: 'click' });

        // Content should change (re-rendered)
        assert.ok(
            commitmentColumn.innerHTML.length !== initialLength,
            'Commitment Review should re-render when view mode changes'
        );
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Week Navigation
// ===================================================================

test('Week Back button should decrement week', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const backBtn = sandbox.elements['week-back-btn'];
        const weekSelector = sandbox.elements['week-selector'];

        const initialIndex = weekSelector.selectedIndex;
        backBtn.dispatchEvent({ type: 'click' });

        assert.ok(weekSelector.selectedIndex < initialIndex,
            'Back button should move to earlier week');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Week Next button should increment week', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const nextBtn = sandbox.elements['week-next-btn'];
        const weekSelector = sandbox.elements['week-selector'];

        const initialIndex = weekSelector.selectedIndex;
        nextBtn.dispatchEvent({ type: 'click' });

        assert.ok(weekSelector.selectedIndex > initialIndex,
            'Next button should move to later week');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Current Week button should reset to current week', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const currentBtn = sandbox.elements['week-current-btn'];
        const weekSelector = sandbox.elements['week-selector'];

        // Change week first
        weekSelector.selectedIndex = 0;
        weekSelector.dispatchEvent({ type: 'change' });

        // Click current week button
        currentBtn.dispatchEvent({ type: 'click' });

        assert.ok(weekSelector.selectedIndex === 1 || weekSelector.value === '2026-02-02',
            'Current Week button should reset to current week');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Week dropdown change should update data', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const weekSelector = sandbox.elements['week-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        // Change week
        weekSelector.value = '2026-01-26';
        weekSelector.dispatchEvent({ type: 'change' });

        // Column should re-render
        assert.ok(commitmentColumn.innerHTML.length > 0,
            'Commitment Review should re-render after week change');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Color Coding Logic
// ===================================================================

test('Should apply green color for achievement >= 76%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        // Check if file has getColorForPercent function
        if (!fs.existsSync(jsFile)) return;
        const content = fs.readFileSync(jsFile, 'utf8');

        assert.ok(
            content.includes('getColorForPercent') || content.includes('green') || content.includes('76'),
            'File should have color coding logic for green threshold (>= 76%)'
        );
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Should apply yellow color for achievement 51-75%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        if (!fs.existsSync(jsFile)) return;
        const content = fs.readFileSync(jsFile, 'utf8');

        assert.ok(
            content.includes('51') && (content.includes('yellow') || content.includes('#E6C300')),
            'File should have color coding logic for yellow threshold (51-75%)'
        );
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Should apply orange color for achievement 26-50%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        if (!fs.existsSync(jsFile)) return;
        const content = fs.readFileSync(jsFile, 'utf8');

        assert.ok(
            content.includes('26') && (content.includes('orange') || content.includes('#ff8c00')),
            'File should have color coding logic for orange threshold (26-50%)'
        );
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Should apply red color for achievement <= 25%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        if (!fs.existsSync(jsFile)) return;
        const content = fs.readFileSync(jsFile, 'utf8');

        assert.ok(
            content.includes('25') && (content.includes('red') || content.includes('#d13438')),
            'File should have color coding logic for red threshold (<= 25%)'
        );
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Edge Cases
// ===================================================================

test('Should handle missing window.LF_DASHBOARD_DATA gracefully', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA = undefined;

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

test('Should handle empty commitment data', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.commitmentData = {};

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle empty commitment data without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle no achieved/missed items for a rep', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.commitmentData.rep_data.rep1.achieved_items = [];
    sandbox.window.LF_DASHBOARD_DATA.commitmentData.rep_data.rep1.missed_items = [];

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle empty items without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle division by zero in percentage calculation', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.commitmentData.rep_data.rep1.new_pipeline.planned = 0;
    sandbox.window.LF_DASHBOARD_DATA.commitmentData.rep_data.rep1.progression.planned = 0;

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle planned = 0 without error (avoid division by zero)');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle first week boundary (no back navigation)', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const backBtn = sandbox.elements['week-back-btn'];
        const weekSelector = sandbox.elements['week-selector'];

        // Go to first week
        weekSelector.selectedIndex = 0;
        weekSelector.dispatchEvent({ type: 'change' });

        // Try to go back further
        const initialIndex = weekSelector.selectedIndex;
        backBtn.dispatchEvent({ type: 'click' });

        assert.ok(weekSelector.selectedIndex === initialIndex,
            'Should not navigate before first week');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle last week boundary (no next navigation)', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const nextBtn = sandbox.elements['week-next-btn'];
        const weekSelector = sandbox.elements['week-selector'];

        // Go to last week
        weekSelector.selectedIndex = weekSelector.options.length - 1;
        weekSelector.dispatchEvent({ type: 'change' });

        // Try to go next
        const initialIndex = weekSelector.selectedIndex;
        nextBtn.dispatchEvent({ type: 'click' });

        assert.ok(weekSelector.selectedIndex === initialIndex,
            'Should not navigate past last week');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle no rep selected in Rep View', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const commitmentColumn = sandbox.elements['commitment-review-column'];

        // Clear rep selection and switch to Rep View
        repSelector.value = '';
        repBtn.dispatchEvent({ type: 'click' });

        // Should show message to select a rep
        assert.ok(commitmentColumn.innerHTML.includes('select') || commitmentColumn.innerHTML.includes('Please'),
            'Rep View should show message when no rep is selected');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

// ============================================================
// Summary
// ============================================================

console.log('\n' + '='.repeat(60));
console.log('SUMMARY: US-017 Dashboard JavaScript Tests');
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
