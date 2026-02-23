/**
 * US-006: Planning JS - Health Summary, Totals Color Coding, Save/Submit Tests
 *
 * Tests that planning.js has been extended to include:
 *
 *   1. Pipeline Health Summary calculations:
 *      - Remaining Quota = annual_quota - closed_ytd
 *      - Pipeline Target = remaining_quota * coverage_multiplier
 *      - Current Pipeline Total = sum of all open pipeline amounts
 *      - Gap to Target = pipeline_target - current_pipeline
 *      - Coverage Ratio = current_pipeline / remaining_quota
 *      - Red styling on gap when pipeline < target
 *
 *   2. Totals row color coding:
 *      - Compare each total against configured weekly targets
 *      - Green if meeting target, red if below
 *
 *   3. Save functionality:
 *      - collectFormData() gathers all pipeline, developing, and prospecting data
 *      - savePlan() calls fetch() to save_json endpoint
 *      - CSRF token included via SUGAR.csrf.form_token
 *      - Success/error message display without page reload
 *
 *   4. Updates Complete button:
 *      - Sends submit action to save_json endpoint
 *      - Sets plan status to 'submitted'
 *
 * These tests MUST FAIL until the implementation is updated.
 *
 * Test approach: Structural pattern matching + DOM sandbox for behavior testing.
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
const jsFile = path.join(customDir, 'modules', 'LF_WeeklyPlan', 'js', 'planning.js');

// Stage probabilities matching the install.php config
const STAGE_PROBS = {
    '2-Analysis (1%)': 1,
    '3-Confirmation (10%)': 10,
    '5-Specifications (30%)': 30,
    '6-Solution (60%)': 60,
    '7-Closing (90%)': 90,
    'closed_won': 100
};

// Source types from config
const SOURCE_TYPES = ['Cold Call', 'Referral', 'Event', 'Partner', 'Inbound', 'Customer Visit', 'Other'];

// Weekly targets from config (defaults)
const WEEKLY_TARGETS = {
    closing: 20000,
    new_pipeline: 100000,
    progression: 100000
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

/**
 * Creates a minimal sandbox that simulates the browser environment.
 * Extended from US-005 sandbox with health summary elements,
 * save buttons, message containers, SUGAR.csrf, and fetch mock.
 */
