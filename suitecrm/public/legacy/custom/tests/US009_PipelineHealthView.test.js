// custom/tests/US009_PipelineHealthView.test.js
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

function createBrowserSandbox(data = {}) {
    const elements = {};
    const eventListeners = {};
    const consoleLogs = [];

    // Basic DOM Element Mock
    function createElement(tag, attrs = {}) {
        return {
            tagName: tag.toUpperCase(),
            attributes: { ...attrs },
            children: [],
            style: {},
            innerHTML: '',
            textContent: '',
            
            getAttribute(name) { return this.attributes[name]; },
            setAttribute(name, val) { this.attributes[name] = val; },
            get id() { return this.attributes.id || ''; },
            get className() { return this.attributes.class || ''; },
            set className(val) { this.attributes.class = val; },
            
            appendChild(child) { this.children.push(child); return child; },
            addEventListener(type, handler) {
                if (!this._listeners) this._listeners = {};
                if (!this._listeners[type]) this._listeners[type] = [];
                this._listeners[type].push(handler);
            },
            
            // Basic query support
            querySelector(selector) {
                // Not implementing full selector engine here
                return null; 
            }
        };
    }

    const container = createElement('div', { id: 'pipeline-health-column' });
    elements['pipeline-health-column'] = container;
    
    // Add other containers required by renderAllColumns to avoid errors
    elements['weekly-priorities-column'] = createElement('div', { id: 'weekly-priorities-column' });
    elements['deal-risk-column'] = createElement('div', { id: 'deal-risk-column' });
    
    // View controls
    elements['team-view-btn'] = createElement('button', { id: 'team-view-btn', class: 'lf-btn active' });
    elements['rep-view-btn'] = createElement('button', { id: 'rep-view-btn', class: 'lf-btn' });
    elements['rep-selector'] = createElement('select', { id: 'rep-selector' });
    elements['week-selector'] = createElement('select', { id: 'week-selector' });

    const document = {
        getElementById(id) { return elements[id] || null; },
        querySelector(sel) { return null; }, // Mock if needed
        createElement,
        addEventListener(type, handler) {
            if (!eventListeners[type]) eventListeners[type] = [];
            eventListeners[type].push(handler);
        }
    };

    const window = {
        LF_DASHBOARD_DATA: data,
        document,
        addEventListener: document.addEventListener.bind(document),
        console: {
            log: (...args) => consoleLogs.push(args.join(' ')),
            error: (...args) => consoleLogs.push('ERROR: ' + args.join(' '))
        }
    };

    return { window, document, elements, eventListeners, container };
}

function loadDashboardJs(sandbox) {
    if (!fs.existsSync(jsFile)) throw new Error(`File not found: ${jsFile}`);
    const content = fs.readFileSync(jsFile, 'utf8');
    const context = vm.createContext({
        window: sandbox.window,
        document: sandbox.document,
        console: sandbox.window.console,
        Intl: Intl,
        Math: Math,
        Object: Object,
        Array: Array,
        JSON: JSON,
        String: String
    });
    vm.runInContext(content, context);
    
    // Trigger DOMContentLoaded
    if (sandbox.eventListeners['DOMContentLoaded']) {
        sandbox.eventListeners['DOMContentLoaded'].forEach(h => h());
    }
}

// ============================================================
// TESTS
// ============================================================
console.log('Running US-009 Tests: Pipeline Health Check Logic & View');

// Config and Data Mock
const mockConfig = {
    default_annual_quota: '100000',
    pipeline_coverage_multiplier: '3.0'
};

const mockReps = [
    { id: 'r1', name: 'Rep 1', assigned_user_id: 'r1', full_name: 'Rep One' },
    { id: 'r2', name: 'Rep 2', assigned_user_id: 'r2', full_name: 'Rep Two' }
];

const mockPipelineByStage = {
    'Prospecting': { count: 5, amount: 20000 },
    'Qualification': { count: 3, amount: 30000 },
    'Proposal': { count: 2, amount: 50000 }
}; // Total: 100,000

const mockData = {
    config: mockConfig,
    reps: mockReps,
    closedYtd: { team: 25000, byRep: { 'r1': 15000, 'r2': 10000 } },
    pipelineByStage: mockPipelineByStage,
    pipelineByRep: {
        'r1': { byStage: mockPipelineByStage }, // Simplify
        'r2': { byStage: {} }
    },
    repTargets: {
        'r1': { quota: '100000' },
        'r2': { quota: '120000' }
    }
};

