/**
 * US-005: Planning JS - Source Type Dropdown Population Tests
 *
 * Tests that planning.js correctly populates Source Type dropdowns
 * with values from window.LF_SOURCE_TYPES when adding new prospecting rows.
 *
 * Key behaviors tested:
 *   1. New prospecting rows created from scratch (no template row) get Source Type
 *      dropdown populated from window.LF_SOURCE_TYPES
 *   2. Source Type dropdown has correct number of options (empty + source types)
 *   3. Cloned rows also have correct Source Type <select> (not text input)
 *   4. Day dropdown in new rows has Mon-Fri options
 *   5. Developing pipeline item_type='developing' is distinguished in data
 *
 * These tests MUST FAIL until the implementation handles source type population correctly.
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
 * Creates a minimal sandbox that simulates the browser environment.
 * Extended to support LF_SOURCE_TYPES for source type dropdown population tests.
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
            _selectedIndex: 0,

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
            get selectedIndex() { return this._selectedIndex; },
            set selectedIndex(v) { this._selectedIndex = v; },
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
                clone._selectedIndex = this._selectedIndex;
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
        // tr.prospecting-row
        const compoundMatch = selector.match(/^(\w+)\.(.+)$/);
        if (compoundMatch) {
            const [, tag, cls] = compoundMatch;
            return el.tagName.toLowerCase() === tag.toLowerCase()
                && (el.className || '').split(/\s+/).includes(cls);
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
        const tr = createElement('tr', { 'data-opportunity-id': oppId, class: 'developing-pipeline-row' });

        const amountTd = createElement('td', { class: 'dev-amount', 'data-amount': String(row.amount || 0) });
        amountTd._textContent = String(row.amount || 0);
        tr.appendChild(amountTd);

        const stageTd = createElement('td', { class: 'dev-current-stage', 'data-stage': '2-Analysis (1%)' });
        stageTd._textContent = '2-Analysis (1%)';
        tr.appendChild(stageTd);

        const projSelect = createElement('select', { name: `dev_projected_stage[${oppId}]`, class: 'dev-projected-stage-select' });
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

    // ---- Build Prospecting Table (empty - no rows for from-scratch creation test) ----
    const prospectTableBody = createElement('tbody');
    prospectingRows.forEach((row, idx) => {
        const tr = createElement('tr', { class: 'prospecting-row', 'data-prospect-index': String(idx) });

        const sourceSelect = createElement('select', { name: `prospect_source[${idx}]`, class: 'prospect-source' });
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

        const daySelect = createElement('select', { name: `prospect_day[${idx}]`, class: 'prospect-day' });
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].forEach(d => {
            const opt = createElement('option', { value: d });
            opt._textContent = d.charAt(0).toUpperCase() + d.slice(1);
            daySelect.appendChild(opt);
        });
        const dayTd = createElement('td');
        dayTd.appendChild(daySelect);
        tr.appendChild(dayTd);

        const amountInput = createElement('input', {
            type: 'number', name: `prospect_amount[${idx}]`, class: 'prospect-amount',
            value: String(row.amount || 0)
        });
        const amountTd = createElement('td');
        amountTd.appendChild(amountInput);
        tr.appendChild(amountTd);

        const descInput = createElement('input', { type: 'text', name: `prospect_description[${idx}]`, value: row.description || '' });
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
        LF_SOURCE_TYPES: sourceTypes,
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
// Section 1: From-Scratch Row Creation - Source Type Dropdown Population
// ============================================================
console.log('Section 1: From-Scratch Row - Source Type Dropdown Options');

test('should populate Source Type dropdown with LF_SOURCE_TYPES when creating from scratch', () => {
    // Start with NO prospecting rows - forces from-scratch creation path
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Click Add Row to create from scratch
    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.ok(tbody, 'Prospecting table body should exist');
    assert.strictEqual(tbody.children.length, 1, `Should have 1 row after Add, got ${tbody.children.length}`);

    const newRow = tbody.children[0];
    const sourceSelect = newRow ? newRow.querySelector('.prospect-source') : null;
    assert.ok(sourceSelect, 'New row must have .prospect-source element');
    assert.strictEqual(sourceSelect.tagName, 'SELECT', 'Source Type must be a SELECT element');

    // Count option children: empty option + 5 source types = 6
    const options = sourceSelect.children.filter(c => c.tagName === 'OPTION');
    assert.strictEqual(
        options.length, 6,
        `Source Type dropdown should have 6 options (1 empty + 5 source types), got ${options.length}`
    );
});

test('should include all source type values in from-scratch dropdown', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[0];
    const sourceSelect = newRow.querySelector('.prospect-source');
    const optionValues = sourceSelect.children
        .filter(c => c.tagName === 'OPTION')
        .map(o => o.value || o.getAttribute('value'))
        .filter(v => v); // exclude empty option

    SOURCE_TYPES.forEach(st => {
        assert.ok(
            optionValues.includes(st),
            `Source Type dropdown must include '${st}', got: [${optionValues.join(', ')}]`
        );
    });
});


// ============================================================
// Section 2: From-Scratch Row - Day Dropdown Population
// ============================================================
console.log('\nSection 2: From-Scratch Row - Day Dropdown Options');

test('should populate Day dropdown with Mon-Fri when creating from scratch', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[0];
    const daySelect = newRow ? newRow.querySelector('.prospect-day') : null;
    assert.ok(daySelect, 'New row must have .prospect-day element');
    assert.strictEqual(daySelect.tagName, 'SELECT', 'Day must be a SELECT element');

    // Should have 5 options (Mon-Fri)
    const options = daySelect.children.filter(c => c.tagName === 'OPTION');
    assert.strictEqual(
        options.length, 5,
        `Day dropdown should have 5 options (Mon-Fri), got ${options.length}`
    );
});


// ============================================================
// Section 3: From-Scratch Row - Expected Value and Description
// ============================================================
console.log('\nSection 3: From-Scratch Row - Input Fields');

test('should create Expected Value as number input from scratch', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[0];
    const amountInput = newRow ? newRow.querySelector('.prospect-amount') : null;
    assert.ok(amountInput, 'New row must have .prospect-amount input');
    assert.strictEqual(amountInput.type, 'number', `Expected Value type must be 'number', got '${amountInput.type}'`);
});

test('should create Description as text input from scratch', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[0];
    const inputs = newRow ? newRow.querySelectorAll('input') : [];
    const textInputs = inputs.filter(i => i.type === 'text');
    assert.ok(textInputs.length >= 1, `New row must have at least 1 text input for Description, found ${textInputs.length}`);
});

test('should create Remove button from scratch', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[0];
    const removeBtn = newRow ? newRow.querySelector('.remove-prospect-row') : null;
    assert.ok(removeBtn, 'New row must have .remove-prospect-row button');
});


// ============================================================
// Section 4: Multiple Source Type Additions
// ============================================================
console.log('\nSection 4: Multiple From-Scratch Rows');

test('should create multiple from-scratch rows each with Source Type dropdown', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    for (let i = 0; i < 3; i++) {
        const clickEvent = new sandbox.window.Event('click', { bubbles: true });
        clickEvent.target = addBtn;
        addBtn.dispatchEvent(clickEvent);
    }

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.strictEqual(tbody.children.length, 3, `Should have 3 rows, got ${tbody.children.length}`);

    // Every row must have a Source Type <select> with options
    for (let i = 0; i < 3; i++) {
        const row = tbody.children[i];
        const sourceSelect = row.querySelector('.prospect-source');
        assert.ok(sourceSelect, `Row ${i} must have .prospect-source`);
        assert.strictEqual(sourceSelect.tagName, 'SELECT', `Row ${i} Source Type must be SELECT`);
        const options = sourceSelect.children.filter(c => c.tagName === 'OPTION');
        assert.ok(options.length >= 6, `Row ${i} Source Type dropdown should have at least 6 options, got ${options.length}`);
    }
});


// ============================================================
// Section 5: Dynamic Row Amount Updates New Pipeline Total
// ============================================================
console.log('\nSection 5: Dynamic Row Amounts in Total');

test('should include from-scratch row amounts in New Pipeline total', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Add a row from scratch
    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[0];
    const amountInput = newRow.querySelector('.prospect-amount');
    assert.ok(amountInput, 'Row must have .prospect-amount');

    amountInput.value = '25000';
    amountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 25000, `New Pipeline total should be 25000, got ${value}`);
});


// ============================================================
// Section 6: Remove From-Scratch Row
// ============================================================
console.log('\nSection 6: Remove From-Scratch Row');

test('should remove from-scratch row via Remove button', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Add 2 rows from scratch
    const addBtn = sandbox.elements['add-prospect-row'];
    for (let i = 0; i < 2; i++) {
        const clickEvent = new sandbox.window.Event('click', { bubbles: true });
        clickEvent.target = addBtn;
        addBtn.dispatchEvent(clickEvent);
    }

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.strictEqual(tbody.children.length, 2, 'Should have 2 rows');

    // Remove first row
    const removeBtn = tbody.children[0].querySelector('.remove-prospect-row');
    assert.ok(removeBtn, 'Row must have remove button');
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = removeBtn;
    removeBtn.dispatchEvent(clickEvent);

    assert.strictEqual(tbody.children.length, 1, `Should have 1 row after remove, got ${tbody.children.length}`);
});


// ============================================================
// Section 7: Custom Source Types
// ============================================================
console.log('\nSection 7: Custom Source Types');

test('should use custom LF_SOURCE_TYPES values in from-scratch dropdown', () => {
    const customSourceTypes = ['Phone', 'Trade Show', 'Webinar'];
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [],
        prospectingRows: [],
        sourceTypes: customSourceTypes
    });
    loadPlanningJsInSandbox(sandbox);

    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const newRow = tbody.children[0];
    const sourceSelect = newRow.querySelector('.prospect-source');
    const optionValues = sourceSelect.children
        .filter(c => c.tagName === 'OPTION')
        .map(o => o.value || o.getAttribute('value'))
        .filter(v => v);

    customSourceTypes.forEach(st => {
        assert.ok(
            optionValues.includes(st),
            `Custom source type '${st}' must be in dropdown, got: [${optionValues.join(', ')}]`
        );
    });

    // Should NOT contain default source types
    assert.ok(
        !optionValues.includes('Cold Call'),
        'Should NOT contain default source types when custom ones are provided'
    );
});


// ============================================================
// Section 8: Developing Pipeline with Prospecting Combined
// ============================================================
console.log('\nSection 8: Developing + From-Scratch Prospecting Combined');

test('should combine developing pipeline + from-scratch prospecting in New Pipeline total', () => {
    // Dev: 100000 * 30/100 = 30000
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        developingPipelineRows: [{
            oppId: 'dev-1',
            amount: 100000,
            projectedStage: '5-Specifications (30%)'
        }],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Add a prospect row from scratch
    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    const amountInput = tbody.children[0].querySelector('.prospect-amount');
    amountInput.value = '15000';

    // Trigger calculation
    const devSelect = sandbox.document.querySelector('.dev-projected-stage-select');
    if (devSelect) devSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    amountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    // Dev: 30000 + Prospect: 15000 = 45000
    assert.strictEqual(value, 45000, `New Pipeline total should be 45000 (30000+15000), got ${value}`);
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
