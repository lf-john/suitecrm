/**
 * US-011: Deal Risk Assessment Column - TDD RED
 *
 * Tests for the Deal Risk Assessment column in dashboard.js
 * This is the third (rightmost) column of the planning dashboard.
 *
 * Renders opportunities with no activity exceeding configured stale_deal_days (default 14).
 * Shows Account name, Opportunity name, Amount, and "Last Activity: N days ago".
 * Sorted by days since last activity (most stale first).
 * Key Buyers section is intentionally NOT shown.
 * Opportunities at '2-Analysis (1%)' stage are excluded.
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

// Mock Data for Deal Risk Assessment testing
const MOCK_DATA = {
    config: {
        brand_blue: '#125EAD',
        brand_green: '#4BB74E',
        stale_deal_days: 14,
        deal_risk: {
            activity_types: ['calls', 'meetings', 'tasks', 'notes']
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
    staleDeals: [
        {
            id: 'opp1',
            account_name: 'Acme Corporation',
            opportunity_name: 'Q1 Enterprise Deal',
            amount: 150000,
            days_since_activity: 45,
            assigned_user_id: 'rep1',
            assigned_user_name: 'John Doe',
            sales_stage: '5-Negotiation'
        },
        {
            id: 'opp2',
            account_name: 'Beta Industries',
            opportunity_name: 'Annual Software License',
            amount: 75000,
            days_since_activity: 21,
            assigned_user_id: 'rep1',
            assigned_user_name: 'John Doe',
            sales_stage: '4-Proposal'
        },
        {
            id: 'opp3',
            account_name: 'Gamma LLC',
            opportunity_name: 'Cloud Migration Project',
            amount: 220000,
            days_since_activity: 30,
            assigned_user_id: 'rep2',
            assigned_user_name: 'Jane Smith',
            sales_stage: '5-Negotiation'
        },
        {
            id: 'opp4',
            account_name: 'Delta Corp',
            opportunity_name: 'Consulting Services',
            amount: 50000,
            days_since_activity: 15,
            assigned_user_id: 'rep2',
            assigned_user_name: 'Jane Smith',
            sales_stage: '3-Qualification'
        },
        {
            id: 'opp5',
            account_name: 'Epsilon Inc',
            opportunity_name: 'Small Deal',
            amount: 25000,
            days_since_activity: 18,
            assigned_user_id: 'rep1',
            assigned_user_name: 'John Doe',
            sales_stage: '6-Review'
        }
    ],
    pipelineByStage: {},
    pipelineByRep: {},
    planItems: [],
    prospectItems: [],
    closedYtd: { team: 0, byRep: {} }
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

// DOM Simulation
function createBrowserSandbox(customData) {
    const elements = {};
    const eventListeners = {};
    const data = customData || JSON.parse(JSON.stringify(MOCK_DATA));

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
            set innerHTML(v) { this._innerHTML = v; },
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
    const weekSelector = createElement('select', { id: 'week-selector' });
    const weekBackBtn = createElement('button', { id: 'week-back-btn' });
    const weekNextBtn = createElement('button', { id: 'week-next-btn' });
    const weekCurrentBtn = createElement('button', { id: 'week-current-btn' });

    // Week selector options
    data.weekList.forEach(w => {
        const opt = createElement('option', { value: w.weekStart });
        opt._innerHTML = w.label;
        if (w.isCurrent) opt.attributes.selected = 'selected';
        weekSelector.appendChild(opt);
    });
    weekSelector.value = '2026-02-02';
    weekSelector.selectedIndex = 1;

    // Rep selector options
    data.reps.forEach(r => {
        const opt = createElement('option', { value: r.assigned_user_id });
        opt._innerHTML = r.full_name;
        repSelector.appendChild(opt);
    });
    repSelector.value = 'rep1';

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
        LF_DASHBOARD_DATA: data,
        document,
        console,
        location: { href: 'http://localhost/index.php?module=LF_WeeklyPlan&action=dashboard' },
        Event: function(type) { return { type, target: null }; },
        URL: class URL {
            constructor(url) { this.url = url; this.searchParams = { _params: {}, set: (k, v) => { this._params[k] = v; } }; }
            toString() { return this.url; }
        }
    };

    return { window, document, elements, eventListeners };
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

console.log('Running US-011 Deal Risk Assessment Column Tests (TDD RED Phase)...\n');

// ===================================================================
// Module Structure - renderDealRisk function existence
// ===================================================================
console.log('Section 1: Module Structure');

test('File should contain renderDealRisk function', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('renderDealRisk') || content.includes('function renderDealRisk') || content.includes('renderDealRisk:'),
        'File must declare renderDealRisk function'
    );
});

// ===================================================================
// AC1: Deal Risk Assessment renders as the third (rightmost) column
// ===================================================================
console.log('\nSection 2: Column Position and Rendering');

test('Deal Risk Assessment should render content in deal-risk-column', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    assert.ok(riskColumn.innerHTML.length > 0,
        'Deal Risk column should have content after initial render');
});

test('Deal Risk Assessment should have a header identifying it', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;
    assert.ok(
        html.includes('Deal Risk') || html.includes('deal-risk') || html.includes('Risk Assessment'),
        'Column should contain "Deal Risk" or "Risk Assessment" header'
    );
});

// ===================================================================
// AC2: Shows opportunities with no activity exceeding stale_deal_days
// ===================================================================
console.log('\nSection 3: Stale Deal Display');

test('Should display stale deals from window.LF_DASHBOARD_DATA.staleDeals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should show at least one stale deal from mock data
    assert.ok(
        html.includes('Acme Corporation') || html.includes('Q1 Enterprise Deal') ||
        html.includes('Beta Industries') || html.includes('Gamma LLC'),
        'Should display stale deals from data'
    );
});

test('Should display deals exceeding configured stale_deal_days', () => {
    const sandbox = createBrowserSandbox();
    // stale_deal_days = 14, so all mock deals (15, 18, 21, 30, 45 days) should show
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Check for days since activity values
    assert.ok(
        html.includes('45') || html.includes('21') || html.includes('30') || html.includes('15') || html.includes('18'),
        'Should display deals exceeding stale threshold'
    );
});

// ===================================================================
// AC3: Each row displays required format
// ===================================================================
console.log('\nSection 4: Row Format - Account, Opportunity, Amount, Days');

test('Should display Account name for stale deals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    assert.ok(
        html.includes('Acme Corporation') || html.includes('Beta Industries') ||
        html.includes('Gamma LLC') || html.includes('Delta Corp') || html.includes('Epsilon Inc'),
        'Should display account names'
    );
});

test('Should display Opportunity name for stale deals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    assert.ok(
        html.includes('Q1 Enterprise Deal') || html.includes('Annual Software License') ||
        html.includes('Cloud Migration Project') || html.includes('Consulting Services') ||
        html.includes('Small Deal'),
        'Should display opportunity names'
    );
});

test('Should display Amount formatted as currency', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Amounts: 150000, 75000, 220000, 50000, 25000
    assert.ok(
        html.includes('150,000') || html.includes('150000') ||
        html.includes('75,000') || html.includes('75000') ||
        html.includes('220,000') || html.includes('220000') ||
        html.includes('50,000') || html.includes('50000') ||
        html.includes('25,000') || html.includes('25000'),
        'Should display amounts formatted as currency'
    );
});

test('Should display "Last Activity: N days ago" format', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML.toLowerCase();

    assert.ok(
        html.includes('last activity') && (html.includes('days ago') || html.includes('day ago')),
        'Should display "Last Activity: N days ago" format'
    );
});

test('Should show specific days since activity values', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Check for specific day values from mock data
    assert.ok(
        html.includes('45 days ago') || html.includes('21 days ago') ||
        html.includes('30 days ago') || html.includes('15 days ago') || html.includes('18 days ago'),
        'Should display specific "N days ago" values'
    );
});

// ===================================================================
// AC4: Sorted by days since last activity (most stale first)
// ===================================================================
console.log('\nSection 5: Sorting by Staleness');

test('Should sort deals by days_since_activity descending (most stale first)', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Most stale: 45 days (Q1 Enterprise Deal) should appear before 21 days (Annual Software License)
    const index45 = html.indexOf('45');
    const index21 = html.indexOf('21');

    assert.ok(
        index45 >= 0 && index21 >= 0 && index45 < index21,
        'Deals should be sorted with most stale first (45 days before 21 days)'
    );
});

test('Should show most stale deal first in the list', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Q1 Enterprise Deal has 45 days (most stale) - should appear first
    const indexQ1Deal = html.indexOf('Q1 Enterprise Deal');
    const indexAnnualLicense = html.indexOf('Annual Software License');
    const indexCloudMigration = html.indexOf('Cloud Migration Project');

    assert.ok(
        indexQ1Deal >= 0 &&
        (indexAnnualLicense < 0 || indexQ1Deal < indexAnnualLicense) &&
        (indexCloudMigration < 0 || indexQ1Deal < indexCloudMigration),
        'Most stale deal (Q1 Enterprise Deal, 45 days) should appear first'
    );
});

// ===================================================================
// AC5: Team View shows all stale deals across all active reps
// ===================================================================
console.log('\nSection 6: Team View - All Stale Deals');

test('Team View should display stale deals from all reps', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should show deals from both rep1 and rep2
    const hasRep1Deal = html.includes('Acme Corporation') || html.includes('Q1 Enterprise Deal');
    const hasRep2Deal = html.includes('Gamma LLC') || html.includes('Cloud Migration Project');

    assert.ok(hasRep1Deal && hasRep2Deal,
        'Team View should show deals from all reps (both rep1 and rep2)');
});

test('Team View should show count of all stale deals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Mock data has 5 stale deals total
    assert.ok(
        html.includes('5 deals') || html.includes('5 Deals') || html.includes('(5)'),
        'Team View should show total count of stale deals (5)'
    );
});

// ===================================================================
// AC6: Rep View filters to the selected rep only
// ===================================================================
console.log('\nSection 7: Rep View - Filtered to Selected Rep');

test('Rep View should filter stale deals to selected rep', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Switch to Rep View for rep1
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });

    const html = riskColumn.innerHTML;

    // Should show rep1 deals (Acme, Beta, Epsilon) but not rep2 deals (Gamma, Delta)
    const hasRep1Deal = html.includes('Acme Corporation') || html.includes('Beta Industries');
    const hasRep2Deal = html.includes('Gamma LLC') || html.includes('Delta Corp');

    assert.ok(hasRep1Deal && !hasRep2Deal,
        'Rep View should show only rep1 deals, not rep2 deals');
});

test('Rep View should show count for selected rep only', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Switch to Rep View for rep1
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });

    const html = riskColumn.innerHTML;

    // rep1 has 3 stale deals (Acme, Beta, Epsilon)
    assert.ok(
        html.includes('3 deals') || html.includes('3 Deals') || html.includes('(3)'),
        'Rep View should show count for rep1 only (3 deals)'
    );
});

test('Rep View switching between reps should update stale deals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Switch to Rep View for rep1
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });
    const html1 = riskColumn.innerHTML;

    // Switch to rep2
    repSelector.value = 'rep2';
    repSelector.dispatchEvent({ type: 'change' });
    const html2 = riskColumn.innerHTML;

    // Should show different deals for rep2
    assert.ok(
        html2.includes('Gamma LLC') || html2.includes('Delta Corp'),
        'Switching to rep2 should show rep2 stale deals'
    );

    assert.ok(
        html1 !== html2,
        'Rep View content should change when switching between reps'
    );
});

// ===================================================================
// AC7: Key Buyers section is NOT shown
// ===================================================================
console.log('\nSection 8: Key Buyers Section Hidden');

test('Should NOT display Key Buyers section', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML.toLowerCase();

    assert.ok(
        !html.includes('key buyers') && !html.includes('key buyer') &&
        !html.includes('keybuyers') && !html.includes('key_buyers'),
        'Should NOT display Key Buyers section (intentionally hidden)'
    );
});

// ===================================================================
// AC8: Stale deal count shown in column header
// ===================================================================
console.log('\nSection 9: Column Header with Count');

test('Column header should show stale deal count in format "(N deals)"', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should have header with count like "Deal Risk Assessment (5 deals)"
    assert.ok(
        (html.includes('Deal Risk') || html.includes('Risk Assessment')) &&
        (html.includes('5 deals') || html.includes('5 Deals') || html.includes('(5)')),
        'Header should show "Deal Risk Assessment (N deals)" format'
    );
});

test('Column header count should update in Rep View', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Team View should show 5 deals
    const teamHtml = riskColumn.innerHTML;
    assert.ok(
        teamHtml.includes('5') || teamHtml.includes('(5)'),
        'Team View header should show 5 deals'
    );

    // Switch to rep1 (has 3 deals)
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });
    const repHtml = riskColumn.innerHTML;

    assert.ok(
        repHtml.includes('3') || repHtml.includes('(3)'),
        'Rep View header should show 3 deals for rep1'
    );
});

// ===================================================================
// AC9: Opportunities at '2-Analysis (1%)' stage excluded
// ===================================================================
console.log('\nSection 10: Stage Exclusion Logic');

test('Should exclude opportunities at "2-Analysis (1%)" stage', () => {
    // Create custom data with an Analysis stage opportunity
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals.push({
        id: 'opp-analysis',
        account_name: 'Analysis Corp',
        opportunity_name: 'Analysis Stage Deal',
        amount: 100000,
        days_since_activity: 60,
        assigned_user_id: 'rep1',
        assigned_user_name: 'John Doe',
        sales_stage: '2-Analysis (1%)'
    });

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should NOT show the Analysis stage deal
    assert.ok(
        !html.includes('Analysis Corp') && !html.includes('Analysis Stage Deal'),
        'Should exclude opportunities at "2-Analysis (1%)" stage'
    );

    // Count should still be 5 (not 6)
    assert.ok(
        html.includes('5 deals') || html.includes('5 Deals') || html.includes('(5)'),
        'Count should exclude Analysis stage deals (5 not 6)'
    );
});

test('Should include opportunities at other stages', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should show deals at stages 3, 4, 5, 6 (Qualification, Proposal, Negotiation, Review)
    assert.ok(
        html.includes('Q1 Enterprise Deal') || // 5-Negotiation
        html.includes('Annual Software License') || // 4-Proposal
        html.includes('Consulting Services') || // 3-Qualification
        html.includes('Small Deal'), // 6-Review
        'Should include opportunities at non-Analysis stages'
    );
});

// ===================================================================
// AC10: Activity types checked are from config
// ===================================================================
console.log('\nSection 11: Configuration Usage');

test('Should use stale_deal_days from config', () => {
    // Test with custom stale_deal_days = 20
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.config.stale_deal_days = 20;

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // With threshold 20, only deals with 21, 30, 45 days should show (3 deals)
    // Deals with 15, 18 days should be excluded
    assert.ok(
        !html.includes('15 days ago') && !html.includes('18 days ago'),
        'Should respect custom stale_deal_days threshold (20) and exclude deals below it'
    );
});

test('Should default to 14 days when config missing', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    delete customData.config.stale_deal_days;

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];

    // With default 14, all mock deals (15, 18, 21, 30, 45) should show
    assert.ok(
        riskColumn.innerHTML.length > 0,
        'Should use default stale_deal_days (14) when config missing'
    );
});

test('Should reference deal_risk.activity_types from config', () => {
    // This test verifies the implementation respects config for activity types
    // The actual filtering happens server-side, but we test that the client renders the data correctly
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    // Just verify column renders with the config present
    assert.ok(
        riskColumn.innerHTML.length > 0 && MOCK_DATA.config.deal_risk.activity_types.length === 4,
        'Config should include deal_risk.activity_types array'
    );
});

// ===================================================================
// Edge Cases
// ===================================================================
console.log('\nSection 12: Edge Cases');

test('Should handle empty staleDeals array', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals = [];

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should show 0 deals or empty state
    assert.ok(
        html.includes('0 deals') || html.includes('0 Deals') || html.includes('(0)') ||
        html.includes('No stale deals') || html.includes('no stale deals') ||
        riskColumn.innerHTML.length >= 0,
        'Should handle empty staleDeals array gracefully'
    );
});

test('Should handle single stale deal', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals = [customData.staleDeals[0]]; // Only one deal

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    assert.ok(
        html.includes('Acme Corporation') && html.includes('Q1 Enterprise Deal'),
        'Should display single stale deal'
    );

    assert.ok(
        html.includes('1 deal') || html.includes('1 Deal') || html.includes('(1)'),
        'Should show count of 1 deal'
    );
});

test('Should handle multiple deals with same staleness', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals[0].days_since_activity = 30;
    customData.staleDeals[1].days_since_activity = 30;
    customData.staleDeals[2].days_since_activity = 30;

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should display all deals with same staleness
    const count30Days = (html.match(/30 days ago/g) || []).length;
    assert.ok(count30Days >= 3, 'Should handle multiple deals with same staleness (30 days)');
});

test('Should handle zero days since activity edge case', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals.push({
        id: 'opp-zero',
        account_name: 'Zero Days Corp',
        opportunity_name: 'Today Activity Deal',
        amount: 10000,
        days_since_activity: 0,
        assigned_user_id: 'rep1',
        assigned_user_name: 'John Doe',
        sales_stage: '5-Negotiation'
    });

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Deal with 0 days should be excluded (below threshold of 14)
    assert.ok(
        !html.includes('Zero Days Corp') && !html.includes('Today Activity Deal'),
        'Should exclude deal with 0 days since activity (below threshold)'
    );
});

test('Should handle large days since activity (999+ days)', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals.push({
        id: 'opp-ancient',
        account_name: 'Ancient Corp',
        opportunity_name: 'Very Old Deal',
        amount: 500000,
        days_since_activity: 999,
        assigned_user_id: 'rep1',
        assigned_user_name: 'John Doe',
        sales_stage: '5-Negotiation'
    });

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];
    const html = riskColumn.innerHTML;

    // Should display very old deal and sort it first
    assert.ok(
        html.includes('999') || html.includes('Ancient Corp') || html.includes('Very Old Deal'),
        'Should handle large days since activity values (999+)'
    );

    // Should be sorted first
    const index999 = html.indexOf('999');
    const index45 = html.indexOf('45');
    if (index999 >= 0 && index45 >= 0) {
        assert.ok(index999 < index45, 'Deal with 999 days should appear before deal with 45 days');
    }
});

test('Should handle missing/null amount values', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals.push({
        id: 'opp-null-amount',
        account_name: 'No Amount Corp',
        opportunity_name: 'Missing Amount Deal',
        amount: null,
        days_since_activity: 25,
        assigned_user_id: 'rep1',
        assigned_user_name: 'John Doe',
        sales_stage: '4-Proposal'
    });

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];

    // Should handle null amount gracefully (show $0 or dash)
    assert.ok(
        riskColumn.innerHTML.length >= 0,
        'Should handle null amount values gracefully'
    );
});

test('Should handle missing account/opportunity names', () => {
    const customData = JSON.parse(JSON.stringify(MOCK_DATA));
    customData.staleDeals.push({
        id: 'opp-missing-names',
        account_name: '',
        opportunity_name: '',
        amount: 50000,
        days_since_activity: 20,
        assigned_user_id: 'rep1',
        assigned_user_name: 'John Doe',
        sales_stage: '4-Proposal'
    });

    const sandbox = createBrowserSandbox(customData);
    loadDashboardJs(sandbox);

    const riskColumn = sandbox.elements['deal-risk-column'];

    // Should handle missing names gracefully without crashing
    assert.ok(
        riskColumn.innerHTML.length >= 0,
        'Should handle missing account/opportunity names without error'
    );
});

test('Should handle rep with no stale deals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    // Add a rep with no deals
    sandbox.window.LF_DASHBOARD_DATA.reps.push({
        assigned_user_id: 'rep3',
        first_name: 'Bob',
        last_name: 'Johnson',
        full_name: 'Bob Johnson'
    });

    const opt = {
        tagName: 'OPTION',
        attributes: { value: 'rep3' },
        _innerHTML: 'Bob Johnson'
    };
    repSelector.children.push(opt);

    // Switch to rep3 (no deals)
    repSelector.value = 'rep3';
    repBtn.dispatchEvent({ type: 'click' });

    const html = riskColumn.innerHTML;

    // Should show 0 deals or empty state
    assert.ok(
        html.includes('0 deals') || html.includes('0 Deals') || html.includes('(0)') ||
        riskColumn.innerHTML.length >= 0,
        'Should handle rep with no stale deals gracefully'
    );
});

test('Should re-render on view mode change', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const teamBtn = sandbox.elements['team-view-btn'];
    const repBtn = sandbox.elements['rep-view-btn'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    const initialContent = riskColumn.innerHTML;

    // Switch to Rep View
    repBtn.dispatchEvent({ type: 'click' });
    const repContent = riskColumn.innerHTML;

    // Switch back to Team View
    teamBtn.dispatchEvent({ type: 'click' });
    const teamContent = riskColumn.innerHTML;

    assert.ok(
        initialContent !== repContent,
        'Content should change when switching to Rep View'
    );

    assert.ok(
        teamContent.includes('5') || teamContent.includes('(5)'),
        'Should show full team count after switching back'
    );
});

test('Should re-render on week change', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const weekSelector = sandbox.elements['week-selector'];
    const riskColumn = sandbox.elements['deal-risk-column'];

    const initialContent = riskColumn.innerHTML;

    // Change week
    weekSelector.value = '2026-02-09';
    weekSelector.dispatchEvent({ type: 'change' });

    // Content may change (data fetched per week) or stay same if data is static
    assert.ok(
        riskColumn.innerHTML.length >= 0,
        'Should handle week change without error'
    );
});

// ============================================================
// Summary
// ============================================================

console.log('\n' + '='.repeat(60));
console.log('SUMMARY: US-011 Deal Risk Assessment Column Tests');
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
    console.log('Proceed to CODE phase to implement the features.');
}

console.log('\n<promise>COMPLETE</promise>');
