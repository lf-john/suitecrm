/**
 * US-006: JS Save Contract Tests
 *
 * Tests that planning.js sends the correct save payload structure
 * matching what the save endpoint (view.save_json.php) expects:
 *
 *   1. Save payload must include plan_id (required by endpoint)
 *   2. Save payload must use 'op_items' key (not 'pipeline'/'developing')
 *   3. Save payload must use 'prospect_items' key (not 'prospecting')
 *   4. Pipeline and developing items must be merged into op_items array
 *   5. Each op_item must have item_type to distinguish pipeline vs developing
 *   6. prospect_items must have correct field names matching endpoint expectations
 *
 * These tests MUST FAIL until the JS save payload is aligned with the PHP endpoint.
 *
 * Test approach: Load planning.js in a sandbox, trigger save, inspect fetch payload.
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

const STAGE_PROBS = {
    '2-Analysis (1%)': 1,
    '3-Confirmation (10%)': 10,
    '5-Specifications (30%)': 30,
    '6-Solution (60%)': 60,
    '7-Closing (90%)': 90,
    'closed_won': 100
};

const SOURCE_TYPES = ['Cold Call', 'Referral', 'Event', 'Partner', 'Inbound', 'Customer Visit', 'Other'];

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
 * Creates a minimal sandbox simulating the browser environment.
 * Extends from existing US-006 sandbox pattern with plan_id support.
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
        currentPipelineTotal = 0,
        planId = 'test-plan-id-abc123'
    } = options;

    const elements = {};
    const eventListeners = {};
    const removedElements = [];
    const fetchCalls = [];
    const alertMessages = [];

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
    const container = createElement('div', {
        id: 'lf-planning-container',
        'data-plan-id': planId
    });
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

    const mockFetch = function(url, options) {
        fetchCalls.push({ url, options });
        return Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ success: true, message: 'Saved successfully' })
        });
    };

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
        LF_PLAN_ID: planId,
        SUGAR: SUGAR,
        document: document,
        addEventListener: document.addEventListener.bind(document),
        fetch: mockFetch,
        alert: function(msg) { alertMessages.push(msg); },
        location: { reload: function() {} },
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
        SUGAR,
        planId
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
        LF_PLAN_ID: sandbox.window.LF_PLAN_ID,
        SUGAR: sandbox.window.SUGAR,
        fetch: sandbox.window.fetch,
        alert: sandbox.window.alert,
        location: sandbox.window.location,
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
// Section 1: Save Payload Must Include plan_id
// ============================================================
console.log('Section 1: Save Payload Must Include plan_id');

test('save payload must include plan_id field from LF_PLAN_ID or container data attribute', () => {
    const sandbox = createBrowserSandbox({
        planId: 'plan-abc-123',
        pipelineRows: [{
            oppId: 'opp-1',
            amount: 50000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '5-Specifications (30%)',
            category: 'progression'
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

    assert.ok(sandbox.fetchCalls.length > 0, 'Save button should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    assert.ok(
        parsed.plan_id === 'plan-abc-123',
        `Save payload must include plan_id='plan-abc-123'. Got plan_id='${parsed.plan_id}'. Keys: ${Object.keys(parsed).join(', ')}`
    );
});

test('updates complete payload must include plan_id field', () => {
    const sandbox = createBrowserSandbox({
        planId: 'plan-submit-456',
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Click updates complete button
    const submitBtn = sandbox.elements['updates-complete'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = submitBtn;
    submitBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Updates Complete should trigger fetch');

    const call = sandbox.fetchCalls[sandbox.fetchCalls.length - 1];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    assert.ok(
        parsed.plan_id === 'plan-submit-456',
        `Submit payload must include plan_id='plan-submit-456'. Got plan_id='${parsed.plan_id}'. Keys: ${Object.keys(parsed).join(', ')}`
    );
});


// ============================================================
// Section 2: Save Payload Must Use op_items Key
// ============================================================
console.log('\nSection 2: Save Payload Must Use op_items Key');

test('save payload must use op_items key for opportunity-based items', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-contract-1',
            amount: 100000,
            currentStage: '5-Specifications (30%)',
            projectedStage: '6-Solution (60%)',
            category: 'progression'
        }],
        developingPipelineRows: [{
            oppId: 'dev-contract-1',
            amount: 50000,
            projectedStage: '3-Confirmation (10%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Save should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    assert.ok(
        Array.isArray(parsed.op_items),
        `Save payload must have 'op_items' array key (matching save endpoint). Got keys: ${Object.keys(parsed).join(', ')}`
    );
});

test('op_items must contain both pipeline and developing items combined', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-merge-1',
            amount: 80000,
            currentStage: '6-Solution (60%)',
            projectedStage: '7-Closing (90%)',
            category: 'closing'
        }],
        developingPipelineRows: [{
            oppId: 'dev-merge-1',
            amount: 30000,
            projectedStage: '5-Specifications (30%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Save should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    assert.ok(
        Array.isArray(parsed.op_items) && parsed.op_items.length === 2,
        `op_items must contain 2 items (1 pipeline + 1 developing). Got ${parsed.op_items ? parsed.op_items.length : 'undefined'} items`
    );

    // Verify both opportunity IDs are present
    const oppIds = (parsed.op_items || []).map(item => item.opportunity_id);
    assert.ok(
        oppIds.includes('opp-merge-1') && oppIds.includes('dev-merge-1'),
        `op_items must include opp-merge-1 and dev-merge-1. Got: ${oppIds.join(', ')}`
    );
});

test('developing items in op_items must have item_type=developing', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-type-1',
            amount: 40000,
            projectedStage: '3-Confirmation (10%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Save should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    const devItems = (parsed.op_items || []).filter(item => item.opportunity_id === 'dev-type-1');
    assert.ok(
        devItems.length === 1 && devItems[0].item_type === 'developing',
        `Developing item must have item_type='developing' in op_items. Got: ${JSON.stringify(devItems)}`
    );
});


// ============================================================
// Section 3: Save Payload Must Use prospect_items Key
// ============================================================
console.log('\nSection 3: Save Payload Must Use prospect_items Key');

test('save payload must use prospect_items key for prospecting data', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 25000, description: 'Lead A' },
            { source: 'Referral', amount: 15000, description: 'Lead B' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Save should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    assert.ok(
        Array.isArray(parsed.prospect_items),
        `Save payload must have 'prospect_items' array key (matching save endpoint). Got keys: ${Object.keys(parsed).join(', ')}`
    );
});

test('prospect_items must include correct field names for endpoint', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Event', amount: 50000, description: 'Trade show' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Save should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    const items = parsed.prospect_items || [];
    assert.ok(items.length === 1, `Should have 1 prospect item, got ${items.length}`);

    const item = items[0];
    assert.ok(
        item.hasOwnProperty('source_type') &&
        item.hasOwnProperty('planned_day') &&
        item.hasOwnProperty('expected_value') &&
        item.hasOwnProperty('plan_description'),
        `prospect_items must have source_type, planned_day, expected_value, plan_description fields. Got: ${Object.keys(item).join(', ')}`
    );
});


// ============================================================
// Section 4: Save Payload Must NOT Have Separate pipeline/developing Keys
// ============================================================
console.log('\nSection 4: Save Payload Key Structure');

test('save payload must not have separate pipeline and developing keys at top level', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-struct-1',
            amount: 60000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '',
            category: 'closing'
        }],
        developingPipelineRows: [{
            oppId: 'dev-struct-1',
            amount: 25000,
            projectedStage: '5-Specifications (30%)'
        }],
        prospectingRows: [
            { source: 'Cold Call', amount: 10000, description: 'Test' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Save should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    // The payload should use op_items and prospect_items (not pipeline/developing/prospecting)
    assert.ok(
        !parsed.hasOwnProperty('pipeline') && !parsed.hasOwnProperty('developing') && !parsed.hasOwnProperty('prospecting'),
        `Save payload must not have 'pipeline', 'developing', or 'prospecting' as separate keys. Must use 'op_items' and 'prospect_items'. Got keys: ${Object.keys(parsed).join(', ')}`
    );
});


// ============================================================
// Section 5: Full Save Payload Structure Validation
// ============================================================
console.log('\nSection 5: Full Save Payload Structure');

test('complete save payload must have plan_id, status, op_items, and prospect_items', () => {
    const sandbox = createBrowserSandbox({
        planId: 'plan-full-789',
        pipelineRows: [{
            oppId: 'opp-full-1',
            amount: 75000,
            currentStage: '5-Specifications (30%)',
            projectedStage: '7-Closing (90%)',
            category: 'progression'
        }],
        developingPipelineRows: [{
            oppId: 'dev-full-1',
            amount: 20000,
            projectedStage: '3-Confirmation (10%)'
        }],
        prospectingRows: [
            { source: 'Referral', amount: 30000, description: 'Partner intro' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const saveBtn = sandbox.elements['save-plan'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = saveBtn;
    saveBtn.dispatchEvent(clickEvent);

    assert.ok(sandbox.fetchCalls.length > 0, 'Save should trigger fetch');

    const call = sandbox.fetchCalls[0];
    const body = call.options ? call.options.body : '';
    const parsed = JSON.parse(body);

    // Verify all required top-level keys
    const requiredKeys = ['plan_id', 'status', 'op_items', 'prospect_items'];
    const missingKeys = requiredKeys.filter(k => !parsed.hasOwnProperty(k));

    assert.ok(
        missingKeys.length === 0,
        `Save payload must have keys: ${requiredKeys.join(', ')}. Missing: ${missingKeys.join(', ')}. Got: ${Object.keys(parsed).join(', ')}`
    );
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
