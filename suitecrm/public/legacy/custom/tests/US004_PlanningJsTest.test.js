/**
 * US-004: Planning Tool JavaScript Tests
 *
 * Tests that custom/modules/LF_WeeklyPlan/js/planning.js exists and implements:
 *   1. Pipeline progression recalculation: amount * (projected_prob - current_prob)
 *   2. Dynamic totals row updates (Closing, At Risk, Progression, New Pipeline)
 *   3. Add/Remove rows for prospecting section
 *   4. Event delegation on form container
 *   5. Reads stage probabilities from window.LF_STAGE_PROBS
 *   6. Uses vanilla JavaScript (no npm dependencies)
 *
 * These tests MUST FAIL until the implementation is created.
 *
 * Test approach: Structural pattern matching (no npm dependencies)
 * plus function extraction for pure logic testing via vm module.
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
 * enough for testing planning.js logic without jsdom.
 *
 * Sets up: window, document with basic DOM stubs, event handling,
 * and the LF_STAGE_PROBS global.
 */
function createBrowserSandbox(options = {}) {
    const {
        stageProbabilities = STAGE_PROBS,
        pipelineRows = [],
        prospectingRows = []
    } = options;

    // Simple element store for getElementById / querySelector
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
                // Bubble up
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
                // For select elements, return the selected option
                return this.children.filter(c => c.attributes.selected);
            }
        };
        return el;
    }

    // Very basic selector matching (supports id, class, tag, attribute selectors)
    function matchesSelector(el, selector) {
        if (!el || !el.tagName) return false;
        // ID selector
        if (selector.startsWith('#')) {
            return el.id === selector.slice(1);
        }
        // Class selector
        if (selector.startsWith('.')) {
            const cls = selector.slice(1);
            return (el.className || '').split(/\s+/).includes(cls);
        }
        // Tag selector
        if (/^[a-z]+$/i.test(selector)) {
            return el.tagName.toLowerCase() === selector.toLowerCase();
        }
        // Attribute selector: [name="value"]
        const attrMatch = selector.match(/\[(\w+)(?:="([^"]*)")?\]/);
        if (attrMatch) {
            const [, attr, val] = attrMatch;
            if (val !== undefined) {
                return el.getAttribute(attr) === val;
            }
            return el.getAttribute(attr) !== null;
        }
        // data-* selector
        if (selector.startsWith('[data-')) {
            const m = selector.match(/\[data-([\w-]+)="?([^"\]]*)"?\]/);
            if (m) return el.getAttribute('data-' + m[1]) === m[2];
        }
        return false;
    }

    function querySelectorInTree(root, selector) {
        // Simple BFS
        const parts = selector.split(/\s+/);
        if (parts.length === 1) {
            // Single selector - search children
            const queue = [...(root.children || [])];
            while (queue.length) {
                const node = queue.shift();
                if (matchesSelector(node, selector)) return node;
                if (node.children) queue.push(...node.children);
            }
            return null;
        }
        // Multi-part selector - simplified: find elements matching last part under root
        const lastPart = parts[parts.length - 1];
        const results = querySelectorAllInTree(root, lastPart);
        return results.length > 0 ? results[0] : null;
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

    // Build DOM tree for pipeline rows
    const pipelineTableBody = createElement('tbody');
    pipelineRows.forEach((row, idx) => {
        const oppId = row.oppId || `opp-${idx}`;
        const tr = createElement('tr', {
            'data-opportunity-id': oppId
        });

        // Amount cell
        const amountTd = createElement('td', {
            class: 'amount',
            'data-amount': String(row.amount || 0)
        });
        amountTd._textContent = String(row.amount || 0);
        tr.appendChild(amountTd);

        // Current stage cell
        const stageTd = createElement('td', {
            class: 'current-stage',
            'data-stage': row.currentStage || ''
        });
        stageTd._textContent = row.currentStage || '';
        tr.appendChild(stageTd);

        // Projected stage select
        const projSelect = createElement('select', {
            name: `projected_stage[${oppId}]`,
            class: 'projected-stage-select'
        });
        const emptyOpt = createElement('option', { value: '' });
        emptyOpt._textContent = '-- Select --';
        projSelect.appendChild(emptyOpt);

        const stageOptions = [
            '3-Confirmation (10%)',
            '5-Specifications (30%)',
            '6-Solution (60%)',
            '7-Closing (90%)'
        ];
        stageOptions.forEach(s => {
            const opt = createElement('option', { value: s });
            opt._textContent = s;
            if (row.projectedStage === s) {
                opt.attributes.selected = 'selected';
                projSelect.value = s;
            }
            projSelect.appendChild(opt);
        });
        if (row.projectedStage) projSelect.attributes.value = row.projectedStage;
        const projTd = createElement('td');
        projTd.appendChild(projSelect);
        tr.appendChild(projTd);

        // Category select
        const catSelect = createElement('select', {
            name: `category[${oppId}]`,
            class: 'category-select'
        });
        ['closing', 'at_risk', 'progression', 'skip'].forEach(cat => {
            const opt = createElement('option', { value: cat });
            opt._textContent = cat;
            if (row.category === cat) {
                opt.attributes.selected = 'selected';
                catSelect.value = cat;
            }
            catSelect.appendChild(opt);
        });
        if (row.category) catSelect.attributes.value = row.category;
        const catTd = createElement('td');
        catTd.appendChild(catSelect);
        tr.appendChild(catTd);

        // Day select
        const daySelect = createElement('select', {
            name: `day[${oppId}]`,
            class: 'day-select'
        });
        const dayTd = createElement('td');
        dayTd.appendChild(daySelect);
        tr.appendChild(dayTd);

        // Plan input
        const planInput = createElement('input', {
            type: 'text',
            name: `plan[${oppId}]`,
            value: ''
        });
        const planTd = createElement('td');
        planTd.appendChild(planInput);
        tr.appendChild(planTd);

        // Pipeline progression cell
        const progTd = createElement('td', {
            class: 'pipeline-progression',
            'data-value': '0'
        });
        progTd._textContent = '0';
        tr.appendChild(progTd);

        pipelineTableBody.appendChild(tr);
    });

    const pipelineTable = createElement('table', { id: 'pipeline-table', class: 'list view table-responsive' });
    pipelineTable.appendChild(pipelineTableBody);

    // Build prospecting table
    const prospectTableBody = createElement('tbody');
    prospectingRows.forEach((row, idx) => {
        const tr = createElement('tr', { class: 'prospecting-row', 'data-prospect-index': String(idx) });

        const sourceInput = createElement('input', {
            type: 'text',
            name: `prospect_source[${idx}]`,
            class: 'prospect-source',
            value: row.source || ''
        });
        const sourceTd = createElement('td');
        sourceTd.appendChild(sourceInput);
        tr.appendChild(sourceTd);

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

        const removeBtn = createElement('button', {
            type: 'button',
            class: 'remove-prospect-row'
        });
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
    container.appendChild(totalsRow);
    container.appendChild(prospectTable);
    container.appendChild(addBtn);

    // Store elements by id for getElementById
    function indexElements(el) {
        if (el.id) elements[el.id] = el;
        (el.children || []).forEach(indexElements);
    }
    indexElements(container);

    // Document mock
    const document = {
        getElementById(id) {
            return elements[id] || null;
        },
        querySelector(selector) {
            return querySelectorInTree(container, selector) || (matchesSelector(container, selector) ? container : null);
        },
        querySelectorAll(selector) {
            const result = querySelectorAllInTree(container, selector);
            if (matchesSelector(container, selector)) result.unshift(container);
            return result;
        },
        createElement(tag) {
            return createElement(tag);
        },
        addEventListener(type, handler) {
            if (!eventListeners[type]) eventListeners[type] = [];
            eventListeners[type].push(handler);
        },
        dispatchEvent(event) {
            const handlers = eventListeners[event.type] || [];
            handlers.forEach(h => h(event));
        }
    };

    // Window mock
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
// Section 1: File Existence
// ============================================================
console.log('Section 1: File Existence');

test('planning.js file should exist', () => {
    assert.ok(
        fs.existsSync(jsFile),
        `planning.js must exist at: custom/modules/LF_WeeklyPlan/js/planning.js`
    );
});

test('planning.js should be a regular file', () => {
    assert.ok(
        fs.statSync(jsFile).isFile(),
        'planning.js must be a regular file, not a directory'
    );
});


// ============================================================
// Section 2: File Content Structure
// ============================================================
console.log('\nSection 2: File Content Structure');

test('planning.js should not be empty', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(content.trim().length > 0, 'planning.js must not be empty');
});

test('planning.js should use vanilla JavaScript (no require/import of npm packages)', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    const hasNpmRequire = /require\s*\(\s*['"][^./]/.test(content);
    assert.ok(!hasNpmRequire, 'planning.js must not use require() for npm packages');
    const hasNpmImport = /import\s+.*\s+from\s+['"][^./]/.test(content);
    assert.ok(!hasNpmImport, 'planning.js must not use import from npm packages');
});

test('planning.js should reference LF_STAGE_PROBS or stageProbabilities', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    const hasStageProbs = content.includes('LF_STAGE_PROBS') || content.includes('stageProbabilities');
    assert.ok(hasStageProbs, 'planning.js must reference window.LF_STAGE_PROBS or stageProbabilities for stage probabilities');
});

test('planning.js should use event delegation (addEventListener on container)', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('addEventListener'),
        'planning.js must use addEventListener for event delegation'
    );
});

test('planning.js should handle change events for recalculation', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('change'),
        'planning.js must handle change events for dropdown recalculation'
    );
});

