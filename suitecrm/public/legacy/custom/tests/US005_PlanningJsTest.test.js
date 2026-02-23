/**
 * US-005: Planning JS - Developing Pipeline & Prospecting Interaction Tests
 *
 * Tests that planning.js handles the new Developing Pipeline table and
 * Prospecting section interactions correctly:
 *
 *   1. Developing Pipeline projected stage changes update New Pipeline total
 *   2. Developing Pipeline table uses #developing-pipeline-table ID
 *   3. Prospecting rows use Source Type <select> dropdown (not text input)
 *   4. Add Row creates rows with Source Type dropdown, Day dropdown,
 *      Expected Value number input, Description text input, Remove button
 *   5. New Pipeline total = developing pipeline projected amounts + prospecting expected values
 *   6. Developing pipeline amounts calculated as: amount * projected_prob / 100
 *
 * These tests MUST FAIL until the implementation is updated.
 *
 * Test approach: Structural pattern matching + DOM sandbox for behavior testing.
 * Reuses the sandbox pattern from US-004 tests.
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
const SOURCE_TYPES = ['Cold Call', 'Referral', 'Event', 'LinkedIn', 'Website'];

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
 * Creates a minimal sandbox that simulates the browser environment
 * for testing planning.js logic. Extends the US-004 sandbox to include
 * a developing pipeline table.
 */
function createBrowserSandbox(options = {}) {
    const {
        stageProbabilities = STAGE_PROBS,
        pipelineRows = [],
        developingPipelineRows = [],
        prospectingRows = [],
        sourceTypes = SOURCE_TYPES
    } = options;

    const elements = {};
    const eventListeners = {};
    const removedElements = [];

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
                this.attributes[name] = value;
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
        const parts = selector.split(/\s+/);
        const lastSelector = parts[parts.length - 1];
        const queue = [...(root.children || [])];
        while (queue.length) {
            const node = queue.shift();
            if (matchesSelector(node, lastSelector)) return node;
            if (node.children) queue.push(...node.children);
        }
        return null;
    }

    function querySelectorAllInTree(root, selector) {
        const results = [];
        const parts = selector.split(',').map(s => s.trim());
        for (const part of parts) {
            const singleParts = part.split(/\s+/);
            const lastSelector = singleParts[singleParts.length - 1];
            const queue = [...(root.children || [])];
            while (queue.length) {
                const node = queue.shift();
                if (matchesSelector(node, lastSelector)) results.push(node);
                if (node.children) queue.push(...node.children);
            }
        }
        return results;
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

        const planInput = createElement('input', { type: 'text', name: `plan[${oppId}]`, value: '' });
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

        // Amount cell
        const amountTd = createElement('td', { class: 'dev-amount', 'data-amount': String(row.amount || 0) });
        amountTd._textContent = String(row.amount || 0);
        tr.appendChild(amountTd);

        // Current stage (always 2-Analysis for developing pipeline)
        const stageTd = createElement('td', { class: 'dev-current-stage', 'data-stage': '2-Analysis (1%)' });
        stageTd._textContent = '2-Analysis (1%)';
        tr.appendChild(stageTd);

        // Projected stage select (stages above 2-Analysis)
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

        // Day select
        const daySelect = createElement('select', { name: `dev_day[${oppId}]`, class: 'dev-day-select' });
        const dayTd = createElement('td');
        dayTd.appendChild(daySelect);
        tr.appendChild(dayTd);

        // Plan input
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

        // Source type dropdown
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

        // Day dropdown
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

        // Expected value number input
        const amountInput = createElement('input', {
            type: 'number',
            name: `prospect_amount[${idx}]`,
            class: 'prospect-amount',
            value: String(row.amount || 0)
        });
        const amountTd = createElement('td');
        amountTd.appendChild(amountInput);
        tr.appendChild(amountTd);

        // Description text input
        const descInput = createElement('input', {
            type: 'text',
            name: `prospect_description[${idx}]`,
            value: row.description || ''
        });
        const descTd = createElement('td');
        descTd.appendChild(descInput);
        tr.appendChild(descTd);

        // Remove button
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

    // Totals elements
    const totalClosing = createElement('span', { id: 'total-closing', 'data-value': '0' });
    totalClosing._textContent = '0';
    const totalAtRisk = createElement('span', { id: 'total-at-risk', 'data-value': '0' });
    totalAtRisk._textContent = '0';
    const totalProgression = createElement('span', { id: 'total-progression', 'data-value': '0' });
    totalProgression._textContent = '0';
    const totalNewPipeline = createElement('span', { id: 'total-new-pipeline', 'data-value': '0' });
    totalNewPipeline._textContent = '0';

    const totalsRow = createElement('div', { id: 'totals-row' });
    totalsRow.appendChild(totalClosing);
    totalsRow.appendChild(totalAtRisk);
    totalsRow.appendChild(totalProgression);
    totalsRow.appendChild(totalNewPipeline);

    // Container
    const container = createElement('div', { id: 'lf-planning-container' });
    container.appendChild(pipelineTable);
    container.appendChild(devPipelineTable);
    container.appendChild(totalsRow);
    container.appendChild(prospectTable);
    container.appendChild(addBtn);

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

    const window = {
        LF_STAGE_PROBS: stageProbabilities,
        stageProbabilities: stageProbabilities,
        document: document,
        addEventListener: document.addEventListener.bind(document),
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
        createElement
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
        parseFloat: parseFloat,
        parseInt: parseInt,
        isNaN: isNaN,
        Number: Number,
        String: String,
        Math: Math,
        JSON: JSON,
        Array: Array,
        Object: Object
    });

    vm.runInContext(jsContent, context);

    // Trigger DOMContentLoaded
    const event = { type: 'DOMContentLoaded', bubbles: false, target: null, currentTarget: null };
    (sandbox.eventListeners['DOMContentLoaded'] || []).forEach(h => h(event));

    return context;
}