function createBrowserSandbox(options = {}) {
    const {
        stageProbabilities = STAGE_PROBS,
        pipelineRows = [],
        developingPipelineRows = [],
        prospectingRows = [],
        sourceTypes = SOURCE_TYPES,
        weeklyTargets = WEEKLY_TARGETS,
        closedYtd = 0,
        annualQuota = 500000,
        coverageMultiplier = 4,
        currentPipelineTotal = 0
    } = options;

    const elements = {};
    const eventListeners = {};
    const removedElements = [];
    const fetchCalls = [];
    const alertMessages = [];

    // Minimal DOM element factory
    function createElement(tag, attrs = {}) {
        const el = {
            tagName: tag.toUpperCase(),
            attributes: { ...attrs },
            children: [],
            parentNode: null,
            style: {},
            _eventListeners: {},
            _innerHTML: '',
            _textContent: '',

            getAttribute(name) {
                return this.attributes[name] !== undefined ? this.attributes[name] : null;
            },
            setAttribute(name, value) {
                this.attributes[name] = String(value);
            },
            get id() { return this.attributes.id || ''; },
            set id(v) { this.attributes.id = v; },
            get className() { return this.attributes.class || ''; },
            set className(v) { this.attributes.class = v; },
            get value() { return this.attributes.value !== undefined ? this.attributes.value : ''; },
            set value(v) { this.attributes.value = String(v); },
            get textContent() { return this._textContent; },
            set textContent(v) { this._textContent = String(v); },
            get innerHTML() { return this._innerHTML; },
            set innerHTML(v) { this._innerHTML = v; },
            get name() { return this.attributes.name || ''; },
            set name(v) { this.attributes.name = v; },
            get type() { return this.attributes.type || ''; },
            set type(v) { this.attributes.type = v; },
            get dataset() {
                const ds = {};
                for (const [k, v] of Object.entries(this.attributes)) {
                    if (k.startsWith('data-')) {
                        const camel = k.slice(5).replace(/-([a-z])/g, (_, c) => c.toUpperCase());
                        ds[camel] = v;
                    }
                }
                return ds;
            },

            addEventListener(type, handler) {
                if (!this._eventListeners[type]) this._eventListeners[type] = [];
                this._eventListeners[type].push(handler);
            },
            dispatchEvent(event) {
                event.target = event.target || this;
                event.currentTarget = this;
                const handlers = this._eventListeners[event.type] || [];
                handlers.forEach(h => h(event));
                if (event.bubbles && this.parentNode) {
                    this.parentNode.dispatchEvent(event);
                }
            },
            querySelector(selector) {
                return querySelectorInTree(this, selector);
            },
            querySelectorAll(selector) {
                return querySelectorAllInTree(this, selector);
            },
            appendChild(child) {
                child.parentNode = this;
                this.children.push(child);
                return child;
            },
            removeChild(child) {
                const idx = this.children.indexOf(child);
                if (idx !== -1) {
                    this.children.splice(idx, 1);
                    child.parentNode = null;
                    removedElements.push(child);
                }
                return child;
            },
            remove() {
                if (this.parentNode) {
                    this.parentNode.removeChild(this);
                }
            },
            cloneNode(deep) {
                const clone = createElement(this.tagName, { ...this.attributes });
                clone._textContent = this._textContent;
                clone._innerHTML = this._innerHTML;
                if (deep) {
                    this.children.forEach(child => {
                        clone.appendChild(child.cloneNode(true));
                    });
                }
                return clone;
            },
            closest(selector) {
                let current = this;
                while (current) {
                    if (matchesSelector(current, selector)) return current;
                    current = current.parentNode;
                }
                return null;
            },
            matches(selector) {
                return matchesSelector(this, selector);
            },
            get selectedOptions() {
                return this.children.filter(c => c.attributes.selected);
            }
        };
        return el;
    }

    function matchesSelector(el, selector) {
        if (!el || !el.tagName) return false;
        if (selector.startsWith('#')) {
            return el.id === selector.slice(1);
        }
        if (selector.startsWith('.')) {
            const cls = selector.slice(1);
            return (el.className || '').split(/\s+/).includes(cls);
        }
        if (/^[a-z]+$/i.test(selector)) {
            return el.tagName.toLowerCase() === selector.toLowerCase();
        }
        const attrMatch = selector.match(/\[(\w[\w-]*)(?:="([^"]*)")?\]/);
        if (attrMatch) {
            const [, attr, val] = attrMatch;
            if (val !== undefined) {
                return el.getAttribute(attr) === val;
            }
            return el.getAttribute(attr) !== null;
        }
        return false;
    }

    function querySelectorInTree(root, selector) {
        const results = querySelectorAllInTree(root, selector);
        return results.length > 0 ? results[0] : null;
    }

    function querySelectorAllInTree(root, selector) {
        // Handle multiple selectors separated by commas
        const selectors = selector.split(',').map(s => s.trim());
        const allResults = [];

        for (const sel of selectors) {
            const parts = sel.split(/\s+/);

            if (parts.length === 0) continue;
            if (parts.length === 1) {
                // Single part: find all matching descendants
                const queue = [...(root.children || [])];
                while (queue.length) {
                    const node = queue.shift();
                    if (matchesSelector(node, parts[0])) allResults.push(node);
                    queue.push(...(node.children || []));
                }
            } else {
                // Multiple parts: handle descendant selector
                const firstPart = parts[0];
                const remainingSelector = parts.slice(1).join(' ');

                // Find elements matching first part
                const firstMatches = querySelectorAllInTree(root, firstPart);

                // For each match, search within for remaining parts
                for (const match of firstMatches) {
                    allResults.push(...querySelectorAllInTree(match, remainingSelector));
                }
            }
        }

        return allResults;
    }

    // ---- Build Existing Pipeline Table ----
    const pipelineTableBody = createElement('tbody');
    pipelineRows.forEach((row, idx) => {
        const oppId = row.oppId || `opp-${idx}`;
        const tr = createElement('tr', { 'data-opportunity-id': oppId });

        const amountTd = createElement('td', { class: 'amount', 'data-amount': String(row.amount || 0) });
        amountTd._textContent = String(row.amount || 0);
        tr.appendChild(amountTd);

        const stageTd = createElement('td', { class: 'current-stage', 'data-stage': row.currentStage || '' });
        stageTd._textContent = row.currentStage || '';
        tr.appendChild(stageTd);

        const projSelect = createElement('select', { name: `projected_stage[${oppId}]`, class: 'projected-stage-select' });
        const emptyOpt = createElement('option', { value: '' });
        emptyOpt._textContent = '-- Select --';
        projSelect.appendChild(emptyOpt);
        ['3-Confirmation (10%)', '5-Specifications (30%)', '6-Solution (60%)', '7-Closing (90%)'].forEach(s => {
            const opt = createElement('option', { value: s });
            opt._textContent = s;
            if (row.projectedStage === s) { opt.attributes.selected = 'selected'; projSelect.value = s; }
            projSelect.appendChild(opt);
        });
        if (row.projectedStage) projSelect.attributes.value = row.projectedStage;
        const projTd = createElement('td');
        projTd.appendChild(projSelect);
        tr.appendChild(projTd);

        const catSelect = createElement('select', { name: `category[${oppId}]`, class: 'category-select' });
        ['closing', 'at_risk', 'progression', 'skip'].forEach(cat => {
            const opt = createElement('option', { value: cat });
            opt._textContent = cat;
            if (row.category === cat) { opt.attributes.selected = 'selected'; catSelect.value = cat; }
            catSelect.appendChild(opt);
        });
        if (row.category) catSelect.attributes.value = row.category;
        const catTd = createElement('td');
        catTd.appendChild(catSelect);
        tr.appendChild(catTd);

        const daySelect = createElement('select', { name: `day[${oppId}]`, class: 'day-select' });
        const dayTd = createElement('td');
        dayTd.appendChild(daySelect);
        tr.appendChild(dayTd);

        const planInput = createElement('input', { type: 'text', name: `plan[${oppId}]`, value: row.planDesc || '' });
        const planTd = createElement('td');
        planTd.appendChild(planInput);
        tr.appendChild(planTd);

        const progTd = createElement('td', { class: 'pipeline-progression', 'data-value': '0' });
        progTd._textContent = '0';
        tr.appendChild(progTd);

        pipelineTableBody.appendChild(tr);
    });

    const pipelineTable = createElement('table', { id: 'pipeline-table', class: 'list view table-responsive' });
    pipelineTable.appendChild(pipelineTableBody);

    // ---- Build Developing Pipeline Table ----
    const devPipelineTableBody = createElement('tbody');
    developingPipelineRows.forEach((row, idx) => {
        const oppId = row.oppId || `dev-opp-${idx}`;
        const tr = createElement('tr', {
            'data-opportunity-id': oppId,
            class: 'developing-pipeline-row'
        });

        const amountTd = createElement('td', { class: 'dev-amount', 'data-amount': String(row.amount || 0) });
        amountTd._textContent = String(row.amount || 0);
        tr.appendChild(amountTd);

        const projSelect = createElement('select', {
            name: `dev_projected_stage[${oppId}]`,
            class: 'dev-projected-stage-select'
        });
        const emptyOpt = createElement('option', { value: '' });
        emptyOpt._textContent = '-- Select --';
        projSelect.appendChild(emptyOpt);
        ['3-Confirmation (10%)', '5-Specifications (30%)', '6-Solution (60%)', '7-Closing (90%)'].forEach(s => {
            const opt = createElement('option', { value: s });
            opt._textContent = s;
            if (row.projectedStage === s) { opt.attributes.selected = 'selected'; projSelect.value = s; }
            projSelect.appendChild(opt);
        });
        if (row.projectedStage) projSelect.attributes.value = row.projectedStage;
        const projTd = createElement('td');
        projTd.appendChild(projSelect);
        tr.appendChild(projTd);

        const daySelect = createElement('select', { name: `dev_day[${oppId}]`, class: 'dev-day-select' });
        const dayTd = createElement('td');
        dayTd.appendChild(daySelect);
        tr.appendChild(dayTd);

        const planInput = createElement('input', { type: 'text', name: `dev_plan[${oppId}]`, value: '' });
        const planTd = createElement('td');
        planTd.appendChild(planInput);
        tr.appendChild(planTd);

        devPipelineTableBody.appendChild(tr);
    });

    const devPipelineTable = createElement('table', { id: 'developing-pipeline-table', class: 'list view table-responsive' });
    devPipelineTable.appendChild(devPipelineTableBody);

    // ---- Build Prospecting Table ----
    const prospectTableBody = createElement('tbody');
    prospectingRows.forEach((row, idx) => {
        const tr = createElement('tr', { class: 'prospecting-row', 'data-prospect-index': String(idx) });

        const sourceSelect = createElement('select', {
            name: `prospect_source[${idx}]`,
            class: 'prospect-source'
        });
        const emptyOpt = createElement('option', { value: '' });
        emptyOpt._textContent = '-- Select --';
        sourceSelect.appendChild(emptyOpt);
        sourceTypes.forEach(st => {
            const opt = createElement('option', { value: st });
            opt._textContent = st;
            if (row.source === st) { opt.attributes.selected = 'selected'; sourceSelect.value = st; }
            sourceSelect.appendChild(opt);
        });
        if (row.source) sourceSelect.attributes.value = row.source;
        const sourceTd = createElement('td');
        sourceTd.appendChild(sourceSelect);
        tr.appendChild(sourceTd);

        const daySelect = createElement('select', {
            name: `prospect_day[${idx}]`,
            class: 'prospect-day'
        });
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].forEach(d => {
            const opt = createElement('option', { value: d });
            opt._textContent = d.charAt(0).toUpperCase() + d.slice(1);
            daySelect.appendChild(opt);
        });
        const dayTd = createElement('td');
        dayTd.appendChild(daySelect);
        tr.appendChild(dayTd);

        const amountInput = createElement('input', {
            type: 'number',
            name: `prospect_amount[${idx}]`,
            class: 'prospect-amount',
            value: String(row.amount || 0)
        });
        const amountTd = createElement('td');
        amountTd.appendChild(amountInput);
        tr.appendChild(amountTd);

        const descInput = createElement('input', {
            type: 'text',
            name: `prospect_description[${idx}]`,
            value: row.description || ''
        });
        const descTd = createElement('td');
        descTd.appendChild(descInput);
        tr.appendChild(descTd);

        const removeBtn = createElement('button', { type: 'button', class: 'remove-prospect-row' });
        removeBtn._textContent = 'Remove';
        const actionTd = createElement('td');
        actionTd.appendChild(removeBtn);
        tr.appendChild(actionTd);

        prospectTableBody.appendChild(tr);
    });

    const prospectTable = createElement('table', { id: 'prospecting-table' });
    prospectTable.appendChild(prospectTableBody);

    const addBtn = createElement('button', { type: 'button', id: 'add-prospect-row' });
    addBtn._textContent = 'Add Row';

    // ---- Build Totals Row ----
    const totalClosing = createElement('span', { id: 'total-closing', 'data-value': '0' });
    totalClosing._textContent = '0';
    const totalAtRisk = createElement('span', { id: 'total-at-risk', 'data-value': '0' });
    totalAtRisk._textContent = '0';
    const totalProgression = createElement('span', { id: 'total-progression', 'data-value': '0' });
    totalProgression._textContent = '0';
    const totalNewPipeline = createElement('span', { id: 'total-new-pipeline', 'data-value': '0' });
    totalNewPipeline._textContent = '0';

    const totalClosingBox = createElement('div', { class: 'total-box', id: 'total-closing-box' });
    totalClosingBox.appendChild(totalClosing);
    const totalAtRiskBox = createElement('div', { class: 'total-box', id: 'total-at-risk-box' });
    totalAtRiskBox.appendChild(totalAtRisk);
    const totalProgressionBox = createElement('div', { class: 'total-box', id: 'total-progression-box' });
    totalProgressionBox.appendChild(totalProgression);
    const totalNewPipelineBox = createElement('div', { class: 'total-box', id: 'total-new-pipeline-box' });
    totalNewPipelineBox.appendChild(totalNewPipeline);

    const totalsRow = createElement('div', { id: 'totals-row', class: 'lf-totals-container' });
    totalsRow.appendChild(totalClosingBox);
    totalsRow.appendChild(totalAtRiskBox);
    totalsRow.appendChild(totalProgressionBox);
    totalsRow.appendChild(totalNewPipelineBox);

    // ---- Build Health Summary Section ----
    const closedYtdEl = createElement('span', { id: 'health-closed-ytd', 'data-value': String(closedYtd) });
    closedYtdEl._textContent = String(closedYtd);
    const remainingQuotaEl = createElement('span', { id: 'health-remaining-quota', 'data-value': '0' });
    remainingQuotaEl._textContent = '0';
    const pipelineTargetEl = createElement('span', { id: 'health-pipeline-target', 'data-value': '0' });
    pipelineTargetEl._textContent = '0';
    const currentPipelineEl = createElement('span', { id: 'health-current-pipeline', 'data-value': String(currentPipelineTotal) });
    currentPipelineEl._textContent = String(currentPipelineTotal);
    const gapToTargetEl = createElement('span', { id: 'health-gap-to-target', 'data-value': '0' });
    gapToTargetEl._textContent = '0';
    const coverageRatioEl = createElement('span', { id: 'health-coverage-ratio', 'data-value': '0' });
    coverageRatioEl._textContent = '0';

    const healthSummary = createElement('div', { id: 'health-summary', class: 'lf-health-summary' });
    healthSummary.appendChild(closedYtdEl);
    healthSummary.appendChild(remainingQuotaEl);
    healthSummary.appendChild(pipelineTargetEl);
    healthSummary.appendChild(currentPipelineEl);
    healthSummary.appendChild(gapToTargetEl);
    healthSummary.appendChild(coverageRatioEl);

    // ---- Build Save/Submit Buttons ----
    const saveBtn = createElement('button', { type: 'button', id: 'save-plan', class: 'btn-save' });
    saveBtn._textContent = 'Save';
    const submitBtn = createElement('button', { type: 'button', id: 'updates-complete', class: 'btn-submit' });
    submitBtn._textContent = 'Updates Complete';

    // ---- Build Message Container ----
    const messageContainer = createElement('div', { id: 'save-message', class: 'lf-message' });
    messageContainer._textContent = '';

    // ---- Build Container ----
    const container = createElement('div', { id: 'lf-planning-container' });
    container.appendChild(totalsRow);
    container.appendChild(pipelineTable);
    container.appendChild(devPipelineTable);
    container.appendChild(prospectTable);
    container.appendChild(addBtn);
    container.appendChild(healthSummary);
    container.appendChild(saveBtn);
    container.appendChild(submitBtn);
    container.appendChild(messageContainer);

    function indexElements(el) {
        if (el.id) elements[el.id] = el;
        (el.children || []).forEach(indexElements);
    }
    indexElements(container);

    const document = {
        getElementById(id) { return elements[id] || null; },
        querySelector(selector) {
            return querySelectorInTree(container, selector) || (matchesSelector(container, selector) ? container : null);
        },
        querySelectorAll(selector) {
            const result = querySelectorAllInTree(container, selector);
            if (matchesSelector(container, selector)) result.unshift(container);
            return result;
        },
        createElement(tag) { return createElement(tag); },
        addEventListener(type, handler) {
            if (!eventListeners[type]) eventListeners[type] = [];
            eventListeners[type].push(handler);
        },
        dispatchEvent(event) {
            const handlers = eventListeners[event.type] || [];
            handlers.forEach(h => h(event));
        }
    };

    // Mock fetch
    const mockFetch = function(url, options) {
        fetchCalls.push({ url, options });
        return Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ success: true, message: 'Saved successfully' })
        });
    };

    // Mock SUGAR global with CSRF token
    const SUGAR = {
        csrf: {
            form_token: 'test-csrf-token-12345'
        }
    };

    const window = {
        LF_STAGE_PROBS: stageProbabilities,
        stageProbabilities: stageProbabilities,
        LF_SOURCE_TYPES: sourceTypes,
        LF_WEEKLY_TARGETS: weeklyTargets,
        LF_HEALTH_DATA: {
            closed_ytd: closedYtd,
            annual_quota: annualQuota,
            coverage_multiplier: coverageMultiplier,
            current_pipeline: currentPipelineTotal
        },
        SUGAR: SUGAR,
        document: document,
        addEventListener: document.addEventListener.bind(document),
        fetch: mockFetch,
        alert: function(msg) { alertMessages.push(msg); },
        Event: function EventMock(type, opts = {}) {
            return { type, bubbles: opts.bubbles || false, target: null, currentTarget: null };
        },
        console: console
    };

    return {
        window,
        document,
        container,
        elements,
        removedElements,
        eventListeners,
        createElement,
        fetchCalls,
        alertMessages,
        SUGAR
    };
}