test('planning.js should handle click events for add/remove buttons', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        content.includes('click'),
        'planning.js must handle click events for add/remove row buttons'
    );
});

test('planning.js should contain pipeline progression calculation logic', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    // The formula involves: amount * (projected_prob - current_prob)
    const hasCalcPattern = content.includes('projected') && content.includes('current')
        && (content.includes('*') || content.includes('amount'));
    assert.ok(hasCalcPattern, 'planning.js must contain pipeline progression calculation logic (amount * (projected_prob - current_prob))');
});

test('planning.js should contain totals update logic', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    const hasTotals = content.includes('total') || content.includes('Total');
    assert.ok(hasTotals, 'planning.js must contain totals update logic');
});

test('planning.js should reference closing category in totals', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        /closing/i.test(content),
        'planning.js must reference Closing category for totals calculation'
    );
});

test('planning.js should reference at risk category in totals', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        /at.?risk/i.test(content),
        'planning.js must reference At Risk category for totals calculation'
    );
});

test('planning.js should reference progression in totals', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        /progression/i.test(content),
        'planning.js must reference Progression for totals calculation'
    );
});

test('planning.js should reference new pipeline in totals', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        /new.?pipeline/i.test(content),
        'planning.js must reference New Pipeline for totals calculation'
    );
});