// ============================================================
// Section 1: File Structure - Developing Pipeline Support
// ============================================================
console.log('Section 1: File Structure - Developing Pipeline Support');

test('planning.js should reference developing pipeline table', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('developing-pipeline') || content.includes('dev-pipeline') || content.includes('developingPipeline'),
        'planning.js must reference developing pipeline table (developing-pipeline or dev-pipeline)'
    );
});

test('planning.js should handle developing pipeline projected stage changes', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('dev-projected-stage') || content.includes('devProjectedStage') || content.includes('developing'),
        'planning.js must handle developing pipeline projected stage change events'
    );
});

test('planning.js should include developing pipeline amounts in New Pipeline total', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    // The New Pipeline total formula must account for developing pipeline
    assert.ok(
        (content.includes('developing') || content.includes('dev-pipeline') || content.includes('dev-amount'))
        && (content.includes('total') || content.includes('Total')),
        'planning.js must include developing pipeline amounts in New Pipeline total calculation'
    );
});


// ============================================================
// Section 2: File Structure - Prospecting Source Type Dropdown
// ============================================================
console.log('\nSection 2: File Structure - Prospecting Source Type Dropdown');

test('planning.js should handle source type as a dropdown (select element)', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    // When adding a new prospecting row, it should create a <select> for source type
    // not just an <input type="text">
    assert.ok(
        content.includes('prospect-source') && (content.includes('select') || content.includes('SELECT')),
        'planning.js must create a <select> element for prospect-source when adding rows'
    );
});

test('planning.js should reference source types configuration for dropdown options', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('sourceTypes') || content.includes('source_types') || content.includes('LF_SOURCE_TYPES'),
        'planning.js must reference source types configuration (sourceTypes / LF_SOURCE_TYPES) for populating dropdowns'
    );
});


// ============================================================
// Section 3: File Structure - Prospecting Day Dropdown
// ============================================================
console.log('\nSection 3: File Structure - Prospecting Day Dropdown');

test('planning.js should create Day dropdown in prospecting rows', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('prospect-day') || content.includes('prospectDay') ||
        (content.includes('day') && content.includes('prospect')),
        'planning.js must create Day dropdown in prospecting rows (prospect-day class)'
    );
});


// ============================================================
// Section 4: Developing Pipeline - New Pipeline Total Calculation
// ============================================================
console.log('\nSection 4: Developing Pipeline - New Pipeline Total Calculation');

test('should include developing pipeline projected amounts in New Pipeline total', () => {
    // Dev opp: amount=100000 at 2-Analysis(1%), projected to 3-Confirmation(10%)
    // New pipeline contribution = 100000 * 10 / 100 = 10000
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-1',
            amount: 100000,
            projectedStage: '3-Confirmation (10%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger recalculation
    const devSelect = sandbox.document.querySelector('.dev-projected-stage-select');
    if (devSelect) devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 10000, `New Pipeline total should be 10000 (100000*10/100), got ${value}`);
});

test('should sum multiple developing pipeline projected amounts', () => {
    // Dev opp 1: 100000 projected to 6-Solution(60%) => 100000 * 60 / 100 = 60000
    // Dev opp 2: 50000 projected to 3-Confirmation(10%) => 50000 * 10 / 100 = 5000
    // Total new pipeline from dev: 65000
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [
            { oppId: 'dev-2', amount: 100000, projectedStage: '6-Solution (60%)' },
            { oppId: 'dev-3', amount: 50000, projectedStage: '3-Confirmation (10%)' }
        ],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const devSelects = sandbox.document.querySelectorAll('.dev-projected-stage-select');
    devSelects.forEach(s => s.dispatchEvent(new sandbox.window.Event('change', { bubbles: true })));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 65000, `New Pipeline total should be 65000 (60000+5000), got ${value}`);
});