/**
 * Load planning.js into a sandbox context and trigger DOMContentLoaded.
 */
function loadPlanningJsInSandbox(sandbox) {
    const jsContent = fs.readFileSync(jsFile, 'utf8');

    const context = vm.createContext({
        window: sandbox.window,
        document: sandbox.document,
        console: console,
        Event: sandbox.window.Event,
        LF_STAGE_PROBS: sandbox.window.LF_STAGE_PROBS,
        stageProbabilities: sandbox.window.stageProbabilities,
        LF_SOURCE_TYPES: sandbox.window.LF_SOURCE_TYPES,
        LF_WEEKLY_TARGETS: sandbox.window.LF_WEEKLY_TARGETS,
        LF_HEALTH_DATA: sandbox.window.LF_HEALTH_DATA,
        SUGAR: sandbox.window.SUGAR,
        fetch: sandbox.window.fetch,
        alert: sandbox.window.alert,
        parseFloat: parseFloat,
        parseInt: parseInt,
        isNaN: isNaN,
        Number: Number,
        String: String,
        Math: Math,
        JSON: JSON,
        Array: Array,
        Object: Object,
        Promise: Promise,
        setTimeout: setTimeout
    });

    vm.runInContext(jsContent, context);

    // Trigger DOMContentLoaded
    const event = { type: 'DOMContentLoaded', bubbles: false, target: null, currentTarget: null };
    (sandbox.eventListeners['DOMContentLoaded'] || []).forEach(h => h(event));

    return context;
}