test('planning.js should have add row functionality for prospecting', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        /add.*row/i.test(content) || content.includes('clone') || content.includes('appendChild'),
        'planning.js must have add row functionality (clone/append) for prospecting section'
    );
});

test('planning.js should have remove row functionality for prospecting', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    assert.ok(
        /remove.*row/i.test(content) || content.includes('removeChild') || content.includes('remove()'),
        'planning.js must have remove row functionality for prospecting section'
    );
});


// ============================================================
// Section 3: Pipeline Progression Calculation (Happy Path)
// ============================================================
console.log('\nSection 3: Pipeline Progression Calculation');

test('should calculate progression as amount * (projected_prob - current_prob) / 100', () => {
    // Opp: amount=100000, current=3-Confirmation(10%), projected=6-Solution(60%)
    // Expected: 100000 * (60 - 10) / 100 = 50000
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-1',
            amount: 100000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '6-Solution (60%)',
            category: 'progression'
        }]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger change event on the projected stage select
    const select = sandbox.document.querySelector('.projected-stage-select');
    const changeEvent = new sandbox.window.Event('change', { bubbles: true });
    changeEvent.target = select;
    select.dispatchEvent(changeEvent);

    // Check the pipeline progression cell value
    const row = sandbox.container.querySelector('[data-opportunity-id="opp-1"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 50000, `Pipeline progression should be 50000, got ${value}`);
});

