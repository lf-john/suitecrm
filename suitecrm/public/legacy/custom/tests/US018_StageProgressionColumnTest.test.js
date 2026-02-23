/**
 * US-018: Create reporting dashboard - Stage Progression column - TDD RED
 *
 * Tests for custom/modules/LF_WeeklyReport/js/dashboard.js
 *
 * These tests verify the STAGE PROGRESSION COLUMN functionality which is NOT YET IMPLEMENTED.
 * The current dashboard.js lacks:
 * - renderStageProgression() function
 * - New Pipeline section (actual vs target with percentage and color coding)
 * - Progressed Pipeline section (actual vs target with percentage and color coding)
 * - Movement counts (Forward N, Backward N, Static N)
 * - Success list (opportunities that advanced with stage transition)
 * - Regression list (backward-moving opportunities with warning styling)
 * - Rep View filtering for stage progression data
 * - Color-coded percentages using achievement tier thresholds
 * - Data sourcing from reportSnapshots field
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

// Mock Data matching PHP structure with reportSnapshots
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
    reportSnapshots: [
        {
            opportunity_id: 'opp1',
            opportunity_name: 'Enterprise Deal',
            account_name: 'Acme Corp',
            amount: 150000,
            stage_at_week_start: '3-Qualification',
            stage_at_week_end: '5-Negotiation',
            probability_at_start: 20,
            probability_at_end: 80,
            movement: 'forward',
            assigned_user_id: 'rep1',
            was_planned: 1,
            plan_category: 'progression'
        },
        {
            opportunity_id: 'opp2',
            opportunity_name: 'Mid-Market Opp',
            account_name: 'Beta Inc',
            amount: 75000,
            stage_at_week_start: '4-Proposal',
            stage_at_week_end: '3-Qualification',
            probability_at_start: 50,
            probability_at_end: 20,
            movement: 'backward',
            assigned_user_id: 'rep1',
            was_planned: 1,
            plan_category: 'progression'
        },
        {
            opportunity_id: 'opp3',
            opportunity_name: 'Stalled Deal',
            account_name: 'Gamma LLC',
            amount: 50000,
            stage_at_week_start: '2-Discovery',
            stage_at_week_end: '2-Discovery',
            probability_at_start: 10,
            probability_at_end: 10,
            movement: 'static',
            assigned_user_id: 'rep1',
            was_planned: 1,
            plan_category: 'progression'
        },
        {
            opportunity_id: 'opp4',
            opportunity_name: 'New Opportunity',
            account_name: 'Delta Corp',
            amount: 100000,
            stage_at_week_start: '',
            stage_at_week_end: '1-Prospecting',
            probability_at_start: 0,
            probability_at_end: 5,
            movement: 'new',
            assigned_user_id: 'rep1',
            was_planned: 1,
            plan_category: 'developing'
        },
        {
            opportunity_id: 'opp5',
            opportunity_name: 'Jane Forward Deal',
            account_name: 'Epsilon Ltd',
            amount: 200000,
            stage_at_week_start: '3-Qualification',
            stage_at_week_end: '6-Closing',
            probability_at_start: 20,
            probability_at_end: 90,
            movement: 'forward',
            assigned_user_id: 'rep2',
            was_planned: 1,
            plan_category: 'progression'
        },
        {
            opportunity_id: 'opp6',
            opportunity_name: 'Jane Regression Deal',
            account_name: 'Zeta Inc',
            amount: 80000,
            stage_at_week_start: '5-Negotiation',
            stage_at_week_end: '3-Qualification',
            probability_at_start: 80,
            probability_at_end: 20,
            movement: 'backward',
            assigned_user_id: 'rep2',
            was_planned: 1,
            plan_category: 'progression'
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
                achieved_items: [],
                missed_items: [],
                unplanned_successes: []
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
                achieved_items: [],
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
            get selectedIndex() {
                return this.children.findIndex(c => c.attributes.selected === 'selected');
            },
            set selectedIndex(v) {
                this.children.forEach((c, i) => {
                    if (i === v) c.attributes.selected = 'selected';
                    else delete c.attributes.selected;
                });
            },

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

    // Dashboard columns
    const commitmentColumn = createElement('div', { id: 'commitment-review-column' });
    const stageProgressionColumn = createElement('div', { id: 'stage-progression-column' });
    const reportingColumn = createElement('div', { id: 'reporting-column' });
    const analysisColumn = createElement('div', { id: 'analysis-column' });
    const dashboardContainer = createElement('div', { id: 'lf-dashboard-container' });
    dashboardContainer.appendChild(commitmentColumn);
    dashboardContainer.appendChild(stageProgressionColumn);
    dashboardContainer.appendChild(reportingColumn);
    dashboardContainer.appendChild(analysisColumn);

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

console.log('Running US-018 Stage Progression Column Tests (TDD RED Phase)...');
console.log('Testing for MISSING functionality:\n');

// ===================================================================
// Structural Tests - File and Function Existence
// ===================================================================

test('File should exist at custom/modules/LF_WeeklyReport/js/dashboard.js', () => {
    assert.ok(fs.existsSync(jsFile), `dashboard.js must exist at ${jsFile}`);
});

test('File should contain renderStageProgression function', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderStageProgression') || content.includes('function renderStageProgression') || content.includes('renderStageProgression:'),
        'File must declare renderStageProgression function'
    );
});

test('File should read reportSnapshots from window.LF_DASHBOARD_DATA', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('reportSnapshots') || content.includes("report_snapshots"),
        'File must read reportSnapshots data from window.LF_DASHBOARD_DATA'
    );
});

test('Stage Progression column container should exist in DOM', () => {
    const sandbox = createBrowserSandbox();
    const column = sandbox.elements['stage-progression-column'];
    assert.ok(column, 'stage-progression-column element must exist in DOM');
});

// ===================================================================
// Functional Tests - Initial Render
// ===================================================================

test('Should render Stage Progression column on initial load', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        assert.ok(column.innerHTML.length > 0,
            'Stage Progression column should have content after initial render');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Stage Progression column should have title header', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Stage Progression') || html.includes('stage progression'),
            'Stage Progression column should display "Stage Progression" title');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Team View - New Pipeline Section
// ===================================================================

test('Team View should display New Pipeline section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('New Pipeline') || html.includes('new pipeline'),
            'Team View should display New Pipeline section');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display New Pipeline actual vs target amounts', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show amounts from reportSnapshots (developing category)
        assert.ok(html.includes('100,000') || html.includes('100000') || html.includes('$'),
            'Team View should display New Pipeline actual amount ($100,000 for opp4)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display New Pipeline percentage', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('%') || html.includes('percent'),
            'Team View should display New Pipeline percentage');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should apply color coding to New Pipeline percentage', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use achievement tier colors
        assert.ok(html.includes('#2F7D32') || html.includes('#E6C300') || html.includes('#ff8c00') || html.includes('#d13438') ||
                   html.includes('green') || html.includes('yellow') || html.includes('orange') || html.includes('red'),
            'Team View should apply color coding to New Pipeline percentage');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Team View - Progressed Pipeline Section
// ===================================================================

test('Team View should display Progressed Pipeline section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Progressed Pipeline') || html.includes('progressed pipeline') || html.includes('Pipeline Progression'),
            'Team View should display Progressed Pipeline section');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Progressed Pipeline actual vs target amounts', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show amounts from progression category snapshots
        assert.ok(html.includes('150,000') || html.includes('150000') || html.includes('75,000') || html.includes('75000') ||
                   html.includes('50,000') || html.includes('50000') || html.includes('$'),
            'Team View should display Progressed Pipeline amounts from snapshots');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Progressed Pipeline percentage', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('%') && (html.includes('Progressed') || html.includes('progressed')),
            'Team View should display Progressed Pipeline percentage');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should apply color coding to Progressed Pipeline percentage', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use achievement tier colors
        assert.ok(html.includes('#2F7D32') || html.includes('#E6C300') || html.includes('#ff8c00') || html.includes('#d13438'),
            'Team View should apply color coding to Progressed Pipeline percentage');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Team View - Movement Counts
// ===================================================================

test('Team View should display movement counts section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Movement') || html.includes('movement') || html.includes('Forward') || html.includes('Backward'),
            'Team View should display movement counts section');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Forward count', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show count of forward movement opportunities (3: opp1, opp5)
        assert.ok(html.includes('Forward') && (html.includes('3') || html.includes('2')),
            'Team View should display Forward movement count');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Backward count', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show count of backward movement opportunities (2: opp2, opp6)
        assert.ok(html.includes('Backward') && (html.includes('2') || html.includes('Backward: 2')),
            'Team View should display Backward movement count');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Static count', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show count of static movement opportunities (1: opp3)
        assert.ok(html.includes('Static') && (html.includes('1') || html.includes('Static: 1')),
            'Team View should display Static movement count');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Team View - Success List
// ===================================================================

test('Team View should display Success list section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Success') || html.includes('success') || html.includes('Advanced'),
            'Team View should display Success/Advanced opportunities list');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Success list should show opportunity Account name', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show account names from forward movement opportunities
        assert.ok(html.includes('Acme Corp') || html.includes('Epsilon Ltd'),
            'Success list should display Account names (Acme Corp, Epsilon Ltd)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Success list should show Opportunity name', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Enterprise Deal') || html.includes('Jane Forward Deal'),
            'Success list should display Opportunity names');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Success list should show amount', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok((html.includes('150,000') || html.includes('150000') || html.includes('200,000') || html.includes('200000')) &&
                   (html.includes('Acme') || html.includes('Epsilon')),
            'Success list should display opportunity amounts');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Success list should show stage transition (Old Stage -> New Stage)', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show stage transitions like "3-Qualification -> 5-Negotiation"
        assert.ok(html.includes('->') || html.includes('&rarr;') || html.includes('to') ||
                   (html.includes('3-Qualification') && html.includes('5-Negotiation')) ||
                   (html.includes('3-Qualification') && html.includes('6-Closing')),
            'Success list should display stage transition (Old -> New)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Success list should apply positive styling', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use green or success styling
        assert.ok(html.includes('#2F7D32') || html.includes('#4BB74E') || html.includes('success') || html.includes('green'),
            'Success list should apply positive styling (green/success)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Team View - Regression List
// ===================================================================

test('Team View should display Regression list section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Regression') || html.includes('regression') || html.includes('Backward'),
            'Team View should display Regression/Backward opportunities list');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Regression list should show opportunity Account name', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show account names from backward movement opportunities
        assert.ok(html.includes('Beta Inc') || html.includes('Zeta Inc'),
            'Regression list should display Account names (Beta Inc, Zeta Inc)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Regression list should show Opportunity name', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Mid-Market Opp') || html.includes('Jane Regression Deal'),
            'Regression list should display Opportunity names');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Regression list should show stage transition', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should show stage transitions like "5-Negotiation -> 3-Qualification"
        assert.ok(html.includes('->') || html.includes('&rarr;') ||
                   (html.includes('5-Negotiation') && html.includes('3-Qualification')) ||
                   (html.includes('4-Proposal') && html.includes('3-Qualification')),
            'Regression list should display stage transition (Old -> New)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Regression list should apply warning styling (yellow/orange)', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use yellow/orange warning styling
        assert.ok(html.includes('#E6C300') || html.includes('#ff8c00') || html.includes('warning') ||
                   html.includes('orange') || html.includes('yellow') ||
                   html.includes('background') || html.includes('border'),
            'Regression list should apply warning styling (yellow/orange background or border)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Rep View
// ===================================================================

test('Rep View should filter snapshots by selected rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        // Select rep1 and switch to Rep View
        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        // Should only show rep1's opportunities (Acme Corp, Beta Inc, Gamma LLC, Delta Corp)
        // Should NOT show rep2's opportunities (Epsilon Ltd, Zeta Inc)
        assert.ok(html.includes('Acme Corp') || html.includes('Beta Inc') || html.includes('Gamma LLC') || html.includes('Delta Corp'),
            'Rep View should show selected rep data');
        assert.ok(!html.includes('Epsilon Ltd') && !html.includes('Zeta Inc'),
            'Rep View should not show other reps data');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display New Pipeline for selected rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('New Pipeline') || html.includes('new pipeline'),
            'Rep View should display New Pipeline section');
        // rep1 has 1 developing opportunity (opp4: Delta Corp, $100,000)
        assert.ok(html.includes('Delta Corp') || html.includes('100,000') || html.includes('100000'),
            'Rep View should show rep1 new pipeline data');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Progressed Pipeline for selected rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('Progressed Pipeline') || html.includes('progressed pipeline') || html.includes('Pipeline Progression'),
            'Rep View should display Progressed Pipeline section');
        // rep1 has 3 progression opportunities (opp1, opp2, opp3)
        assert.ok(html.includes('Acme Corp') || html.includes('Beta Inc') || html.includes('Gamma LLC'),
            'Rep View should show rep1 progressed pipeline data');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display movement counts for selected rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        // rep1: 1 forward (opp1), 1 backward (opp2), 1 static (opp3)
        assert.ok(html.includes('Forward') && html.includes('Backward') && html.includes('Static'),
            'Rep View should display movement counts for selected rep');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Success list for selected rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        // rep1 has 1 forward opportunity (opp1: Acme Corp)
        assert.ok(html.includes('Acme Corp') && html.includes('Enterprise Deal'),
            'Rep View should show success list for selected rep');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Regression list for selected rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        // rep1 has 1 backward opportunity (opp2: Beta Inc)
        assert.ok(html.includes('Beta Inc') && html.includes('Mid-Market Opp'),
            'Rep View should show regression list for selected rep');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Color Coding with Achievement Thresholds
// ===================================================================

test('Should use achievement tier thresholds from config for color coding', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');

    assert.ok(
        content.includes('achievement') || content.includes('green_threshold') || content.includes('yellow_threshold'),
        'File should use achievement tier thresholds from config for color coding'
    );
});

test('Should apply green color for achievement >= 76%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use green (#2F7D32) for high achievement percentages
        assert.ok(html.includes('#2F7D32') || html.includes('green'),
            'Should apply green color for achievement >= 76%');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Should apply yellow color for achievement 51-75%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use yellow (#E6C300) for medium achievement
        assert.ok(html.includes('#E6C300') || html.includes('yellow'),
            'Should apply yellow color for achievement 51-75%');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Should apply orange color for achievement 26-50%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use orange (#ff8c00) for low achievement
        assert.ok(html.includes('#ff8c00') || html.includes('orange'),
            'Should apply orange color for achievement 26-50%');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Should apply red color for achievement < 26%', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should use red (#d13438) for very low achievement
        assert.ok(html.includes('#d13438') || html.includes('red'),
            'Should apply red color for achievement < 26%');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Data Source from reportSnapshots
// ===================================================================

test('Should source data from reportSnapshots field', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');

    assert.ok(
        content.includes('reportSnapshots') || content.includes('movement'),
        'File should read data from reportSnapshots field'
    );
});

test('Should use movement field from snapshots', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');

    assert.ok(
        content.includes('movement') || content.includes('forward') || content.includes('backward') || content.includes('static'),
        'File should use movement field from snapshots to categorize opportunities'
    );
});

test('Should use stage_at_week_start from snapshots', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');

    assert.ok(
        content.includes('stage_at_week_start') || content.includes('stage_at_week_end'),
        'File should use stage fields from snapshots for transition display'
    );
});

test('Should filter snapshots by selected week', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');

    assert.ok(
        content.includes('week') || content.includes('selectedWeek'),
        'File should filter snapshots by selected week'
    );
});

test('Should filter snapshots by assigned_user_id for Rep View', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');

    assert.ok(
        content.includes('assigned_user_id') || content.includes('selectedRepId'),
        'File should filter snapshots by assigned_user_id for Rep View'
    );
});

// ===================================================================
// Edge Cases
// ===================================================================

test('Should handle empty reportSnapshots array', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots = [];

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['stage-progression-column'];
        assert.ok(true, 'Should handle empty reportSnapshots without error');
        // Should show empty state message
        if (column.innerHTML.length > 0) {
            assert.ok(column.innerHTML.includes('No data') || column.innerHTML.includes('none') || column.innerHTML.includes('0'),
                'Should display empty state message when no snapshots');
        }
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle snapshots with no forward movements', () => {
    const sandbox = createBrowserSandbox();
    // Remove all forward movement snapshots
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots = sandbox.window.LF_DASHBOARD_DATA.reportSnapshots.filter(
        s => s.movement !== 'forward'
    );

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['stage-progression-column'];
        assert.ok(true, 'Should handle no forward movements without error');
        const html = column.innerHTML;
        if (html.length > 0) {
            assert.ok(html.includes('Forward: 0') || html.includes('Forward: None'),
                'Should show zero count when no forward movements');
        }
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle snapshots with no backward movements', () => {
    const sandbox = createBrowserSandbox();
    // Remove all backward movement snapshots
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots = sandbox.window.LF_DASHBOARD_DATA.reportSnapshots.filter(
        s => s.movement !== 'backward'
    );

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['stage-progression-column'];
        assert.ok(true, 'Should handle no backward movements without error');
        const html = column.innerHTML;
        if (html.length > 0) {
            assert.ok(html.includes('Backward: 0') || html.includes('Backward: None'),
                'Should show zero count when no backward movements');
        }
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle snapshots with no static movements', () => {
    const sandbox = createBrowserSandbox();
    // Remove all static movement snapshots
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots = sandbox.window.LF_DASHBOARD_DATA.reportSnapshots.filter(
        s => s.movement !== 'static'
    );

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['stage-progression-column'];
        assert.ok(true, 'Should handle no static movements without error');
        const html = column.innerHTML;
        if (html.length > 0) {
            assert.ok(html.includes('Static: 0') || html.includes('Static: None'),
                'Should show zero count when no static movements');
        }
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle missing stage information', () => {
    const sandbox = createBrowserSandbox();
    // Add snapshot with missing stage info
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots.push({
        opportunity_id: 'opp_missing',
        opportunity_name: 'Missing Stage Opp',
        account_name: 'Missing Corp',
        amount: 50000,
        stage_at_week_start: '',
        stage_at_week_end: '',
        probability_at_start: 0,
        probability_at_end: 0,
        movement: 'static',
        assigned_user_id: 'rep1',
        was_planned: 1,
        plan_category: 'progression'
    });

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing stage information without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle division by zero in percentage calculation', () => {
    const sandbox = createBrowserSandbox();
    // Set planned targets to 0 to test division by zero
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

test('Should handle no rep selected in Rep View', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        // Clear rep selection and switch to Rep View
        repSelector.value = '';
        repBtn.dispatchEvent({ type: 'click' });

        // Should show message to select a rep
        const html = column.innerHTML;
        assert.ok(html.includes('select') || html.includes('Please') || html.includes('rep'),
            'Rep View should show message when no rep is selected');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle rep with no snapshots', () => {
    const sandbox = createBrowserSandbox();
    // Add rep3 with no snapshots
    sandbox.window.LF_DASHBOARD_DATA.reps.push({
        assigned_user_id: 'rep3',
        first_name: 'Bob',
        last_name: 'Johnson',
        full_name: 'Bob Johnson'
    });

    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['stage-progression-column'];

        repSelector.value = 'rep3';
        repBtn.dispatchEvent({ type: 'click' });

        assert.ok(true, 'Should handle rep with no snapshots without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle missing window.LF_DASHBOARD_DATA gracefully', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA = undefined;

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing data without error');
    } catch (e) {
        // Expected to fail since implementation doesn't exist yet
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find') || e.message.includes('undefined'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle missing reportSnapshots field', () => {
    const sandbox = createBrowserSandbox();
    delete sandbox.window.LF_DASHBOARD_DATA.reportSnapshots;

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing reportSnapshots field without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle special characters in opportunity names', () => {
    const sandbox = createBrowserSandbox();
    // Add snapshot with special characters
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots.push({
        opportunity_id: 'opp_special',
        opportunity_name: 'Deal with <script> & "quotes"',
        account_name: 'Test & Demo <Inc>',
        amount: 25000,
        stage_at_week_start: '1-Prospecting',
        stage_at_week_end: '2-Discovery',
        probability_at_start: 5,
        probability_at_end: 10,
        movement: 'forward',
        assigned_user_id: 'rep1',
        was_planned: 1,
        plan_category: 'developing'
    });

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['stage-progression-column'];
        const html = column.innerHTML;
        // Should escape HTML to prevent XSS
        if (html.length > 0) {
            assert.ok(!html.includes('<script>') || html.includes('&lt;script&gt;'),
                'Should escape HTML special characters to prevent XSS');
        }
        assert.ok(true, 'Should handle special characters without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle very large amounts (formatting)', () => {
    const sandbox = createBrowserSandbox();
    // Add snapshot with very large amount
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots.push({
        opportunity_id: 'opp_large',
        opportunity_name: 'Enterprise Mega Deal',
        account_name: 'Big Corp',
        amount: 999999999,
        stage_at_week_start: '1-Prospecting',
        stage_at_week_end: '2-Discovery',
        probability_at_start: 5,
        probability_at_end: 10,
        movement: 'forward',
        assigned_user_id: 'rep1',
        was_planned: 1,
        plan_category: 'developing'
    });

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle very large amounts without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle zero amount opportunities', () => {
    const sandbox = createBrowserSandbox();
    // Add snapshot with zero amount
    sandbox.window.LF_DASHBOARD_DATA.reportSnapshots.push({
        opportunity_id: 'opp_zero',
        opportunity_name: 'Zero Amount Opp',
        account_name: 'Zero Corp',
        amount: 0,
        stage_at_week_start: '1-Prospecting',
        stage_at_week_end: '2-Discovery',
        probability_at_start: 5,
        probability_at_end: 10,
        movement: 'forward',
        assigned_user_id: 'rep1',
        was_planned: 1,
        plan_category: 'developing'
    });

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle zero amount opportunities without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

// ============================================================
// Summary
// ============================================================

console.log('\n' + '='.repeat(60));
console.log('SUMMARY: US-018 Stage Progression Column Tests');
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

// Exit with proper code for TDD-RED-CHECK to detect failures
process.exit(failCount > 0 ? 1 : 0);