// ============================================================
// Section 1: File Structure - Health Summary Support
// ============================================================
console.log('Section 1: File Structure - Health Summary Support');

test('planning.js should reference health summary section', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('health-summary') || content.includes('healthSummary') ||
        content.includes('health_summary') || content.includes('pipeline-health'),
        'planning.js must reference health summary section (health-summary, healthSummary, etc.)'
    );
});

test('planning.js should reference remaining quota calculation', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('remainingQuota') || content.includes('remaining_quota') ||
        content.includes('remaining-quota'),
        'planning.js must reference remaining quota calculation'
    );
});

test('planning.js should reference pipeline target calculation', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('pipelineTarget') || content.includes('pipeline_target') ||
        content.includes('pipeline-target'),
        'planning.js must reference pipeline target calculation'
    );
});

test('planning.js should reference gap to target calculation', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('gapToTarget') || content.includes('gap_to_target') ||
        content.includes('gap-to-target'),
        'planning.js must reference gap to target calculation'
    );
});

test('planning.js should reference coverage ratio calculation', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('coverageRatio') || content.includes('coverage_ratio') ||
        content.includes('coverage-ratio'),
        'planning.js must reference coverage ratio calculation'
    );
});


// ============================================================
// Section 2: File Structure - Save Functionality
// ============================================================
console.log('\nSection 2: File Structure - Save Functionality');