test('should calculate progression correctly for 3-Confirmation to 7-Closing', () => {
    // amount=200000, current=3-Confirmation(10%), projected=7-Closing(90%)
    // Expected: 200000 * (90 - 10) / 100 = 160000
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-2',
            amount: 200000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '7-Closing (90%)',
            category: 'progression'
        }]
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.container.querySelector('[name="projected_stage[opp-2]"]');
    if (!select) {
        const anySelect = sandbox.document.querySelector('.projected-stage-select');
        if (anySelect) {
            anySelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
        }
    } else {
        select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    const row = sandbox.container.querySelector('[data-opportunity-id="opp-2"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 160000, `Pipeline progression should be 160000, got ${value}`);
});

test('should calculate progression for adjacent stages (5-Specifications to 6-Solution)', () => {
    // amount=50000, current=5-Specifications(30%), projected=6-Solution(60%)
    // Expected: 50000 * (60 - 30) / 100 = 15000
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-3',
            amount: 50000,
            currentStage: '5-Specifications (30%)',
            projectedStage: '6-Solution (60%)',
            category: 'progression'
        }]
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.projected-stage-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const row = sandbox.container.querySelector('[data-opportunity-id="opp-3"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 15000, `Pipeline progression should be 15000, got ${value}`);
});


// ============================================================
// Section 4: Pipeline Progression Edge Cases
// ============================================================
console.log('\nSection 4: Pipeline Progression Edge Cases');

test('should return 0 progression when projected stage is empty (-- Select --)', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-4',
            amount: 100000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '',
            category: 'progression'
        }]
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.projected-stage-select');
    if (select) {
        select.value = '';
        select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    const row = sandbox.container.querySelector('[data-opportunity-id="opp-4"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 0, `Pipeline progression should be 0 when no projected stage, got ${value}`);
});

test('should return 0 progression when amount is 0', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-5',
            amount: 0,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '7-Closing (90%)',
            category: 'progression'
        }]
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.projected-stage-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const row = sandbox.container.querySelector('[data-opportunity-id="opp-5"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 0, `Pipeline progression should be 0 when amount is 0, got ${value}`);
});

test('should handle projected stage same as current stage (progression = 0)', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-6',
            amount: 100000,
            currentStage: '5-Specifications (30%)',
            projectedStage: '5-Specifications (30%)',
            category: 'progression'
        }]
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.projected-stage-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const row = sandbox.container.querySelector('[data-opportunity-id="opp-6"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 0, `Pipeline progression should be 0 when projected equals current, got ${value}`);
});


// ============================================================
// Section 5: Totals Row - Closing Total
// ============================================================
console.log('\nSection 5: Totals Row - Closing Total');

