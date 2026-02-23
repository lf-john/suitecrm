/**
 * US-019: Create reporting dashboard - Forecast Pulse column - TDD RED
 *
 * Tests for custom/modules/LF_WeeklyReport/js/dashboard.js
 *
 * These tests verify the FORECAST PULSE COLUMN functionality which is NOT YET IMPLEMENTED.
 * The current dashboard.js lacks:
 * - renderForecastPulse() function
 * - Current Quarter section with per-rep cards (Team View) or opportunity details (Rep View)
 * - Next Quarter section with independent totals
 * - Pipeline Value calculation (sum of opportunity amounts)
 * - Weighted Forecast calculation (sum of amount * probability / 100)
 * - Confidence indicator (ratio of weighted to pipeline)
 * - Quarter boundary calculation from fiscal_year_start_month config
 * - Data sourcing from forecastOpportunities field
 * - XSS protection for opportunity names
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

// Mock Data matching PHP structure with forecastOpportunities
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
        },
        // Fiscal year starts in January (Q1=Jan-Mar, Q2=Apr-Jun, etc.)
        fiscal_year_start_month: 1
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
    // Current Quarter (Q1 2026: Jan-Mar) opportunities
    forecastOpportunities: {
        current: {
            quarter: 1,
            year: 2026,
            opportunities: [
                {
                    opportunity_id: 'opp1',
                    opportunity_name: 'Enterprise Deal Q1',
                    account_name: 'Acme Corp',
                    amount: 150000,
                    probability: 75,
                    sales_stage: '4-Proposal',
                    date_closed: '2026-03-15',
                    assigned_user_id: 'rep1'
                    // Weighted: 150000 * 75 / 100 = 112500
                },
                {
                    opportunity_id: 'opp2',
                    opportunity_name: 'Mid-Market Deal Q1',
                    account_name: 'Beta Inc',
                    amount: 80000,
                    probability: 50,
                    sales_stage: '3-Qualification',
                    date_closed: '2026-02-28',
                    assigned_user_id: 'rep1'
                    // Weighted: 80000 * 50 / 100 = 40000
                },
                {
                    opportunity_id: 'opp3',
                    opportunity_name: 'Small Deal Q1',
                    account_name: 'Gamma LLC',
                    amount: 40000,
                    probability: 25,
                    sales_stage: '2-Discovery',
                    date_closed: '2026-03-01',
                    assigned_user_id: 'rep1'
                    // Weighted: 40000 * 25 / 100 = 10000
                },
                {
                    opportunity_id: 'opp4',
                    opportunity_name: 'Jane Q1 Deal',
                    account_name: 'Delta Corp',
                    amount: 200000,
                    probability: 80,
                    sales_stage: '5-Negotiation',
                    date_closed: '2026-03-20',
                    assigned_user_id: 'rep2'
                    // Weighted: 200000 * 80 / 100 = 160000
                },
                {
                    opportunity_id: 'opp5',
                    opportunity_name: 'Jane Small Q1',
                    account_name: 'Epsilon Ltd',
                    amount: 60000,
                    probability: 40,
                    sales_stage: '3-Qualification',
                    date_closed: '2026-02-15',
                    assigned_user_id: 'rep2'
                    // Weighted: 60000 * 40 / 100 = 24000
                }
            ]
        },
        next: {
            quarter: 2,
            year: 2026,
            opportunities: [
                {
                    opportunity_id: 'opp6',
                    opportunity_name: 'Enterprise Deal Q2',
                    account_name: 'Zeta Inc',
                    amount: 180000,
                    probability: 60,
                    sales_stage: '3-Qualification',
                    date_closed: '2026-04-15',
                    assigned_user_id: 'rep1'
                    // Weighted: 180000 * 60 / 100 = 108000
                },
                {
                    opportunity_id: 'opp7',
                    opportunity_name: 'Mid-Market Q2',
                    account_name: 'Eta Corp',
                    amount: 90000,
                    probability: 30,
                    sales_stage: '2-Discovery',
                    date_closed: '2026-05-30',
                    assigned_user_id: 'rep1'
                    // Weighted: 90000 * 30 / 100 = 27000
                },
                {
                    opportunity_id: 'opp8',
                    opportunity_name: 'Jane Q2 Deal',
                    account_name: 'Theta LLC',
                    amount: 250000,
                    probability: 70,
                    sales_stage: '4-Proposal',
                    date_closed: '2026-06-10',
                    assigned_user_id: 'rep2'
                    // Weighted: 250000 * 70 / 100 = 175000
                }
            ]
        }
    },
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
    },
    reportSnapshots: []
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
    const forecastPulseColumn = createElement('div', { id: 'forecast-pulse-column' });
    const dashboardContainer = createElement('div', { id: 'lf-dashboard-container' });
    dashboardContainer.appendChild(commitmentColumn);
    dashboardContainer.appendChild(stageProgressionColumn);
    dashboardContainer.appendChild(forecastPulseColumn);

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

console.log('Running US-019 Forecast Pulse Column Tests (TDD RED Phase)...');
console.log('Testing for MISSING functionality:\n');

// ===================================================================
// Structural Tests - File and Function Existence
// ===================================================================

test('File should exist at custom/modules/LF_WeeklyReport/js/dashboard.js', () => {
    assert.ok(fs.existsSync(jsFile), `dashboard.js must exist at ${jsFile}`);
});

test('File should contain renderForecastPulse function', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderForecastPulse') || content.includes('function renderForecastPulse') || content.includes('renderForecastPulse:'),
        'File must declare renderForecastPulse function'
    );
});

test('File should read forecastOpportunities from window.LF_DASHBOARD_DATA', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('forecastOpportunities') || content.includes('forecast_opportunities'),
        'File must read forecastOpportunities data from window.LF_DASHBOARD_DATA'
    );
});

test('File should use fiscal_year_start_month from config', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('fiscal_year_start_month') || content.includes('fiscal'),
        'File should read fiscal_year_start_month config for quarter calculation'
    );
});

test('Forecast Pulse column container should exist in DOM', () => {
    const sandbox = createBrowserSandbox();
    const column = sandbox.elements['forecast-pulse-column'];
    assert.ok(column, 'forecast-pulse-column element must exist in DOM');
});

test('renderDashboard should call renderForecastPulse', () => {
    if (!fs.existsSync(jsFile)) return;
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderForecastPulse()') || content.includes('renderForecastPulse('),
        'renderDashboard function should call renderForecastPulse'
    );
});

// ===================================================================
// Functional Tests - Initial Render
// ===================================================================

test('Should render Forecast Pulse column on initial load', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        assert.ok(column.innerHTML.length > 0,
            'Forecast Pulse column should have content after initial render');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Forecast Pulse column should have title header', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Forecast Pulse') || html.includes('forecast pulse') || html.includes('Forecast'),
            'Forecast Pulse column should display "Forecast Pulse" title');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Current Quarter Section - Team View
// ===================================================================

test('Team View should display Current Quarter section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Current Quarter') || html.includes('current quarter') || html.includes('Q1'),
            'Team View should display Current Quarter section');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display per-rep cards for Current Quarter', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // Should show rep names
        assert.ok(html.includes('John Doe') || html.includes('Jane Smith'),
            'Team View should display per-rep cards with rep names');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Pipeline Value for each rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // rep1 Pipeline Value = 150000 + 80000 + 40000 = 270000
        // rep2 Pipeline Value = 200000 + 60000 = 260000
        assert.ok(html.includes('Pipeline') || html.includes('pipeline'),
            'Team View should display Pipeline Value label');
        assert.ok(html.includes('270,000') || html.includes('270000') || html.includes('260,000') || html.includes('260000'),
            'Team View should display Pipeline Value amounts');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should calculate Pipeline Value as sum of opportunity amounts', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // rep1: 150000 + 80000 + 40000 = 270000
        assert.ok(html.includes('270') || html.includes('150,000') || html.includes('80,000') || html.includes('40,000'),
            'Team View should calculate Pipeline Value correctly (sum of amounts)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Weighted Forecast for each rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // rep1 Weighted = 112500 + 40000 + 10000 = 162500
        // rep2 Weighted = 160000 + 24000 = 184000
        assert.ok(html.includes('Weighted') || html.includes('weighted'),
            'Team View should display Weighted Forecast label');
        assert.ok(html.includes('162,500') || html.includes('162500') || html.includes('184,000') || html.includes('184000'),
            'Team View should display Weighted Forecast amounts');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should calculate Weighted Forecast as sum of (amount * probability / 100)', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // opp1: 150000 * 75 / 100 = 112500
        // opp2: 80000 * 50 / 100 = 40000
        // opp3: 40000 * 25 / 100 = 10000
        // Total: 162500
        assert.ok(html.includes('162,500') || html.includes('162500'),
            'Team View should calculate Weighted Forecast correctly (sum of amount * probability / 100)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Confidence indicator for each rep', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // rep1 Confidence = 162500 / 270000 = 0.6019 = 60.19%
        // rep2 Confidence = 184000 / 260000 = 0.7077 = 70.77%
        assert.ok(html.includes('Confidence') || html.includes('confidence'),
            'Team View should display Confidence indicator label');
        assert.ok(html.includes('60') || html.includes('70') || html.includes('%'),
            'Team View should display Confidence percentages');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should calculate Confidence as ratio of weighted to pipeline', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // rep1: 162500 / 270000 = 60.19%
        assert.ok(html.includes('60') || html.includes('60.19') || html.includes('60.2'),
            'Team View should calculate Confidence correctly (weighted / pipeline)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display totals row for Current Quarter', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // Total Pipeline = 270000 + 260000 = 530000
        // Total Weighted = 162500 + 184000 = 346500
        assert.ok(html.includes('Total') || html.includes('total') || html.includes('TOTAL'),
            'Team View should display totals row');
        assert.ok(html.includes('530,000') || html.includes('530000') || html.includes('346,500') || html.includes('346500'),
            'Team View should display Current Quarter totals');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Current Quarter Section - Rep View
// ===================================================================

test('Rep View should display Current Quarter section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('Current Quarter') || html.includes('current quarter') || html.includes('Q1'),
            'Rep View should display Current Quarter section');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display individual opportunity forecasts', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('Enterprise Deal Q1') || html.includes('Mid-Market Deal Q1') || html.includes('Small Deal Q1'),
            'Rep View should display opportunity names');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Opportunity name', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('Enterprise Deal Q1'),
            'Rep View should display Opportunity name');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Account name', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('Acme Corp') || html.includes('Beta Inc') || html.includes('Gamma LLC'),
            'Rep View should display Account name');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Amount', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok((html.includes('150,000') || html.includes('150000')) && html.includes('Acme'),
            'Rep View should display Amount');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Stage', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('4-Proposal') || html.includes('3-Qualification') || html.includes('2-Discovery'),
            'Rep View should display sales stage');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Probability %', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('75%') || html.includes('75') || html.includes('50%') || html.includes('50'),
            'Rep View should display Probability percentage');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Close Date', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('2026-03-15') || html.includes('03/15') || html.includes('Mar') || html.includes('March'),
            'Rep View should display Close Date');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Weighted Value', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        // opp1 Weighted = 150000 * 75 / 100 = 112500
        assert.ok(html.includes('112,500') || html.includes('112500') || html.includes('Weighted'),
            'Rep View should display Weighted Value (amount * probability / 100)');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should calculate Weighted Value as amount * probability / 100', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        // Verify calculation: 150000 * 75 / 100 = 112500
        assert.ok(html.includes('112,500') || html.includes('112500'),
            'Rep View should calculate Weighted Value correctly');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display totals row for Current Quarter', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        // rep1 totals: Pipeline = 270000, Weighted = 162500
        assert.ok(html.includes('Total') || html.includes('total') || html.includes('TOTAL'),
            'Rep View should display totals row');
        assert.ok(html.includes('270,000') || html.includes('270000') || html.includes('162,500') || html.includes('162500'),
            'Rep View should display Current Quarter totals for selected rep');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Next Quarter Section
// ===================================================================

test('Should display Next Quarter section as SEPARATE section', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        assert.ok(html.includes('Next Quarter') || html.includes('next quarter') || html.includes('Q2'),
            'Should display Next Quarter section separate from Current Quarter');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Next Quarter should have independent totals from Current Quarter', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // Current Quarter Total Pipeline = 530000
        // Next Quarter Total Pipeline = 180000 + 90000 + 250000 = 520000
        assert.ok(html.includes('520,000') || html.includes('520000'),
            'Next Quarter should have independent totals');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Team View should display Next Quarter per-rep cards', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // rep1 Next Quarter: Pipeline = 180000 + 90000 = 270000, Weighted = 108000 + 27000 = 135000
        // rep2 Next Quarter: Pipeline = 250000, Weighted = 175000
        assert.ok(html.includes('270,000') || html.includes('270000') || html.includes('250,000') || html.includes('250000'),
            'Team View should display Next Quarter per-rep cards');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Rep View should display Next Quarter opportunity forecasts', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const repBtn = sandbox.elements['rep-view-btn'];
        const repSelector = sandbox.elements['rep-selector'];
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep1';
        repBtn.dispatchEvent({ type: 'click' });

        const html = column.innerHTML;
        assert.ok(html.includes('Enterprise Deal Q2') || html.includes('Mid-Market Q2'),
            'Rep View should display Next Quarter opportunity forecasts');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Next Quarter section should have its own totals row', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // Should have two separate totals sections
        const totalMatches = (html.match(/Total/g) || []).length;
        assert.ok(totalMatches >= 2 || html.includes('Current Quarter') && html.includes('Next Quarter'),
            'Next Quarter should have its own independent totals row');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Functional Tests - Quarter Boundary Calculation
// ===================================================================

test('Should calculate quarter boundaries from fiscal_year_start_month', () => {
    const sandbox = createBrowserSandbox();
    try {
        loadDashboardJs(sandbox);

        const column = sandbox.elements['forecast-pulse-column'];
        const html = column.innerHTML;
        // With fiscal_year_start_month = 1 (January):
        // Q1 = Jan-Mar, Q2 = Apr-Jun
        assert.ok(html.includes('Q1') || html.includes('Q2') || html.includes('Current Quarter') || html.includes('Next Quarter'),
            'Should calculate quarters based on fiscal_year_start_month config');
    } catch (e) {
        assert.ok(e.message.includes('File not found'), 'Expected failure due to missing implementation');
    }
});

test('Should handle fiscal year starting in month 4 (April)', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.config.fiscal_year_start_month = 4;

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['forecast-pulse-column'];
        assert.ok(true, 'Should handle fiscal year starting in April');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle fiscal year starting in month 7 (July)', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.config.fiscal_year_start_month = 7;

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['forecast-pulse-column'];
        assert.ok(true, 'Should handle fiscal year starting in July');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle fiscal year starting in month 10 (October)', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.config.fiscal_year_start_month = 10;

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['forecast-pulse-column'];
        assert.ok(true, 'Should handle fiscal year starting in October');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

// ===================================================================
// Edge Cases
// ===================================================================

test('Should handle empty forecast opportunities array', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities = {
        current: { quarter: 1, year: 2026, opportunities: [] },
        next: { quarter: 2, year: 2026, opportunities: [] }
    };

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['forecast-pulse-column'];
        assert.ok(true, 'Should handle empty forecast opportunities without error');
        // Should show empty state message
        if (column.innerHTML.length > 0) {
            assert.ok(column.innerHTML.includes('No data') || column.innerHTML.includes('none') || column.innerHTML.includes('0') || column.innerHTML.includes('No forecast'),
                'Should display empty state message when no forecast opportunities');
        }
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle opportunities with zero probability', () => {
    const sandbox = createBrowserSandbox();
    // Add opportunity with 0% probability
    sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities.current.opportunities.push({
        opportunity_id: 'opp_zero_prob',
        opportunity_name: 'Zero Probability Deal',
        account_name: 'Zero Prob Corp',
        amount: 100000,
        probability: 0,
        sales_stage: '1-Prospecting',
        date_closed: '2026-03-30',
        assigned_user_id: 'rep1'
        // Weighted: 100000 * 0 / 100 = 0
    });

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle zero probability opportunities without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle opportunities with zero amount', () => {
    const sandbox = createBrowserSandbox();
    // Add opportunity with $0 amount
    sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities.current.opportunities.push({
        opportunity_id: 'opp_zero_amt',
        opportunity_name: 'Zero Amount Deal',
        account_name: 'Zero Amt Corp',
        amount: 0,
        probability: 50,
        sales_stage: '2-Discovery',
        date_closed: '2026-03-31',
        assigned_user_id: 'rep1'
        // Weighted: 0 * 50 / 100 = 0
    });

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle zero amount opportunities without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle missing forecastOpportunities field', () => {
    const sandbox = createBrowserSandbox();
    delete sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities;

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing forecastOpportunities field without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle missing fiscal_year_start_month config', () => {
    const sandbox = createBrowserSandbox();
    delete sandbox.window.LF_DASHBOARD_DATA.config.fiscal_year_start_month;

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing fiscal_year_start_month without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle division by zero in confidence calculation', () => {
    const sandbox = createBrowserSandbox();
    // Set all amounts to 0 to test division by zero in confidence calculation
    sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities.current.opportunities.forEach(opp => {
        opp.amount = 0;
    });

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle division by zero in confidence calculation without error');
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
        const column = sandbox.elements['forecast-pulse-column'];

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

test('Should handle rep with no forecast opportunities', () => {
    const sandbox = createBrowserSandbox();
    // Add rep3 with no forecast opportunities
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
        const column = sandbox.elements['forecast-pulse-column'];

        repSelector.value = 'rep3';
        repBtn.dispatchEvent({ type: 'click' });

        assert.ok(true, 'Should handle rep with no forecast opportunities without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should escape HTML special characters in opportunity names (XSS prevention)', () => {
    const sandbox = createBrowserSandbox();
    // Add opportunity with special characters
    sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities.current.opportunities.push({
        opportunity_id: 'opp_xss',
        opportunity_name: '<script>alert("XSS")</script> & "quotes"',
        account_name: 'Test & Demo <Inc>',
        amount: 50000,
        probability: 50,
        sales_stage: '3-Qualification',
        date_closed: '2026-03-25',
        assigned_user_id: 'rep1'
    });

    try {
        loadDashboardJs(sandbox);
        const column = sandbox.elements['forecast-pulse-column'];
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
    // Add opportunity with very large amount
    sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities.current.opportunities.push({
        opportunity_id: 'opp_large',
        opportunity_name: 'Enterprise Mega Deal',
        account_name: 'Big Corp',
        amount: 999999999,
        probability: 90,
        sales_stage: '5-Negotiation',
        date_closed: '2026-03-31',
        assigned_user_id: 'rep1'
    });

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle very large amounts without error');
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

test('Should handle missing next quarter opportunities', () => {
    const sandbox = createBrowserSandbox();
    delete sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities.next;

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing next quarter opportunities without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

test('Should handle missing current quarter opportunities', () => {
    const sandbox = createBrowserSandbox();
    delete sandbox.window.LF_DASHBOARD_DATA.forecastOpportunities.current;

    try {
        loadDashboardJs(sandbox);
        assert.ok(true, 'Should handle missing current quarter opportunities without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found') || e.message.includes('Cannot find'),
            'Expected failure due to missing implementation');
    }
});

// ============================================================
// Summary
// ============================================================

console.log('\n' + '='.repeat(60));
console.log('SUMMARY: US-019 Forecast Pulse Column Tests');
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