test('planning.js should use fetch() for AJAX save', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('fetch(') || content.includes('fetch ('),
        'planning.js must use fetch() for AJAX save to the save_json endpoint'
    );
});

test('planning.js should reference save_json endpoint', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('save_json') || content.includes('action=save_json'),
        'planning.js must reference the save_json AJAX endpoint'
    );
});

test('planning.js should include CSRF token in fetch requests', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        (content.includes('csrf') || content.includes('CSRF') || content.includes('form_token')) &&
        content.includes('SUGAR'),
        'planning.js must include SUGAR.csrf.form_token in fetch requests for CSRF protection'
    );
});

test('planning.js should reference save button', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('save-plan') || content.includes('savePlan') || content.includes('save_plan'),
        'planning.js must handle save button click (save-plan or savePlan)'
    );
});


// ============================================================
// Section 3: File Structure - Updates Complete / Submit
// ============================================================
console.log('\nSection 3: File Structure - Updates Complete / Submit');

test('planning.js should handle Updates Complete button', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('updates-complete') || content.includes('updatesComplete') ||
        content.includes('submit-plan') || content.includes('submitPlan'),
        'planning.js must handle Updates Complete button click'
    );
});

test('planning.js should send submitted status', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes("'submitted'") || content.includes('"submitted"'),
        'planning.js must send "submitted" status when Updates Complete is clicked'
    );
});

test('planning.js should display success/error messages', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('save-message') || content.includes('saveMessage') ||
        content.includes('lf-message') || content.includes('status-message') ||
        content.includes('notification'),
        'planning.js must reference message container for save success/error display'
    );
});


// ============================================================
// Section 4: File Structure - Totals Color Coding
// ============================================================
console.log('\nSection 4: File Structure - Totals Color Coding');

test('planning.js should reference weekly targets for color coding', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('WEEKLY_TARGETS') || content.includes('weeklyTargets') ||
        content.includes('weekly_targets') || content.includes('data-target'),
        'planning.js must reference weekly targets for totals color coding'
    );
});

test('planning.js should apply color coding based on target comparison', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    // Must contain logic for green/red styling
    assert.ok(
        (content.includes('green') || content.includes('#2F7D32') || content.includes('meeting') || content.includes('on-target')) &&
        (content.includes('red') || content.includes('#d13438') || content.includes('below') || content.includes('off-target')),
        'planning.js must apply green (meeting target) and red (below target) color coding'
    );
});


// ============================================================
// Section 5: Health Summary Calculations - Remaining Quota
// ============================================================
console.log('\nSection 5: Health Summary - Remaining Quota Calculation');