test('should calculate Closing total as sum of amounts where category=closing', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [
            { oppId: 'opp-a', amount: 50000, currentStage: '7-Closing (90%)', projectedStage: '', category: 'closing' },
            { oppId: 'opp-b', amount: 30000, currentStage: '7-Closing (90%)', projectedStage: '', category: 'closing' },
            { oppId: 'opp-c', amount: 20000, currentStage: '5-Specifications (30%)', projectedStage: '', category: 'progression' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger recalculation
    const select = sandbox.document.querySelector('.category-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const closingTotal = sandbox.elements['total-closing'];
    assert.ok(closingTotal, 'total-closing element should exist');
    const value = parseFloat(closingTotal.getAttribute('data-value') || closingTotal.textContent);
    assert.strictEqual(value, 80000, `Closing total should be 80000 (50000+30000), got ${value}`);
});

test('should return 0 Closing total when no items have category=closing', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [
            { oppId: 'opp-d', amount: 50000, currentStage: '5-Specifications (30%)', projectedStage: '', category: 'at_risk' },
            { oppId: 'opp-e', amount: 30000, currentStage: '5-Specifications (30%)', projectedStage: '', category: 'progression' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.category-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const closingTotal = sandbox.elements['total-closing'];
    assert.ok(closingTotal, 'total-closing element should exist');
    const value = parseFloat(closingTotal.getAttribute('data-value') || closingTotal.textContent);
    assert.strictEqual(value, 0, `Closing total should be 0 when no closing items, got ${value}`);
});


// ============================================================
// Section 6: Totals Row - At Risk Total
// ============================================================
console.log('\nSection 6: Totals Row - At Risk Total');

test('should calculate At Risk total as sum of amounts where category=at_risk', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [
            { oppId: 'opp-f', amount: 40000, currentStage: '6-Solution (60%)', projectedStage: '', category: 'at_risk' },
            { oppId: 'opp-g', amount: 25000, currentStage: '5-Specifications (30%)', projectedStage: '', category: 'at_risk' },
            { oppId: 'opp-h', amount: 60000, currentStage: '7-Closing (90%)', projectedStage: '', category: 'closing' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.category-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const atRiskTotal = sandbox.elements['total-at-risk'];
    assert.ok(atRiskTotal, 'total-at-risk element should exist');
    const value = parseFloat(atRiskTotal.getAttribute('data-value') || atRiskTotal.textContent);
    assert.strictEqual(value, 65000, `At Risk total should be 65000 (40000+25000), got ${value}`);
});


// ============================================================
// Section 7: Totals Row - Progression Total
// ============================================================
console.log('\nSection 7: Totals Row - Progression Total');

test('should calculate Progression total as sum of pipeline progression values', () => {
    // Row 1: 100000 * (60-10)/100 = 50000
    // Row 2: 50000 * (90-30)/100 = 30000
    // Total: 80000
    const sandbox = createBrowserSandbox({
        pipelineRows: [
            {
                oppId: 'opp-i',
                amount: 100000,
                currentStage: '3-Confirmation (10%)',
                projectedStage: '6-Solution (60%)',
                category: 'progression'
            },
            {
                oppId: 'opp-j',
                amount: 50000,
                currentStage: '5-Specifications (30%)',
                projectedStage: '7-Closing (90%)',
                category: 'progression'
            }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger change on both selects
    const selects = sandbox.document.querySelectorAll('.projected-stage-select');
    selects.forEach(s => s.dispatchEvent(new sandbox.window.Event('change', { bubbles: true })));

    const progressionTotal = sandbox.elements['total-progression'];
    assert.ok(progressionTotal, 'total-progression element should exist');
    const value = parseFloat(progressionTotal.getAttribute('data-value') || progressionTotal.textContent);
    assert.strictEqual(value, 80000, `Progression total should be 80000 (50000+30000), got ${value}`);
});


// ============================================================
// Section 8: Totals Row - New Pipeline Total
// ============================================================
console.log('\nSection 8: Totals Row - New Pipeline Total');

test('should calculate New Pipeline total including prospecting expected values', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 25000, description: 'New lead' },
            { source: 'Referral', amount: 35000, description: 'Partner ref' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger recalculation via prospect amount change
    const amountInput = sandbox.document.querySelector('.prospect-amount');
    if (amountInput) amountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 60000, `New Pipeline total should be 60000 (25000+35000), got ${value}`);
});

test('should return 0 New Pipeline total when no prospecting rows and no developing items', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [
            { oppId: 'opp-l', amount: 50000, currentStage: '7-Closing (90%)', projectedStage: '', category: 'closing' }
        ],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.category-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 0, `New Pipeline total should be 0 when no prospecting/developing items, got ${value}`);
});


// ============================================================
// Section 9: Totals Update on Any Form Value Change
// ============================================================
console.log('\nSection 9: Totals Update on Form Value Changes');

test('should update totals when category dropdown changes', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [
            { oppId: 'opp-m', amount: 75000, currentStage: '6-Solution (60%)', projectedStage: '', category: 'closing' },
            { oppId: 'opp-n', amount: 50000, currentStage: '5-Specifications (30%)', projectedStage: '', category: 'closing' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger initial calculation
    const categorySelect = sandbox.document.querySelector('.category-select');
    if (categorySelect) categorySelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const closingTotal = sandbox.elements['total-closing'];
    assert.ok(closingTotal, 'total-closing element should exist');
    let closingValue = parseFloat(closingTotal.getAttribute('data-value') || closingTotal.textContent);
    assert.strictEqual(closingValue, 125000, `Initial closing total should be 125000, got ${closingValue}`);

    // Change first from closing to at_risk
    if (categorySelect) {
        categorySelect.value = 'at_risk';
        categorySelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    closingValue = parseFloat(closingTotal.getAttribute('data-value') || closingTotal.textContent);
    assert.strictEqual(closingValue, 50000, `After change, closing total should be 50000, got ${closingValue}`);

    const atRiskTotal = sandbox.elements['total-at-risk'];
    const atRiskValue = parseFloat(atRiskTotal.getAttribute('data-value') || atRiskTotal.textContent);
    assert.strictEqual(atRiskValue, 75000, `After change, at risk total should be 75000, got ${atRiskValue}`);
});

test('should update totals when projected stage dropdown changes', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-p',
            amount: 100000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '',
            category: 'progression'
        }]
    });
    loadPlanningJsInSandbox(sandbox);

    // Select a projected stage
    const projSelect = sandbox.document.querySelector('.projected-stage-select');
    if (projSelect) {
        projSelect.value = '6-Solution (60%)';
        projSelect.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    const progressionTotal = sandbox.elements['total-progression'];
    assert.ok(progressionTotal, 'total-progression element should exist');
    const value = parseFloat(progressionTotal.getAttribute('data-value') || progressionTotal.textContent);
    // 100000 * (60 - 10) / 100 = 50000
    assert.strictEqual(value, 50000, `Progression total should be 50000, got ${value}`);
});

test('should update totals when prospecting amount changes', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 10000, description: 'Test' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const amountInput = sandbox.document.querySelector('.prospect-amount');
    if (amountInput) {
        amountInput.value = '50000';
        amountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));
    }

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 50000, `New Pipeline total should be 50000 after prospecting amount change, got ${value}`);
});