test('should return 0 for developing pipeline when no projected stage selected', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-4',
            amount: 100000,
            projectedStage: ''
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const devSelect = sandbox.document.querySelector('.dev-projected-stage-select');
    if (devSelect) {
        devSelect.value = '';
        devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 0, `New Pipeline total should be 0 with no projected stage, got ${value}`);
});


// ============================================================
// Section 5: Combined Developing + Prospecting in New Pipeline Total
// ============================================================
console.log('\nSection 5: Combined Developing + Prospecting Total');

test('should combine developing pipeline and prospecting amounts in New Pipeline total', () => {
    // Dev opp: 100000 projected to 5-Specifications(30%) => 100000 * 30 / 100 = 30000
    // Prospect 1: expected_value = 20000
    // Prospect 2: expected_value = 15000
    // Total: 30000 + 20000 + 15000 = 65000
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-5',
            amount: 100000,
            projectedStage: '5-Specifications (30%)'
        }],
        prospectingRows: [
            { source: 'Cold Call', amount: 20000, description: 'Lead A' },
            { source: 'Referral', amount: 15000, description: 'Lead B' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger recalculation
    const devSelect = sandbox.document.querySelector('.dev-projected-stage-select');
    if (devSelect) devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 65000, `New Pipeline total should be 65000 (30000+20000+15000), got ${value}`);
});

test('should combine all three sources: existing pipeline progression, developing, and prospecting', () => {
    // Existing pipeline: opp at 3-Confirmation(10%) projected to 6-Solution(60%), category=progression
    //   -> progression = 80000 * (60-10) / 100 = 40000
    //   -> new pipeline contribution from existing: 80000 * 60 / 100 = 48000
    // Dev opp: 50000 projected to 7-Closing(90%) => 50000 * 90 / 100 = 45000
    // Prospect: expected_value = 10000
    // Total new pipeline = 48000 + 45000 + 10000 = 103000
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-x',
            amount: 80000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '6-Solution (60%)',
            category: 'progression'
        }],
        developingPipelineRows: [{
            oppId: 'dev-6',
            amount: 50000,
            projectedStage: '7-Closing (90%)'
        }],
        prospectingRows: [
            { source: 'Event', amount: 10000, description: 'Conference lead' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger recalculation on all selects
    const allSelects = [
        ...sandbox.document.querySelectorAll('.projected-stage-select'),
        ...sandbox.document.querySelectorAll('.dev-projected-stage-select')
    ];
    allSelects.forEach(s => s.dispatchEvent(new sandbox.window.Event('change', { bubbles: true })));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    // Developing (45000) + Prospecting (10000) + Existing pipeline progression contribution
    assert.ok(value >= 55000, `New Pipeline total should include developing (45000) + prospecting (10000) = at least 55000, got ${value}`);
});


// ============================================================
// Section 6: Add Prospecting Row with Source Type Dropdown
// ============================================================
console.log('\nSection 6: Add Prospecting Row with Source Type Dropdown');

test('should add a prospecting row with Source Type as a <select> dropdown', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 10000, description: 'Initial' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Click Add Row
    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.ok(tbody, 'Prospecting table body should exist');
    assert.strictEqual(tbody.children.length, 2, `Should have 2 rows after Add, got ${tbody.children.length}`);

    // Check the new row has a select element for source type
    const newRow = tbody.children[1];
    const sourceSelect = newRow ? newRow.querySelector('.prospect-source') : null;
    assert.ok(sourceSelect, 'New prospecting row must have a .prospect-source element');
    assert.strictEqual(
        sourceSelect.tagName, 'SELECT',
        `Source Type must be a SELECT element, got ${sourceSelect ? sourceSelect.tagName : 'null'}`
    );
});

test('should add a prospecting row with Day dropdown', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Referral', amount: 5000, description: 'Test' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[1];
    const daySelect = newRow ? newRow.querySelector('.prospect-day') : null;
    assert.ok(daySelect, 'New prospecting row must have a .prospect-day dropdown element');
    assert.strictEqual(
        daySelect.tagName, 'SELECT',
        `Day must be a SELECT element, got ${daySelect ? daySelect.tagName : 'null'}`
    );
});