test('should calculate Remaining Quota as annual_quota - closed_ytd', () => {
    // Annual Quota: 500000, Closed YTD: 150000
    // Remaining Quota = 500000 - 150000 = 350000
    const sandbox = createBrowserSandbox({
        closedYtd: 150000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const remainingQuotaEl = sandbox.elements['health-remaining-quota'];
    assert.ok(remainingQuotaEl, 'health-remaining-quota element should exist');
    const value = parseFloat(remainingQuotaEl.getAttribute('data-value') || remainingQuotaEl.textContent);
    assert.strictEqual(value, 350000, `Remaining Quota should be 350000 (500000-150000), got ${value}`);
});

test('should calculate Remaining Quota as 0 when closed YTD exceeds quota', () => {
    // Edge case: closed more than quota
    const sandbox = createBrowserSandbox({
        closedYtd: 600000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const remainingQuotaEl = sandbox.elements['health-remaining-quota'];
    assert.ok(remainingQuotaEl, 'health-remaining-quota element should exist');
    const value = parseFloat(remainingQuotaEl.getAttribute('data-value') || remainingQuotaEl.textContent);
    // Value should be negative (or clamped to 0 depending on implementation)
    assert.ok(value <= 0, `Remaining Quota should be <= 0 when closed exceeds quota, got ${value}`);
});


// ============================================================
// Section 6: Health Summary - Pipeline Target
// ============================================================
console.log('\nSection 6: Health Summary - Pipeline Target Calculation');

test('should calculate Pipeline Target as remaining_quota * coverage_multiplier', () => {
    // Remaining Quota: 350000 (500000 - 150000), Coverage Multiplier: 4
    // Pipeline Target = 350000 * 4 = 1400000
    const sandbox = createBrowserSandbox({
        closedYtd: 150000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const pipelineTargetEl = sandbox.elements['health-pipeline-target'];
    assert.ok(pipelineTargetEl, 'health-pipeline-target element should exist');
    const value = parseFloat(pipelineTargetEl.getAttribute('data-value') || pipelineTargetEl.textContent);
    assert.strictEqual(value, 1400000, `Pipeline Target should be 1400000 (350000*4), got ${value}`);
});


// ============================================================
// Section 7: Health Summary - Gap to Target
// ============================================================
console.log('\nSection 7: Health Summary - Gap to Target');

test('should calculate Gap to Target as pipeline_target - current_pipeline', () => {
    // Pipeline Target: 1400000, Current Pipeline: 800000
    // Gap = 1400000 - 800000 = 600000
    const sandbox = createBrowserSandbox({
        closedYtd: 150000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        currentPipelineTotal: 800000,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const gapEl = sandbox.elements['health-gap-to-target'];
    assert.ok(gapEl, 'health-gap-to-target element should exist');
    const value = parseFloat(gapEl.getAttribute('data-value') || gapEl.textContent);
    assert.strictEqual(value, 600000, `Gap to Target should be 600000 (1400000-800000), got ${value}`);
});

test('should apply red styling to Gap to Target when pipeline < target', () => {
    // Pipeline (800000) < Target (1400000), should get red styling
    const sandbox = createBrowserSandbox({
        closedYtd: 150000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        currentPipelineTotal: 800000,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const gapEl = sandbox.elements['health-gap-to-target'];
    assert.ok(gapEl, 'health-gap-to-target element should exist');

    // Should have red styling - check class or inline style
    const hasRedClass = (gapEl.className || '').includes('negative') ||
                        (gapEl.className || '').includes('red') ||
                        (gapEl.className || '').includes('danger') ||
                        (gapEl.className || '').includes('gap-negative');
    const hasRedStyle = (gapEl.style.color || '').includes('red') ||
                        (gapEl.style.color || '').includes('#d13438');

    assert.ok(
        hasRedClass || hasRedStyle,
        `Gap to Target should have red styling when pipeline < target. Class: "${gapEl.className}", Style: "${JSON.stringify(gapEl.style)}"`
    );
});

test('should not apply red styling when pipeline >= target', () => {
    // Pipeline (2000000) > Target (1400000), should NOT have red
    const sandbox = createBrowserSandbox({
        closedYtd: 150000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        currentPipelineTotal: 2000000,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const gapEl = sandbox.elements['health-gap-to-target'];
    assert.ok(gapEl, 'health-gap-to-target element should exist');

    const hasRedClass = (gapEl.className || '').includes('negative') ||
                        (gapEl.className || '').includes('danger') ||
                        (gapEl.className || '').includes('gap-negative');

    assert.ok(
        !hasRedClass,
        `Gap to Target should NOT have red styling when pipeline >= target. Class: "${gapEl.className}"`
    );
});


// ============================================================
// Section 8: Health Summary - Coverage Ratio
// ============================================================
console.log('\nSection 8: Health Summary - Coverage Ratio');

test('should calculate Coverage Ratio as current_pipeline / remaining_quota', () => {
    // Current Pipeline: 800000, Remaining Quota: 350000
    // Coverage Ratio = 800000 / 350000 = 2.29 (approximately)
    const sandbox = createBrowserSandbox({
        closedYtd: 150000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        currentPipelineTotal: 800000,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const ratioEl = sandbox.elements['health-coverage-ratio'];
    assert.ok(ratioEl, 'health-coverage-ratio element should exist');
    const value = parseFloat(ratioEl.getAttribute('data-value') || ratioEl.textContent);
    // 800000 / 350000 = 2.2857...
    assert.ok(
        Math.abs(value - 2.29) < 0.1,
        `Coverage Ratio should be approximately 2.29 (800000/350000), got ${value}`
    );
});

test('should handle zero remaining quota in coverage ratio (avoid division by zero)', () => {
    // Edge case: closed_ytd >= annual_quota, remaining_quota = 0
    const sandbox = createBrowserSandbox({
        closedYtd: 500000,
        annualQuota: 500000,
        coverageMultiplier: 4,
        currentPipelineTotal: 100000,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const ratioEl = sandbox.elements['health-coverage-ratio'];
    assert.ok(ratioEl, 'health-coverage-ratio element should exist');
    const value = parseFloat(ratioEl.getAttribute('data-value') || ratioEl.textContent);
    // Should not be NaN or Infinity
    assert.ok(
        !isNaN(value) && isFinite(value),
        `Coverage Ratio should be a valid number when remaining_quota is 0, got ${value}`
    );
});


// ============================================================
// Section 9: Totals Color Coding - Below Target = Red
// ============================================================
console.log('\nSection 9: Totals Color Coding - Below Target');

test('should apply red color to Closing total when below weekly target', () => {
    // Closing total: 10000, weekly closed target: 20000 -> below -> red
    const sandbox = createBrowserSandbox({
        weeklyTargets: { closing: 20000, new_pipeline: 100000, progression: 100000 },
        pipelineRows: [{
            oppId: 'opp-1',
            amount: 10000,
            currentStage: '7-Closing (90%)',
            projectedStage: '',
            category: 'closing'
        }],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger calculation
    const catSelect = sandbox.document.querySelector('.category-select');
    if (catSelect) catSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const closingBox = sandbox.elements['total-closing-box'] || sandbox.elements['total-closing'];
    assert.ok(closingBox, 'total-closing element or box should exist');

    // Check for red/below-target styling
    const hasRedStyling = (closingBox.className || '').includes('below') ||
                          (closingBox.className || '').includes('red') ||
                          (closingBox.className || '').includes('off-target') ||
                          (closingBox.style.color || '').includes('red') ||
                          (closingBox.style.color || '').includes('#d13438') ||
                          (closingBox.style.backgroundColor || '').includes('red');

    assert.ok(
        hasRedStyling,
        `Closing total box should have red styling when 10000 < 20000 target. Class: "${closingBox.className}"`
    );
});

test('should apply green color to Closing total when meeting weekly target', () => {
    // Closing total: 25000, weekly closed target: 20000 -> meeting -> green
    const sandbox = createBrowserSandbox({
        weeklyTargets: { closing: 20000, new_pipeline: 100000, progression: 100000 },
        pipelineRows: [{
            oppId: 'opp-2',
            amount: 25000,
            currentStage: '7-Closing (90%)',
            projectedStage: '',
            category: 'closing'
        }],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const catSelect = sandbox.document.querySelector('.category-select');
    if (catSelect) catSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const closingBox = sandbox.elements['total-closing-box'] || sandbox.elements['total-closing'];
    assert.ok(closingBox, 'total-closing element or box should exist');

    const hasGreenStyling = (closingBox.className || '').includes('meeting') ||
                            (closingBox.className || '').includes('green') ||
                            (closingBox.className || '').includes('on-target') ||
                            (closingBox.style.color || '').includes('green') ||
                            (closingBox.style.color || '').includes('#2F7D32');

    assert.ok(
        hasGreenStyling,
        `Closing total box should have green styling when 25000 >= 20000 target. Class: "${closingBox.className}"`
    );
});

test('should apply color coding to New Pipeline total based on target', () => {
    // New Pipeline total: 50000, target: 100000 -> below -> red
    const sandbox = createBrowserSandbox({
        weeklyTargets: { closing: 20000, new_pipeline: 100000, progression: 100000 },
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-1',
            amount: 50000,
            projectedStage: '3-Confirmation (10%)' // 50000 * 10/100 = 5000
        }],
        prospectingRows: [
            { source: 'Cold Call', amount: 45000, description: 'Test' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger
    const devSelect = sandbox.document.querySelector('.dev-projected-stage-select');
    if (devSelect) devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineBox = sandbox.elements['total-new-pipeline-box'] || sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineBox, 'total-new-pipeline element or box should exist');

    const hasRedStyling = (newPipelineBox.className || '').includes('below') ||
                          (newPipelineBox.className || '').includes('red') ||
                          (newPipelineBox.className || '').includes('off-target') ||
                          (newPipelineBox.style.color || '').includes('red') ||
                          (newPipelineBox.style.color || '').includes('#d13438');

    assert.ok(
        hasRedStyling,
        `New Pipeline total should have red styling when 50000 < 100000 target. Class: "${newPipelineBox.className}"`
    );
});


// ============================================================
// Section 10: Save Functionality - collectFormData
// ============================================================
console.log('\nSection 10: Save Functionality - Form Data Collection');

test('planning.js should have a function to collect form data for saving', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('collectFormData') || content.includes('collectData') ||
        content.includes('gatherFormData') || content.includes('getFormData') ||
        content.includes('buildSavePayload') || content.includes('buildPayload'),
        'planning.js must have a function to collect form data (collectFormData, gatherFormData, etc.)'
    );
});

test('planning.js should collect pipeline items data for save', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    // Must iterate pipeline rows and collect data
    assert.ok(
        (content.includes('pipeline') || content.includes('Pipeline')) &&
        content.includes('JSON.stringify'),
        'planning.js must collect pipeline data and use JSON.stringify for the save request body'
    );
});


// ============================================================
// Section 11: Save Functionality - fetch with CSRF
// ============================================================
console.log('\nSection 11: Save Functionality - Fetch with CSRF Token');

test('should call fetch with POST method and JSON body when save button clicked', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-save-1',
            amount: 50000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '5-Specifications (30%)',
            category: 'progression',
            planDesc: 'Test plan'
        }],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Click save button
    const saveBtn = sandbox.elements['save-plan'];
    assert.ok(saveBtn, 'save-plan button should exist');
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    // Check fetch was called (async, but check if any calls were registered)
    // In sync test, we verify fetch was invoked
    assert.ok(
        sandbox.fetchCalls.length > 0,
        `Save button click should trigger fetch(), got ${sandbox.fetchCalls.length} fetch calls`
    );
});

test('should include CSRF token in fetch headers or body', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-csrf-1',
            amount: 30000,
            currentStage: '5-Specifications (30%)',
            projectedStage: '',
            category: 'closing'
        }],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Click save button
    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    if (sandbox.fetchCalls.length > 0) {
        const call = sandbox.fetchCalls[0];
        const opts = call.options || {};
        const headers = opts.headers || {};
        const body = opts.body || '';

        // CSRF token must appear in headers or body
        const csrfInHeaders = Object.values(headers).some(v => String(v).includes('test-csrf-token'));
        const csrfInBody = typeof body === 'string' && body.includes('test-csrf-token');
        const csrfInHeaderKey = Object.keys(headers).some(k =>
            k.toLowerCase().includes('csrf') || k.toLowerCase().includes('x-csrf')
        );

        assert.ok(
            csrfInHeaders || csrfInBody || csrfInHeaderKey,
            `Fetch request must include CSRF token. Headers: ${JSON.stringify(headers)}, Body starts with: ${String(body).substring(0, 100)}`
        );
    } else {
        assert.fail('No fetch calls recorded to check CSRF token');
    }
});

test('should call save_json endpoint URL', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    if (sandbox.fetchCalls.length > 0) {
        const url = sandbox.fetchCalls[0].url || '';
        assert.ok(
            url.includes('save_json') || url.includes('action=save_json'),
            `Fetch URL should reference save_json endpoint, got: "${url}"`
        );
    } else {
        assert.fail('No fetch calls recorded to verify endpoint URL');
    }
});


// ============================================================
// Section 12: Updates Complete Button
// ============================================================
console.log('\nSection 12: Updates Complete Button Behavior');

test('should send submitted status when Updates Complete button clicked', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Click updates complete button
    const submitBtn = sandbox.elements['updates-complete'];
    assert.ok(submitBtn, 'updates-complete button should exist');
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = submitBtn;
    submitBtn.dispatchEvent(clickEvent);

    // Should have triggered fetch
    assert.ok(
        sandbox.fetchCalls.length > 0,
        `Updates Complete should trigger fetch(), got ${sandbox.fetchCalls.length} fetch calls`
    );

    if (sandbox.fetchCalls.length > 0) {
        const call = sandbox.fetchCalls[sandbox.fetchCalls.length - 1]; // Last call
        const body = call.options ? call.options.body : '';
        const bodyStr = typeof body === 'string' ? body : JSON.stringify(body);

        assert.ok(
            bodyStr.includes('submitted') || bodyStr.includes('submit'),
            `Updates Complete request body should include "submitted" status. Body: ${bodyStr.substring(0, 200)}`
        );
    }
});


// ============================================================
// Section 13: Message Display
// ============================================================
console.log('\nSection 13: Message Display');

test('planning.js should update message container on save response', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    // After fetch response, must update the message display area
    assert.ok(
        (content.includes('.then') || content.includes('async') || content.includes('await')) &&
        (content.includes('message') || content.includes('Message')),
        'planning.js must handle fetch response and update message display'
    );
});


// ============================================================
// Section 14: Edge Cases - Health Summary with Zero Values
// ============================================================
console.log('\nSection 14: Edge Cases - Health Summary');

test('should handle zero closed YTD correctly', () => {
    const sandbox = createBrowserSandbox({
        closedYtd: 0,
        annualQuota: 500000,
        coverageMultiplier: 4,
        currentPipelineTotal: 0,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const remainingEl = sandbox.elements['health-remaining-quota'];
    assert.ok(remainingEl, 'health-remaining-quota should exist');
    const value = parseFloat(remainingEl.getAttribute('data-value') || remainingEl.textContent);
    assert.strictEqual(value, 500000, `Remaining Quota should be 500000 with 0 closed, got ${value}`);
});

test('should handle zero annual quota correctly', () => {
    const sandbox = createBrowserSandbox({
        closedYtd: 0,
        annualQuota: 0,
        coverageMultiplier: 4,
        currentPipelineTotal: 100000,
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const targetEl = sandbox.elements['health-pipeline-target'];
    assert.ok(targetEl, 'health-pipeline-target should exist');
    const value = parseFloat(targetEl.getAttribute('data-value') || targetEl.textContent);
    assert.strictEqual(value, 0, `Pipeline Target should be 0 with 0 quota, got ${value}`);
});


// ============================================================
// Section 15: Progression Color Coding
// ============================================================
console.log('\nSection 15: Progression Totals Color Coding');

test('should apply color coding to Progression total based on weekly target', () => {
    // Progression total below target -> red
    const sandbox = createBrowserSandbox({
        weeklyTargets: { closing: 20000, new_pipeline: 100000, progression: 100000 },
        pipelineRows: [{
            oppId: 'opp-prog-1',
            amount: 200000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '5-Specifications (30%)',
            category: 'progression'
        }],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger recalc - progression = 200000 * (30-10)/100 = 40000 < 100000 target
    const projSelect = sandbox.document.querySelector('.projected-stage-select');
    if (projSelect) projSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const progressionBox = sandbox.elements['total-progression-box'] || sandbox.elements['total-progression'];
    assert.ok(progressionBox, 'total-progression element should exist');

    const hasRedStyling = (progressionBox.className || '').includes('below') ||
                          (progressionBox.className || '').includes('red') ||
                          (progressionBox.className || '').includes('off-target') ||
                          (progressionBox.style.color || '').includes('red') ||
                          (progressionBox.style.color || '').includes('#d13438');

    assert.ok(
        hasRedStyling,
        `Progression total should have red styling when 40000 < 100000 target. Class: "${progressionBox.className}"`
    );
});


// ============================================================
// Section 16: Save Includes Prospecting Items
// ============================================================
console.log('\nSection 16: Save Includes Prospecting Items');

test('should include prospecting items in save payload', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 25000, description: 'New lead A' },
            { source: 'Referral', amount: 10000, description: 'New lead B' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    if (sandbox.fetchCalls.length > 0) {
        const call = sandbox.fetchCalls[0];
        const body = call.options ? call.options.body : '';
        const bodyStr = typeof body === 'string' ? body : JSON.stringify(body);

        // Body should contain prospecting data
        assert.ok(
            bodyStr.includes('prospect') || bodyStr.includes('Prospect') ||
            bodyStr.includes('source_type') || bodyStr.includes('sourceType') ||
            bodyStr.includes('Cold Call') || bodyStr.includes('expected_value'),
            `Save payload must include prospecting items. Body: ${bodyStr.substring(0, 300)}`
        );
    } else {
        assert.fail('No fetch calls to verify prospecting data in save payload');
    }
});


// ============================================================
// Section 17: Save Includes Developing Pipeline Items
// ============================================================
console.log('\nSection 17: Save Includes Developing Pipeline Items');

test('should include developing pipeline items in save payload', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-save-1',
            amount: 75000,
            projectedStage: '5-Specifications (30%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    if (sandbox.fetchCalls.length > 0) {
        const call = sandbox.fetchCalls[0];
        const body = call.options ? call.options.body : '';
        const bodyStr = typeof body === 'string' ? body : JSON.stringify(body);

        // Body should contain developing pipeline data
        assert.ok(
            bodyStr.includes('dev') || bodyStr.includes('developing') ||
            bodyStr.includes('dev-save-1') || bodyStr.includes('5-Specifications'),
            `Save payload must include developing pipeline items. Body: ${bodyStr.substring(0, 300)}`
        );
    } else {
        assert.fail('No fetch calls to verify developing pipeline data in save payload');
    }
});


// ============================================================
// Summary
// ============================================================
console.log('\n' + '='.repeat(60));
console.log('SUMMARY');
console.log('='.repeat(60));
console.log(`Total: ${passCount + failCount}`);
console.log(`Passed: ${passCount}`);
console.log(`Failed: ${failCount}`);

if (failures.length > 0) {
    console.log('\nFailed tests:');
    failures.forEach(f => {
        console.log(`  - ${f.name}`);
        console.log(`    ${f.error}`);
    });
}

console.log('='.repeat(60));

process.exit(failCount > 0 ? 1 : 0);
