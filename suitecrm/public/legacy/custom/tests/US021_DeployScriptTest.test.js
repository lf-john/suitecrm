/**
 * US-021: Create deployment script
 *
 * Tests that deploy.ps1 exists in the project root and performs the required
 * deployment steps to the SuiteCRM Docker container:
 * 1. Copy custom/ directory
 * 2. Fix ownership (chown)
 * 3. Fix permissions (chmod)
 * 4. Run install.php
 * 5. Run Quick Repair and Rebuild
 * 6. Clear Symfony cache
 *
 * These tests MUST FAIL until the implementation is created.
 */

'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

// ============================================================
// Configuration
// ============================================================

// deploy.ps1 should be in the project root (two levels up from custom/tests/)
const projectRoot = path.resolve(__dirname, '..', '..');
const deployFile = path.join(projectRoot, 'deploy.ps1');

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

console.log('Running US-021 Deployment Script Tests...');
console.log(`Target File: ${deployFile}`);

// ============================================================
// Section 1: File Existence
// ============================================================
console.log('');
console.log('Section 1: File Existence');

test('deploy.ps1 should exist in project root', () => {
    assert.ok(
        fs.existsSync(deployFile),
        `deploy.ps1 must exist at: ${deployFile}`
    );
});

// We read the content once for all subsequent tests
let content = '';
try {
    if (fs.existsSync(deployFile)) {
        content = fs.readFileSync(deployFile, 'utf8');
    }
} catch (e) {
    // Ignore read errors here, subsequent tests will fail on empty content
}

// ============================================================
// Section 2: Container Reference
// ============================================================
console.log('');
console.log('Section 2: Container Reference');

test('should reference correct Docker container name', () => {
    assert.ok(
        content.includes('suitecrm892_app'),
        "Script must reference Docker container 'suitecrm892_app'"
    );
});

// ============================================================
// Section 3: File Copying
// ============================================================
console.log('');
console.log('Section 3: File Copying');

test('should copy custom/ directory to container', () => {
    // Expected: docker cp custom/. suitecrm892_app:/var/www/html/public/legacy/custom/
    const expected = /docker\s+cp\s+custom\/\.\s+suitecrm892_app:\/var\/www\/html\/public\/legacy\/custom\//i;
    assert.match(
        content,
        expected,
        "Script must run 'docker cp' to copy custom/ directory to /var/www/html/public/legacy/custom/"
    );
});

// ============================================================
// Section 4: File Ownership and Permissions
// ============================================================
console.log('');
console.log('Section 4: File Ownership and Permissions');

test('should fix file ownership (chown www-data)', () => {
    // Expected: docker exec suitecrm892_app chown -R www-data:www-data /var/www/html/public/legacy/custom/
    const expected = /docker\s+exec.*chown\s+-R\s+www-data:www-data\s+\/var\/www\/html\/public\/legacy\/custom\//i;
    assert.match(
        content,
        expected,
        "Script must run 'chown -R www-data:www-data' on custom directory inside container"
    );
});

test('should fix file permissions (chmod 775)', () => {
    // Expected: docker exec suitecrm892_app chmod -R 775 /var/www/html/public/legacy/custom/
    const expected = /docker\s+exec.*chmod\s+-R\s+775\s+\/var\/www\/html\/public\/legacy\/custom\//i;
    assert.match(
        content,
        expected,
        "Script must run 'chmod -R 775' on custom directory inside container"
    );
});

// ============================================================
// Section 5: Install Script Execution
// ============================================================
console.log('');
console.log('Section 5: Install Script Execution');

test('should execute install.php', () => {
    // Expected: docker exec suitecrm892_app php /var/www/html/public/legacy/custom/modules/LF_PRConfig/install.php
    const expected = /docker\s+exec.*php\s+\/var\/www\/html\/public\/legacy\/custom\/modules\/LF_PRConfig\/install\.php/i;
    assert.match(
        content,
        expected,
        "Script must execute 'install.php' inside container"
    );
});

// ============================================================
// Section 6: Quick Repair and Rebuild
// ============================================================
console.log('');
console.log('Section 6: Quick Repair and Rebuild');

test('should run Quick Repair and Rebuild', () => {
    // Verify essential parts of the repair command are present
    // require_once 'modules/Administration/QuickRepairAndRebuild.php'
    // $repair = new RepairAndClear();
    // $repair->repairAndClearAll(...)
    
    assert.ok(
        content.includes('QuickRepairAndRebuild.php'),
        "Script must require 'QuickRepairAndRebuild.php'"
    );
    assert.ok(
        content.includes('new RepairAndClear'),
        "Script must instantiate 'RepairAndClear' class"
    );
    assert.ok(
        content.includes('repairAndClearAll'),
        "Script must call 'repairAndClearAll' method"
    );
});

// ============================================================
// Section 7: Cache Clearing
// ============================================================
console.log('');
console.log('Section 7: Cache Clearing');

test('should clear Symfony cache', () => {
    // Expected: docker exec suitecrm892_app php /var/www/html/bin/console cache:clear
    // Note: path might be bin/console or /var/www/html/bin/console
    const expected = /docker\s+exec.*php\s+.*bin\/console\s+cache:clear/i;
    assert.match(
        content,
        expected,
        "Script must run 'bin/console cache:clear'"
    );
});

// ============================================================
// Section 8: Status Output
// ============================================================
console.log('');
console.log('Section 8: Status Output');

test('should output status messages', () => {
    // Check for Write-Host or echo
    const hasOutput = /Write-Host/i.test(content) || /echo/i.test(content);
    assert.ok(hasOutput, "Script should output status messages (using Write-Host or echo)");
});

// ============================================================
// Section 9: Error Handling
// ============================================================
console.log('');
console.log('Section 9: Error Handling');

test('should include error handling logic', () => {
    // Check for try/catch or $LASTEXITCODE checks
    const hasTryCatch = /try\s*\{/i.test(content) && /catch/i.test(content);
    const hasExitCode = /\$LASTEXITCODE/i.test(content);
    
    assert.ok(
        hasTryCatch || hasExitCode,
        "Script must include error handling (try/catch or checking $LASTEXITCODE)"
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

// Exit with code 1 if any test failed
process.exit(failCount > 0 ? 1 : 0);