// ============================================================
// Section 10: Add Row for Prospecting Section
// ============================================================
console.log('\nSection 10: Add Row for Prospecting Section');

test('should add a new row when Add Row button is clicked', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 10000, description: 'Initial row' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.ok(tbody, 'Prospecting table body should exist');
    const initialRowCount = tbody.children.length;
    assert.strictEqual(initialRowCount, 1, 'Should start with 1 prospecting row');

    // Click Add Row button
    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    const newRowCount = tbody.children.length;
    assert.strictEqual(newRowCount, 2, `Should have 2 rows after Add Row click, got ${newRowCount}`);
});

test('should add multiple rows with successive clicks', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
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
    assert.ok(tbody, 'Prospecting table body should exist');
    const rowCount = tbody.children.length;
    assert.strictEqual(rowCount, 3, `Should have 3 rows after 3 Add Row clicks, got ${rowCount}`);
});


// ============================================================
// Section 11: Remove Row for Prospecting Section
// ============================================================
console.log('\nSection 11: Remove Row for Prospecting Section');

test('should remove a row when Remove button is clicked', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 10000, description: 'Row 1' },
            { source: 'Referral', amount: 20000, description: 'Row 2' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.strictEqual(tbody.children.length, 2, 'Should start with 2 rows');

    // Click Remove on first row
    const removeBtn = sandbox.document.querySelector('.remove-prospect-row');
    assert.ok(removeBtn, 'Remove button should exist');
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = removeBtn;
    removeBtn.dispatchEvent(clickEvent);

    const remainingRows = tbody.children.length;
    assert.strictEqual(remainingRows, 1, `Should have 1 row after remove, got ${remainingRows}`);
});

