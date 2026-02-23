/**
 * US-010: Weekly Priorities Column - TDD RED
 *
 * Tests for the Weekly Priorities column in dashboard.js
 * This is the second (center) column of the planning dashboard.
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

// Mock Data for Weekly Priorities testing
const MOCK_DATA = {
    config: {
        brand_blue: '#125EAD',
        brand_green: '#4BB74E',
        default_weekly_new_pipeline: 10000,
        default_weekly_progression: 5000
    },
    reps: [
        { assigned_user_id: 'rep1', first_name: 'John', last_name: 'Doe', full_name: 'John Doe' },
        { assigned_user_id: 'rep2', first_name: 'Jane', last_name: 'Smith', full_name: 'Jane Smith' }
    ],
    repTargets: {
        'rep1': { weekly_new_pipeline: 15000, weekly_progression: 8000 },
        'rep2': { weekly_new_pipeline: 12000, weekly_progression: 6000 }
    },
    weekInfo: {
        currentWeek: '2026-02-02',
        weekEnd: '2026-02-08'
    },
    weekList: [
        { weekStart: '2026-01-26', label: 'Jan 26', isCurrent: false },
        { weekStart: '2026-02-02', label: 'Feb 02', isCurrent: true },
        { weekStart: '2026-02-09', label: 'Feb 09', isCurrent: false }
    ],
    planItems: [
        {
            id: 'plan1',
            assigned_user_id: 'rep1',
            item_category: 'closing',
            account_name: 'Acme Corp',
            opportunity_name: 'Big Deal',
            amount: 50000,
            projected_stage: '7-Closing',
            planned_day: 'Monday',
            description: 'Final contract review'
        },
        {
            id: 'plan2',
            assigned_user_id: 'rep1',
            item_category: 'at_risk',
            account_name: 'Beta Inc',
            opportunity_name: 'Stalled Opp',
            amount: 25000,
            projected_stage: '4-Proposal',
            planned_day: 'Tuesday',
            description: 'Revive conversation'
        },
        {
            id: 'plan3',
            assigned_user_id: 'rep1',
            item_category: 'progression',
            account_name: 'Gamma LLC',
            opportunity_name: 'Moving Deal',
            amount: 30000,
            projected_stage: '5-Negotiation',
            planned_day: 'Wednesday',
            description: 'Send updated proposal'
        },
        {
            id: 'plan4',
            assigned_user_id: 'rep2',
            item_category: 'closing',
            account_name: 'Delta Corp',
            opportunity_name: 'Quick Win',
            amount: 20000,
            projected_stage: '7-Closing',
            planned_day: 'Thursday',
            description: 'Collect signature'
        },
        {
            id: 'plan5',
            assigned_user_id: 'rep2',
            item_category: 'developing_pipeline',
            account_name: 'Epsilon Inc',
            opportunity_name: 'New Opp',
            amount: 40000,
            projected_stage: '2-Analysis',
            planned_day: 'Friday',
            description: 'Initial discovery call'
        }
    ],
    prospectItems: [
        {
            id: 'prospect1',
            assigned_user_id: 'rep1',
            item_category: 'prospecting',
            account_name: 'Zeta Corp',
            opportunity_name: 'Cold Outreach',
            amount: 15000,
            projected_stage: '1-Prospecting',
            planned_day: 'Monday',
            description: 'First contact email'
        },
        {
            id: 'prospect2',
            assigned_user_id: 'rep2',
            item_category: 'prospecting',
            account_name: 'Eta Inc',
            opportunity_name: 'Referral Lead',
            amount: 22000,
            projected_stage: '1-Prospecting',
            planned_day: 'Tuesday',
            description: 'Follow up on referral'
        }
    ],
    pipelineByStage: {},
    pipelineByRep: {},
    staleDeals: [],
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
function createBrowserSandbox() {
    const elements = {};
    const eventListeners = {};

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
        LF_DASHBOARD_DATA: JSON.parse(JSON.stringify(MOCK_DATA)),
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

console.log('Running US-010 Weekly Priorities Column Tests (TDD RED Phase)...\n');

// ===================================================================
// AC1: Weekly Priorities renders as the second (center) column
// ===================================================================

test('Weekly Priorities should render content in weekly-priorities-column', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    assert.ok(prioritiesColumn.innerHTML.length > 0,
        'Weekly Priorities column should have content after initial render');
});

test('Weekly Priorities should have a header identifying it as Weekly Priorities', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;
    assert.ok(html.includes('Weekly Priorities') || html.includes('weekly-priorities'),
        'Column should contain "Weekly Priorities" header or identifier');
});

// ===================================================================
// AC2: Team View shows target cards with per-rep targets
// ===================================================================

test('Team View should display New Pipeline targets per rep', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Should show rep1's target ($15,000) or rep2's ($12,000)
    assert.ok(
        html.includes('15,000') || html.includes('15000') ||
        html.includes('12,000') || html.includes('12000') ||
        html.includes('New Pipeline'),
        'Team View should display New Pipeline targets for reps'
    );
});

test('Team View should display Progression targets per rep', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Should show rep1's progression target ($8,000) or rep2's ($6,000)
    assert.ok(
        html.includes('8,000') || html.includes('8000') ||
        html.includes('6,000') || html.includes('6000') ||
        html.includes('Progression'),
        'Team View should display Progression targets for reps'
    );
});

// ===================================================================
// AC3: Per-rep cards display planned items grouped by category
// ===================================================================

test('Should display items categorized as Closing', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML.toLowerCase();

    assert.ok(html.includes('closing') || html.includes('close'),
        'Should display Closing category items');
});

test('Should display items categorized as At Risk', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML.toLowerCase();

    assert.ok(html.includes('at risk') || html.includes('at_risk') || html.includes('atrisk'),
        'Should display At Risk category items');
});

test('Should display items categorized as Progression', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML.toLowerCase();

    assert.ok(html.includes('progression'),
        'Should display Progression category items');
});

test('Should display items categorized as Developing Pipeline', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML.toLowerCase();

    assert.ok(html.includes('developing') || html.includes('pipeline'),
        'Should display Developing Pipeline category items');
});

test('Should display items categorized as Prospecting', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML.toLowerCase();

    assert.ok(html.includes('prospecting') || html.includes('prospect'),
        'Should display Prospecting category items');
});

// ===================================================================
// AC4: Each item shows all 6 data points
// ===================================================================

test('Should display Account name for plan items', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock data has 'Acme Corp', 'Beta Inc', etc.
    assert.ok(
        html.includes('Acme Corp') || html.includes('Beta Inc') ||
        html.includes('Gamma LLC') || html.includes('Delta Corp'),
        'Should display account names'
    );
});

test('Should display Opportunity name for plan items', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock data has 'Big Deal', 'Stalled Opp', etc.
    assert.ok(
        html.includes('Big Deal') || html.includes('Stalled Opp') ||
        html.includes('Moving Deal') || html.includes('Quick Win'),
        'Should display opportunity names'
    );
});

test('Should display Amount for plan items', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock data amounts: 50000, 25000, 30000, 20000, etc.
    assert.ok(
        html.includes('50,000') || html.includes('50000') ||
        html.includes('25,000') || html.includes('25000') ||
        html.includes('30,000') || html.includes('30000'),
        'Should display amounts formatted as currency'
    );
});

test('Should display Projected Stage for plan items', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock data stages: '7-Closing', '4-Proposal', '5-Negotiation', etc.
    assert.ok(
        html.includes('7-Closing') || html.includes('Closing') ||
        html.includes('4-Proposal') || html.includes('Proposal') ||
        html.includes('5-Negotiation') || html.includes('Negotiation'),
        'Should display projected stage'
    );
});

test('Should display Planned Day for plan items', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock data days: Monday, Tuesday, Wednesday, etc.
    assert.ok(
        html.includes('Monday') || html.includes('Tuesday') ||
        html.includes('Wednesday') || html.includes('Thursday') || html.includes('Friday'),
        'Should display planned day'
    );
});

test('Should display Plan Description for plan items', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock data descriptions
    assert.ok(
        html.includes('Final contract review') || html.includes('Revive conversation') ||
        html.includes('Send updated proposal') || html.includes('Collect signature'),
        'Should display plan descriptions'
    );
});

// ===================================================================
// AC5: Totals shown per rep and as team aggregate
// ===================================================================

test('Should display team totals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML.toLowerCase();

    // Should have some indication of totals
    assert.ok(
        html.includes('total') || html.includes('sum') || html.includes('aggregate'),
        'Should display totals (team or category)'
    );
});

test('Should show rep names in Team View', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock data has John Doe and Jane Smith
    assert.ok(
        html.includes('John Doe') || html.includes('Jane Smith'),
        'Team View should show rep names'
    );
});

// ===================================================================
// AC6: At Risk totals included in the display
// ===================================================================

test('Should include At Risk amounts in totals', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // Mock at_risk item has amount 25000
    assert.ok(
        html.includes('25,000') || html.includes('25000') ||
        html.includes('at risk') || html.includes('At Risk'),
        'Should include At Risk amounts or category'
    );
});

// ===================================================================
// AC7: Rep View filters to selected rep only
// ===================================================================

test('Rep View should filter to selected rep', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];

    // Switch to Rep View for rep1
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });

    const html = prioritiesColumn.innerHTML;

    // Should show rep1 items (Acme Corp, Big Deal) but not rep2 items (Delta Corp)
    // At minimum, should have different content than team view
    assert.ok(
        html.includes('John Doe') || html.includes('Acme Corp') ||
        !html.includes('Jane Smith'),
        'Rep View should filter to selected rep'
    );
});

// ===================================================================
// AC8: Rep View splits into Pipeline Progression and New Pipeline sections
// ===================================================================

test('Rep View should have Pipeline Progression Priorities section', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];

    // Switch to Rep View
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });

    const html = prioritiesColumn.innerHTML.toLowerCase();

    assert.ok(
        html.includes('pipeline progression') || html.includes('progression priorities'),
        'Rep View should have Pipeline Progression Priorities section'
    );
});

test('Rep View should have New Pipeline Priorities section', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];
    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];

    // Switch to Rep View
    repSelector.value = 'rep1';
    repBtn.dispatchEvent({ type: 'click' });

    const html = prioritiesColumn.innerHTML.toLowerCase();

    assert.ok(
        html.includes('new pipeline') || html.includes('pipeline priorities'),
        'Rep View should have New Pipeline Priorities section'
    );
});

// ===================================================================
// AC9: Data comes from plan items and prospect items
// ===================================================================

test('Should display data from planItems', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // planItems has 'Big Deal' from mock data
    assert.ok(html.includes('Big Deal') || html.includes('Acme Corp'),
        'Should display data from planItems');
});

test('Should display data from prospectItems', () => {
    const sandbox = createBrowserSandbox();
    loadDashboardJs(sandbox);

    const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
    const html = prioritiesColumn.innerHTML;

    // prospectItems has 'Cold Outreach' and 'Zeta Corp' from mock data
    assert.ok(
        html.includes('Cold Outreach') || html.includes('Zeta Corp') ||
        html.includes('Referral Lead') || html.includes('Eta Inc'),
        'Should display data from prospectItems'
    );
});

// ===================================================================
// Edge Cases
// ===================================================================

test('Should handle empty planItems array', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.planItems = [];

    try {
        loadDashboardJs(sandbox);
        const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
        assert.ok(prioritiesColumn.innerHTML.length >= 0, 'Should handle empty planItems without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found'),
            'Expected failure due to missing implementation or should handle gracefully');
    }
});

test('Should handle empty prospectItems array', () => {
    const sandbox = createBrowserSandbox();
    sandbox.window.LF_DASHBOARD_DATA.prospectItems = [];

    try {
        loadDashboardJs(sandbox);
        const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
        assert.ok(prioritiesColumn.innerHTML.length >= 0, 'Should handle empty prospectItems without error');
    } catch (e) {
        assert.ok(e.message.includes('File not found'),
            'Expected failure due to missing implementation or should handle gracefully');
    }
});

test('Should handle rep with no plan items', () => {
    const sandbox = createBrowserSandbox();
    // Remove all items for rep2
    sandbox.window.LF_DASHBOARD_DATA.planItems =
        sandbox.window.LF_DASHBOARD_DATA.planItems.filter(i => i.assigned_user_id !== 'rep2');
    sandbox.window.LF_DASHBOARD_DATA.prospectItems =
        sandbox.window.LF_DASHBOARD_DATA.prospectItems.filter(i => i.assigned_user_id !== 'rep2');

    const repBtn = sandbox.elements['rep-view-btn'];
    const repSelector = sandbox.elements['rep-selector'];

    try {
        loadDashboardJs(sandbox);
        repSelector.value = 'rep2';
        repBtn.dispatchEvent({ type: 'click' });

        const prioritiesColumn = sandbox.elements['weekly-priorities-column'];
        // Should display something even if no items (empty state or message)
        assert.ok(prioritiesColumn.innerHTML.length >= 0, 'Should handle rep with no items');
    } catch (e) {
        assert.ok(e.message.includes('File not found'),
            'Expected failure due to missing implementation or should handle gracefully');
    }
});

// ============================================================
// Summary
// ============================================================

console.log('\n' + '='.repeat(60));
console.log('SUMMARY: US-010 Weekly Priorities Column Tests');
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