test('should add a prospecting row with Expected Value number input', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Event', amount: 8000, description: 'Test' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[1];
    const amountInput = newRow ? newRow.querySelector('.prospect-amount') : null;
    assert.ok(amountInput, 'New prospecting row must have a .prospect-amount input');
    assert.strictEqual(
        amountInput.type, 'number',
        `Expected Value input type must be 'number', got '${amountInput ? amountInput.type : 'null'}'`
    );
});

test('should add a prospecting row with Description text input', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'LinkedIn', amount: 12000, description: 'Test' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[1];

    // Should have a text input for description (not the amount input)
    const inputs = newRow ? newRow.querySelectorAll('input') : [];
    const textInputs = inputs.filter(i => i.type === 'text');
    assert.ok(
        textInputs.length >= 1,
        `New prospecting row must have at least 1 text input for Description, found ${textInputs.length}`
    );
});

test('should add a prospecting row with Remove button', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Website', amount: 6000, description: 'Test' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[1];
    const removeBtn = newRow ? newRow.querySelector('.remove-prospect-row') : null;
    assert.ok(removeBtn, 'New prospecting row must have a .remove-prospect-row button');
});


// ============================================================
// Section 7: Remove Prospecting Row Updates Total
// ============================================================
console.log('\nSection 7: Remove Prospecting Row');

test('should remove prospecting row and update New Pipeline total', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 25000, description: 'Row 1' },
            { source: 'Referral', amount: 15000, description: 'Row 2' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger initial calc
    const amountInput = sandbox.document.querySelector('.prospect-amount');
    if (amountInput) amountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    // Remove first row (25000)
    const removeBtn = sandbox.document.querySelector('.remove-prospect-row');
    if (removeBtn) {
        const clickEvent = new sandbox.window.Event('click', { bubbles: true });
        clickEvent.target = removeBtn;
        removeBtn.dispatchEvent(clickEvent);
    }

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.strictEqual(tbody.children.length, 1, `Should have 1 row after remove, got ${tbody.children.length}`);

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 15000, `New Pipeline total should be 15000 after removing 25000 row, got ${value}`);
});


// ============================================================
// Section 8: Developing Pipeline Projected Stage Change Event
// ============================================================
console.log('\nSection 8: Developing Pipeline Event Handling');

test('should recalculate New Pipeline total when developing pipeline projected stage changes', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-7',
            amount: 200000,
            projectedStage: '3-Confirmation (10%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Initial: 200000 * 10 / 100 = 20000
    const devSelect = sandbox.document.querySelector('.dev-projected-stage-select');
    if (devSelect) {
        devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    let newPipelineTotal = sandbox.elements['total-new-pipeline'];
    let value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 20000, `Initial New Pipeline total should be 20000, got ${value}`);

    // Change to 7-Closing(90%): 200000 * 90 / 100 = 180000
    if (devSelect) {
        devSelect.value = '7-Closing (90%)';
        devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 180000, `After change, New Pipeline total should be 180000, got ${value}`);
});


// ============================================================
// Section 9: Edge Cases - Empty Developing Pipeline
// ============================================================
console.log('\nSection 9: Edge Cases');

test('should handle empty developing pipeline table (no analysis opportunities)', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 5000, description: 'Solo prospect' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const amountInput = sandbox.document.querySelector('.prospect-amount');
    if (amountInput) amountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 5000, `New Pipeline total should be 5000 with only prospecting, got ${value}`);
});

test('should handle zero amount in developing pipeline', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-8',
            amount: 0,
            projectedStage: '7-Closing (90%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const devSelect = sandbox.document.querySelector('.dev-projected-stage-select');
    if (devSelect) devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 0, `New Pipeline total should be 0 with zero amount developing opp, got ${value}`);
});

test('should handle all sections empty', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 0, `New Pipeline total should be 0 with all sections empty, got ${value}`);
});


// ============================================================
// Section 10: Prospecting Row Index Management
// ============================================================
console.log('\nSection 10: Prospecting Row Index Management');

test('should update name indices when adding new prospecting rows', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 10000, description: 'First' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Add two rows
    const addBtn = sandbox.elements['add-prospect-row'];
    for (let i = 0; i < 2; i++) {
        const clickEvent = new sandbox.window.Event('click', { bubbles: true });
        clickEvent.target = addBtn;
        addBtn.dispatchEvent(clickEvent);
    }

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.strictEqual(tbody.children.length, 3, `Should have 3 rows, got ${tbody.children.length}`);

    // Check that each row has distinct input names (index 0, 1, 2)
    const amountInputs = tbody.querySelectorAll('.prospect-amount');
    const names = amountInputs.map(input => input.getAttribute('name'));
    const uniqueNames = new Set(names);
    assert.strictEqual(
        uniqueNames.size, 3,
        `Each row's amount input should have a unique name, found ${uniqueNames.size} unique names out of ${names.length}`
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