test('should update totals after removing a prospecting row', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        prospectingRows: [
            { source: 'Cold Call', amount: 25000, description: 'Row 1' },
            { source: 'Referral', amount: 35000, description: 'Row 2' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Initial trigger to calculate totals
    const amountInput = sandbox.document.querySelector('.prospect-amount');
    if (amountInput) amountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    // Remove first row (25000)
    const removeBtn = sandbox.document.querySelector('.remove-prospect-row');
    if (removeBtn) {
        const clickEvent = new sandbox.window.Event('click', { bubbles: true });
        clickEvent.target = removeBtn;
        removeBtn.dispatchEvent(clickEvent);
    }

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    assert.ok(newPipelineTotal, 'total-new-pipeline element should exist');
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 35000, `New Pipeline total should be 35000 after removing 25000 row, got ${value}`);
});


// ============================================================
// Section 12: Event Delegation Pattern
// ============================================================
console.log('\nSection 12: Event Delegation Pattern');

test('should use event delegation on form container rather than individual elements', () => {
    const content = fs.readFileSync(jsFile, 'utf8');
    // Event delegation: addEventListener on a container, not on each element
    const hasDelegation =
        (content.includes('lf-planning-container') || content.includes('planning-container')
        || content.includes('getElementById'))
        && content.includes('addEventListener');
    assert.ok(hasDelegation, 'planning.js must use event delegation on a container element');
});

test('should handle events from dynamically added prospecting rows', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // Add a row dynamically
    const addBtn = sandbox.elements['add-prospect-row'];
    const clickEvent = new sandbox.window.Event('click', { bubbles: true });
    clickEvent.target = addBtn;
    addBtn.dispatchEvent(clickEvent);

    // Find the newly added amount input
    const tbody = sandbox.elements['prospecting-table'].querySelector('tbody');
    assert.ok(tbody, 'Prospecting table body should exist');
    assert.ok(tbody.children.length > 0, 'Should have at least one row after adding');

    const newRow = tbody.children[tbody.children.length - 1];
    const newAmountInput = newRow ? newRow.querySelector('.prospect-amount') : null;
    assert.ok(newAmountInput, 'Dynamically added row should have an amount input');

    newAmountInput.value = '15000';
    newAmountInput.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const newPipelineTotal = sandbox.elements['total-new-pipeline'];
    const value = parseFloat(newPipelineTotal.getAttribute('data-value') || newPipelineTotal.textContent);
    assert.strictEqual(value, 15000, `New Pipeline total should update for dynamically added row, got ${value}`);
});


// ============================================================
// Section 13: Reads Stage Probabilities from Window
// ============================================================
console.log('\nSection 13: Stage Probabilities from Window');

test('should use window stage probabilities for calculation', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-q',
            amount: 100000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '7-Closing (90%)',
            category: 'progression'
        }],
        stageProbabilities: {
            '3-Confirmation (10%)': 10,
            '7-Closing (90%)': 90
        }
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.projected-stage-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    const row = sandbox.container.querySelector('[data-opportunity-id="opp-q"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 80000, `Should use window stage probs: expected 80000, got ${value}`);
});

test('should handle custom stage probability values correctly', () => {
    const customProbs = {
        '3-Confirmation (10%)': 15,
        '5-Specifications (30%)': 35,
        '6-Solution (60%)': 65,
        '7-Closing (90%)': 95
    };
    const sandbox = createBrowserSandbox({
        pipelineRows: [{
            oppId: 'opp-r',
            amount: 200000,
            currentStage: '3-Confirmation (10%)',
            projectedStage: '6-Solution (60%)',
            category: 'progression'
        }],
        stageProbabilities: customProbs
    });
    loadPlanningJsInSandbox(sandbox);

    const select = sandbox.document.querySelector('.projected-stage-select');
    if (select) select.dispatchEvent(new sandbox.window.Event('change', { bubbles: true }));

    // 200000 * (65 - 15) / 100 = 100000
    const row = sandbox.container.querySelector('[data-opportunity-id="opp-r"]');
    const progressionCell = row ? row.querySelector('.pipeline-progression') : null;
    assert.ok(progressionCell, 'Pipeline progression cell should exist');
    const value = parseFloat(progressionCell.getAttribute('data-value') || progressionCell.textContent);
    assert.strictEqual(value, 100000, `Should use custom probs: expected 100000, got ${value}`);
});