// 1. Team View: Render "Team Quota: N reps x $X = $Y"
test('should render Team Quota calculation correctly in Team View', () => {
    const sandbox = createBrowserSandbox(mockData);
    // Default view is team
    loadDashboardJs(sandbox);
    
    const html = sandbox.container.innerHTML;
    
    // Logic: 2 reps. Rep 1: 100k (from repTargets). Rep 2: 120k (from repTargets). Total 220k.
    // Or if repTargets missing, default * reps.
    // Requirement says: "Team Quota: N reps x $X = $Y" showing number of active reps times default OR sum of custom.
    // Let's assume implementation sums active quotas.
    // "Team Quota: 2 reps... $220,000"
    
    assert.ok(html.includes('Team Quota'), 'Should contain "Team Quota" label');
    assert.ok(html.includes('2 reps'), 'Should mention "2 reps"');
    assert.ok(html.includes('220,000') || html.includes('220000'), 'Should show total quota $220,000');
});

// 2. Target Calculation: (Quota - Closed YTD) x multiplier
test('should calculate and display Target correctly', () => {
    const sandbox = createBrowserSandbox(mockData);
    loadDashboardJs(sandbox);
    
    const html = sandbox.container.innerHTML;
    
    // Quota: 220,000
    // Closed YTD (Team): 25,000
    // Gap to Quota: 195,000
    // Multiplier: 3.0
    // Target: 195,000 * 3 = 585,000
    
    assert.ok(html.includes('Target') || html.includes('Coverage Target'), 'Should mention Target');
    assert.ok(html.includes('585,000') || html.includes('585000'), 'Should display calculated target $585,000');
});

// 3. Gap to Target: Separate callout
test('should display Gap to Target as separate callout', () => {
    const sandbox = createBrowserSandbox(mockData);
    loadDashboardJs(sandbox);
    
    const html = sandbox.container.innerHTML;
    
    // Target: 585,000
    // Current Pipeline: 100,000
    // Gap: 485,000
    
    assert.ok(html.includes('Gap to Target'), 'Should contain "Gap to Target" label');
    assert.ok(html.includes('485,000') || html.includes('485000'), 'Should display gap $485,000');
    
    // Requirement: "red styling" - check for style or class (implementation detail, but crucial for acceptance)
    // We can check if it's in a div with color/style
    // Ideally, implementation adds a class like 'lf-gap-alert' or style='color: ...'
    // I'll check for the text first. The "NOT a segment" part is implied by separate text.
});

// 4. Coverage Ratio
test('should display Coverage Ratio', () => {
    const sandbox = createBrowserSandbox(mockData);
    loadDashboardJs(sandbox);
    
    const html = sandbox.container.innerHTML;
    
    // Pipeline: 100,000
    // Remaining Quota (Target / Multiplier? No, usually "Remaining Quota" implies Quota - Closed)
    // Requirement says: "Coverage Ratio: Current Pipeline / Remaining Quota"
    // Remaining Quota = 220,000 - 25,000 = 195,000
    // Ratio = 100,000 / 195,000 = 0.51
    
    assert.ok(html.includes('Coverage Ratio'), 'Should contain "Coverage Ratio" label');
    assert.ok(html.includes('0.51') || html.includes('0.5x'), 'Should display coverage ratio 0.51');
});

// 5. Stacked Bar Chart (HTML/CSS)
test('should render Stacked Bar Chart using CSS widths', () => {
    const sandbox = createBrowserSandbox(mockData);
    loadDashboardJs(sandbox);
    
    const html = sandbox.container.innerHTML;
    
    // Pipeline Total: 100,000
    // Prospecting: 20,000 (20%)
    // Qualification: 30,000 (30%)
    // Proposal: 50,000 (50%)
    
    // Check for styles
    assert.ok(html.includes('width: 20%') || html.includes('width: 20.0%'), 'Should have bar segment with 20% width');
    assert.ok(html.includes('width: 30%') || html.includes('width: 30.0%'), 'Should have bar segment with 30% width');
    assert.ok(html.includes('width: 50%') || html.includes('width: 50.0%'), 'Should have bar segment with 50% width');
    
    // Check for NO table (the old implementation used a table)
    // Requirement: "Stacked bar chart... NO charting library" implies divs
    // If table exists, it might be for legend, but main viz should be bars.
    // I'll check specifically for the bar container structure if possible, but widths are good proxy.
});

// 6. Pipeline by Rep (Stacked Bars)
test('should render Pipeline by Rep section', () => {
    const sandbox = createBrowserSandbox(mockData);
    loadDashboardJs(sandbox);
    
    const html = sandbox.container.innerHTML;
    
    assert.ok(html.includes('Pipeline by Rep'), 'Should contain "Pipeline by Rep" section');
    assert.ok(html.includes('Rep 1'), 'Should show Rep 1 name');
    assert.ok(html.includes('Rep 2'), 'Should show Rep 2 name');
    
    // Rep 1 has pipeline, Rep 2 empty
    // Check Rep 1 bar existence (implied by content)
});


// ============================================================
// Run Summary
// ============================================================
console.log(`\nSUMMARY:`);
console.log(`Passed: ${passCount}`);
console.log(`Failed: ${failCount}`);
process.exit(failCount > 0 ? 1 : 0);