// ============================================================
// Section 14: Combined Totals (Integration)
// ============================================================
console.log('\nSection 14: Combined Totals (Integration)');

test('should correctly compute all four totals simultaneously', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [
            { oppId: 'opp-s', amount: 60000, currentStage: '7-Closing (90%)', projectedStage: '', category: 'closing' },
            { oppId: 'opp-t', amount: 45000, currentStage: '6-Solution (60%)', projectedStage: '', category: 'at_risk' },
            {
                oppId: 'opp-u',
                amount: 80000,
                currentStage: '3-Confirmation (10%)',
                projectedStage: '7-Closing (90%)',
                category: 'progression'
            }
        ],
        prospectingRows: [
            { source: 'Cold Call', amount: 30000, description: 'Prospect 1' }
        ]
    });
    loadPlanningJsInSandbox(sandbox);

    // Trigger recalculation
    const selects = sandbox.document.querySelectorAll('.projected-stage-select');
    selects.forEach(s => s.dispatchEvent(new sandbox.window.Event('change', { bubbles: true })));

    const closingTotal = parseFloat(sandbox.elements['total-closing'].getAttribute('data-value') || sandbox.elements['total-closing'].textContent);
    const atRiskTotal = parseFloat(sandbox.elements['total-at-risk'].getAttribute('data-value') || sandbox.elements['total-at-risk'].textContent);
    const progressionTotal = parseFloat(sandbox.elements['total-progression'].getAttribute('data-value') || sandbox.elements['total-progression'].textContent);
    const newPipelineTotal = parseFloat(sandbox.elements['total-new-pipeline'].getAttribute('data-value') || sandbox.elements['total-new-pipeline'].textContent);

    assert.strictEqual(closingTotal, 60000, `Closing total should be 60000, got ${closingTotal}`);
    assert.strictEqual(atRiskTotal, 45000, `At Risk total should be 45000, got ${atRiskTotal}`);
    // 80000 * (90-10)/100 = 64000
    assert.strictEqual(progressionTotal, 64000, `Progression total should be 64000, got ${progressionTotal}`);
    assert.ok(newPipelineTotal >= 30000, `New Pipeline total should be at least 30000 (prospecting), got ${newPipelineTotal}`);
});


// ============================================================
// Section 15: Edge Cases - No Rows
// ============================================================
console.log('\nSection 15: Edge Cases - No Rows');

test('should handle empty pipeline table (no opportunity rows)', () => {
    const sandbox = createBrowserSandbox({
        pipelineRows: [],
        prospectingRows: []
    });
    loadPlanningJsInSandbox(sandbox);

    // All totals should be 0 on initialization
    const closingTotal = parseFloat(sandbox.elements['total-closing'].getAttribute('data-value') || sandbox.elements['total-closing'].textContent);
    const atRiskTotal = parseFloat(sandbox.elements['total-at-risk'].getAttribute('data-value') || sandbox.elements['total-at-risk'].textContent);
    const progressionTotal = parseFloat(sandbox.elements['total-progression'].getAttribute('data-value') || sandbox.elements['total-progression'].textContent);
    const newPipelineTotal = parseFloat(sandbox.elements['total-new-pipeline'].getAttribute('data-value') || sandbox.elements['total-new-pipeline'].textContent);

    assert.strictEqual(closingTotal, 0, `Closing should be 0 with no rows, got ${closingTotal}`);
    assert.strictEqual(atRiskTotal, 0, `At Risk should be 0 with no rows, got ${atRiskTotal}`);
    assert.strictEqual(progressionTotal, 0, `Progression should be 0 with no rows, got ${progressionTotal}`);
    assert.strictEqual(newPipelineTotal, 0, `New Pipeline should be 0 with no rows, got ${newPipelineTotal}`);
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
