<?php
/**
 * US-020: Dashboard CSS Tests - TDD RED
 *
 * Tests that custom/themes/lf_dashboard.css exists and contains ALL required styles
 * for the planning and reporting dashboards as specified in the acceptance criteria.
 *
 * Acceptance Criteria Verified:
 *   1. File exists at custom/themes/lf_dashboard.css
 *   2. Uses CSS custom properties (--lf-blue: #125EAD, --lf-green: #4BB74E) in :root block
 *   3. 3-column CSS grid layout for dashboard container
 *   4. Card styling with border, padding, and shadow consistent with SuiteCRM
 *   5. Stacked bar chart CSS with width percentages on colored divs
 *   6. Achievement badge colors: green #2F7D32, yellow #E6C300, orange #ff8c00, red #d13438
 *   7. Team/Rep toggle button styling with active state
 *   8. Week selector button and dropdown styling
 *   9. Gap to Target callout with red accent styling (#d13438)
 *  10. Responsive breakpoints: single column < 768px, 2 columns 768-1200px, 3 columns > 1200px
 *  11. Does NOT override SuiteCRM base styles (header, footer, nav, fonts)
 *  12. Uses Logical Front brand colors: blue #125EAD, green #4BB74E
 *
 * These tests MUST FAIL until the implementation is complete.
 */

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// ============================================================
// Test Harness
// ============================================================

$passCount = 0;
$failCount = 0;
$failures = [];

function test_assert(bool $condition, string $message): void
{
    global $passCount, $failCount, $failures;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$message}\n";
    } else {
        $failCount++;
        $failures[] = $message;
        echo "  [FAIL] {$message}\n";
    }
}

// ============================================================
// Configuration
// ============================================================

$customDir = dirname(__DIR__);
$cssFile = $customDir . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'lf_dashboard.css';

echo "Testing CSS file: {$cssFile}\n";

// ============================================================
// Section 1: File Existence and Structure
// ============================================================
echo "\nSection 1: File Existence and Structure\n";

test_assert(
    file_exists($cssFile),
    "CSS file must exist at custom/themes/lf_dashboard.css"
);

test_assert(
    file_exists($cssFile) && is_file($cssFile),
    "CSS file must be a regular file (not a directory)"
);

test_assert(
    file_exists($cssFile) && is_readable($cssFile),
    "CSS file must be readable"
);

// Early exit if file doesn't exist
if (!file_exists($cssFile)) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "SUMMARY (early exit - file not found)\n";
    echo str_repeat('=', 60) . "\n";
    echo "Total: " . ($passCount + $failCount) . "\n";
    echo "Passed: " . $passCount . "\n";
    echo "Failed: " . $failCount . "\n";
    if (count($failures) > 0) {
        echo "\nFailed tests:\n";
        foreach ($failures as $f) {
            echo "  - {$f}\n";
        }
    }
    echo str_repeat('=', 60) . "\n";
    exit($failCount > 0 ? 1 : 0);
}

$cssContent = file_get_contents($cssFile);

test_assert(
    strlen($cssContent) > 0,
    "CSS file must not be empty"
);

test_assert(
    str_contains($cssContent, '/*') || str_contains($cssContent, '//') || str_contains($cssContent, ':root') || str_contains($cssContent, '.'),
    "CSS file must contain valid CSS content (comments, selectors, or rules)"
);

// ============================================================
// Section 2: CSS Custom Properties (Variables) in :root Block
// ============================================================
echo "\nSection 2: CSS Custom Properties (:root Block)\n";

test_assert(
    str_contains($cssContent, ':root'),
    "CSS must contain a :root block for CSS custom properties"
);

test_assert(
    str_contains($cssContent, '--lf-blue') || str_contains($cssContent, '--lf-blue:'),
    ":root block must define --lf-blue variable"
);

test_assert(
    str_contains($cssContent, '#125EAD') || str_contains($cssContent, '#125ead'),
    ":root block must set --lf-blue to #125EAD (Logical Front brand blue)"
);

test_assert(
    str_contains($cssContent, '--lf-green') || str_contains($cssContent, '--lf-green:'),
    ":root block must define --lf-green variable"
);

test_assert(
    str_contains($cssContent, '#4BB74E') || str_contains($cssContent, '#4bb74e'),
    ":root block must set --lf-green to #4BB74E (Logical Front brand green)"
);

// Check that :root block contains both variables
$rootBlockMatch = preg_match('/:root\s*{[^}]*--lf-blue[^}]*}/s', $cssContent);
test_assert(
    $rootBlockMatch === 1,
    ":root block must contain --lf-blue variable definition"
);

$rootBlockMatch = preg_match('/:root\s*{[^}]*--lf-green[^}]*}/s', $cssContent);
test_assert(
    $rootBlockMatch === 1,
    ":root block must contain --lf-green variable definition"
);

// Achievement color variables (may be separate or combined)
test_assert(
    str_contains($cssContent, '--lf-achievement') || str_contains($cssContent, '#2F7D32') || str_contains($cssContent, '#2f7d32'),
    "Should define achievement green color (#2F7D32) as variable or in rule"
);

test_assert(
    str_contains($cssContent, '#E6C300') || str_contains($cssContent, '#e6c300'),
    "Should define achievement yellow color (#E6C300)"
);

test_assert(
    str_contains($cssContent, '#ff8c00') || str_contains($cssContent, '#FF8C00'),
    "Should define achievement orange color (#ff8c00)"
);

test_assert(
    str_contains($cssContent, '#d13438') || str_contains($cssContent, '#D13438'),
    "Should define achievement red color (#d13438)"
);

// ============================================================
// Section 3: Dashboard Container - 3-Column CSS Grid Layout
// ============================================================
echo "\nSection 3: Dashboard Container - CSS Grid Layout\n";

test_assert(
    str_contains($cssContent, 'grid-template-columns') || str_contains($cssContent, 'grid-template-columns:'),
    "Dashboard container must use grid-template-columns for 3-column layout"
);

test_assert(
    str_contains($cssContent, 'display: grid') || str_contains($cssContent, 'display:grid'),
    "Dashboard container must use CSS Grid (display: grid)"
);

test_assert(
    str_contains($cssContent, '1fr') || str_contains($cssContent, 'repeat(3'),
    "Grid layout must specify 3 columns (using 1fr or repeat(3))"
);

// Check for dashboard container class or ID
$dashboardContainerPattern = '/(\.(lf-dashboard|dashboard-container|lf_dashboard)|#(lf-dashboard|dashboard-container|lf_dashboard))\s*{/';
test_assert(
    preg_match($dashboardContainerPattern, $cssContent) === 1,
    "CSS must have a dashboard container selector (class or ID)"
);

// ============================================================
// Section 4: Card Styling (border, padding, box-shadow, border-radius)
// ============================================================
echo "\nSection 4: Card Styling\n";

test_assert(
    str_contains($cssContent, 'border') && (str_contains($cssContent, '1px') || str_contains($cssContent, 'border:')),
    "Cards must have border styling"
);

test_assert(
    str_contains($cssContent, 'padding'),
    "Cards must have padding"
);

test_assert(
    str_contains($cssContent, 'box-shadow'),
    "Cards must have box-shadow for depth"
);

test_assert(
    str_contains($cssContent, 'border-radius'),
    "Cards must have border-radius for rounded corners"
);

// Look for card-related selector
$cardPattern = '/(\.card|\.lf-card|\.dashboard-card)/i';
test_assert(
    preg_match($cardPattern, $cssContent) === 1,
    "CSS should have a card selector (class with 'card' in name)"
);

// ============================================================
// Section 5: Stacked Bar Chart (horizontal bars with width percentages)
// ============================================================
echo "\nSection 5: Stacked Bar Chart\n";

test_assert(
    str_contains($cssContent, 'width:') || str_contains($cssContent, 'width :'),
    "Chart bars must use width property for percentage-based sizing"
);

// Look for bar-related selectors
$barPattern = '/(\.bar|\.stacked|\.lf-bar|chart)/i';
test_assert(
    preg_match($barPattern, $cssContent) === 1,
    "CSS should have bar/chart-related selectors (class with 'bar' or 'stacked' in name)"
);

test_assert(
    str_contains($cssContent, 'display: flex') || str_contains($cssContent, 'display:flex'),
    "Stacked bars should use flexbox for horizontal layout"
);

test_assert(
    str_contains($cssContent, 'height') && (str_contains($cssContent, '30px') || str_contains($cssContent, '40px')),
    "Bars should have a defined height (e.g., 30px or 40px)"
);

// ============================================================
// Section 6: Achievement Badge Colors
// ============================================================
echo "\nSection 6: Achievement Badge Colors\n";

test_assert(
    str_contains($cssContent, '.achievement-green') || str_contains($cssContent, 'achievement green'),
    "CSS must define .achievement-green class selector"
);

test_assert(
    str_contains($cssContent, '.achievement-green') && str_contains($cssContent, '#2F7D32'),
    ".achievement-green must have color #2F7D32"
);

test_assert(
    str_contains($cssContent, '.achievement-yellow') || str_contains($cssContent, 'achievement yellow'),
    "CSS must define .achievement-yellow class selector"
);

test_assert(
    str_contains($cssContent, '.achievement-yellow') && str_contains($cssContent, '#E6C300'),
    ".achievement-yellow must have color #E6C300"
);

test_assert(
    str_contains($cssContent, '.achievement-orange') || str_contains($cssContent, 'achievement orange'),
    "CSS must define .achievement-orange class selector"
);

test_assert(
    str_contains($cssContent, '.achievement-orange') && str_contains($cssContent, '#ff8c00'),
    ".achievement-orange must have color #ff8c00"
);

test_assert(
    str_contains($cssContent, '.achievement-red') || str_contains($cssContent, 'achievement red'),
    "CSS must define .achievement-red class selector"
);

test_assert(
    str_contains($cssContent, '.achievement-red') && str_contains($cssContent, '#d13438'),
    ".achievement-red must have color #d13438"
);

// Verify all four achievement colors exist
$achievementColors = [
    'green' => '#2F7D32',
    'yellow' => '#E6C300',
    'orange' => '#ff8c00',
    'red' => '#d13438'
];

foreach ($achievementColors as $color => $hex) {
    $pattern = '/\.achievement-' . $color . '\s*{[^}]*color:\s*' . preg_quote($hex, '/') . '/i';
    test_assert(
        preg_match($pattern, $cssContent) === 1,
        ".achievement-{$color} should have color: {$hex}"
    );
}

// ============================================================
// Section 7: Team/Rep Toggle Button Styling
// ============================================================
echo "\nSection 7: Team/Rep Toggle Button Styling\n";

test_assert(
    str_contains($cssContent, 'active') || str_contains($cssContent, '.active') || str_contains($cssContent, ':active'),
    "Toggle buttons must have an active state styling"
);

test_assert(
    str_contains($cssContent, '#125EAD') || str_contains($cssContent, 'var(--lf-blue)'),
    "Toggle buttons should use brand blue #125EAD (direct or via variable)"
);

test_assert(
    str_contains($cssContent, 'cursor: pointer') || str_contains($cssContent, 'cursor:pointer'),
    "Buttons must have cursor: pointer for interactivity"
);

// Look for button-related selectors
$buttonPattern = '/(\.btn|\.lf-btn|\.button|button\s*\{)/i';
test_assert(
    preg_match($buttonPattern, $cssContent) === 1,
    "CSS should have button-related selectors"
);

// ============================================================
// Section 8: Week Selector (button and dropdown styling)
// ============================================================
echo "\nSection 8: Week Selector Styling\n";

test_assert(
    str_contains($cssContent, 'select') || str_contains($cssContent, '.select') || str_contains($cssContent, '.dropdown'),
    "CSS must style dropdown/select elements"
);

test_assert(
    str_contains($cssContent, 'padding') && str_contains($cssContent, 'border'),
    "Week selector dropdown must have padding and border"
);

test_assert(
    str_contains($cssContent, 'week') || str_contains($cssContent, 'selector'),
    "CSS should have week-related selectors"
);

// ============================================================
// Section 9: Deal Risk Rows (alternating backgrounds, stale deal emphasis)
// ============================================================
echo "\nSection 9: Deal Risk Rows\n";

test_assert(
    str_contains($cssContent, 'nth-child') || str_contains($cssContent, ':hover') || str_contains($cssContent, 'background'),
    "Deal risk rows should have alternating backgrounds or hover effects"
);

test_assert(
    str_contains($cssContent, 'risk') || str_contains($cssContent, 'stale') || str_contains($cssContent, 'deal'),
    "CSS should have risk/stale/deal-related selectors"
);

// ============================================================
// Section 10: Priority Cards (category headers with colored left borders)
// ============================================================
echo "\nSection 10: Priority Cards\n";

test_assert(
    str_contains($cssContent, 'border-left') || str_contains($cssContent, 'border-left-color'),
    "Priority cards should have colored left borders"
);

test_assert(
    str_contains($cssContent, 'priority') || str_contains($cssContent, 'category'),
    "CSS should have priority/category-related selectors"
);

// ============================================================
// Section 11: Totals Rows (bold, border-top, background highlight)
// ============================================================
echo "\nSection 11: Totals Rows\n";

test_assert(
    str_contains($cssContent, 'font-weight: bold') || str_contains($cssContent, 'font-weight:bold') || str_contains($cssContent, 'font-weight: 700') || str_contains($cssContent, 'font-weight:700'),
    "Totals rows must have bold font"
);

test_assert(
    str_contains($cssContent, 'border-top') || str_contains($cssContent, 'border-top:'),
    "Totals rows must have top border"
);

test_assert(
    str_contains($cssContent, 'total') || str_contains($cssContent, 'aggregate'),
    "CSS should have total/aggregate-related selectors"
);

// ============================================================
// Section 12: Gap to Target Callout (red accent #d13438)
// ============================================================
echo "\nSection 12: Gap to Target Callout\n";

test_assert(
    str_contains($cssContent, '#d13438') || str_contains($cssContent, 'D13438'),
    "Gap to target must use red accent color #d13438"
);

test_assert(
    str_contains($cssContent, 'gap') || str_contains($cssContent, 'target') || str_contains($cssContent, 'alert') || str_contains($cssContent, 'warning'),
    "CSS should have gap/target/alert-related selectors"
);

// Check for border-left or background with red color
$gapPattern = '/(border-left[^}]*#d13438|background[^}]*#d13438)/i';
test_assert(
    preg_match($gapPattern, $cssContent) === 1,
    "Gap to target should use red color (#d13438) on border-left or background"
);

// ============================================================
// Section 13: Responsive Breakpoints
// ============================================================
echo "\nSection 13: Responsive Breakpoints\n";

test_assert(
    str_contains($cssContent, '@media'),
    "CSS must contain @media queries for responsive design"
);

test_assert(
    str_contains($cssContent, '@media') && str_contains($cssContent, 'max-width: 768px'),
    "Must have breakpoint @media (max-width: 768px) for single column"
);

test_assert(
    str_contains($cssContent, '768px') && (str_contains($cssContent, 'min-width') || str_contains($cssContent, 'max-width')),
    "Must have breakpoint at 768px"
);

test_assert(
    str_contains($cssContent, '@media') && str_contains($cssContent, '1200px'),
    "Must have breakpoint at 1200px"
);

test_assert(
    str_contains($cssContent, '1200px') && str_contains($cssContent, 'min-width'),
    "Must have @media (min-width: 1201px) or similar for 3 columns"
);

// Check for 3-column grid in different breakpoints
$breakpointCount = preg_match_all('/@media/', $cssContent);
test_assert(
    $breakpointCount >= 2,
    "Should have at least 2 responsive breakpoints, found: {$breakpointCount}"
);

// ============================================================
// Section 14: Negative Tests - Should NOT Override SuiteCRM Base Styles
// ============================================================
echo "\nSection 14: Negative Tests - No SuiteCRM Base Style Overrides\n";

// Check for global style overrides (bad practice)
test_assert(
    !preg_match('/^(body|html|\*)\s*{/m', $cssContent),
    "CSS must NOT override global body/html/* styles (SuiteCRM base styles)"
);

test_assert(
    !str_contains($cssContent, 'header {') && !str_contains($cssContent, '.header{') && !str_contains($cssContent, 'header{'),
    "CSS must NOT override SuiteCRM header styles"
);

test_assert(
    !str_contains($cssContent, 'footer {') && !str_contains($cssContent, '.footer{') && !str_contains($cssContent, 'footer{'),
    "CSS must NOT override SuiteCRM footer styles"
);

test_assert(
    !str_contains($cssContent, 'nav {') && !str_contains($cssContent, '.nav{') && !str_contains($cssContent, 'nav{'),
    "CSS must NOT override SuiteCRM navigation styles"
);

test_assert(
    !preg_match('/font-family:\s*["\']?[^"\']+$/', $cssContent),
    "CSS must NOT override global font-family (SuiteCRM base fonts)"
);

// Verify styles are scoped to dashboard elements
test_assert(
    str_contains($cssContent, '.lf-') || str_contains($cssContent, '#lf-'),
    "CSS selectors should be scoped with 'lf-' prefix to avoid conflicts"
);

// ============================================================
// Section 15: Logical Front Brand Colors Usage
// ============================================================
echo "\nSection 15: Logical Front Brand Colors\n";

test_assert(
    str_contains($cssContent, '#125EAD') || str_contains($cssContent, 'var(--lf-blue)'),
    "CSS must use Logical Front brand blue #125EAD (direct or via variable)"
);

test_assert(
    str_contains($cssContent, '#4BB74E') || str_contains($cssContent, 'var(--lf-green)'),
    "CSS must use Logical Front brand green #4BB74E (direct or via variable)"
);

// Count usage of brand colors (should be used multiple times)
$blueUsage = substr_count(strtolower($cssContent), '#125ead') + substr_count($cssContent, 'var(--lf-blue)');
test_assert(
    $blueUsage >= 1,
    "Brand blue should be used at least once in the CSS"
);

$greenUsage = substr_count(strtolower($cssContent), '#4bb74e') + substr_count($cssContent, 'var(--lf-green)');
test_assert(
    $greenUsage >= 1,
    "Brand green should be used at least once in the CSS"
);

// ============================================================
// Section 16: Additional Style Requirements
// ============================================================
echo "\nSection 16: Additional Style Requirements\n";

// Check for flexbox usage (modern layout)
test_assert(
    str_contains($cssContent, 'display: flex') || str_contains($cssContent, 'display:flex'),
    "CSS should use flexbox for layouts (display: flex)"
);

// Check for color usage (not just grayscale)
$colorUsage = preg_match_all('/color:\s*#[0-9a-f]{3,6}/i', $cssContent);
test_assert(
    $colorUsage >= 5,
    "CSS should use colors, not just grayscale, found {$colorUsage} color declarations"
);

// Check for proper CSS syntax (no obvious errors)
$syntaxErrors = 0;
// Check for unclosed braces
$openBraces = substr_count($cssContent, '{');
$closeBraces = substr_count($cssContent, '}');
test_assert(
    $openBraces === $closeBraces,
    "CSS must have balanced braces (open: {$openBraces}, close: {$closeBraces})"
);

// ============================================================
// Summary
// ============================================================
echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY: US-020 Dashboard CSS Tests\n";
echo str_repeat('=', 60) . "\n";
echo "Total: " . ($passCount + $failCount) . "\n";
echo "Passed: " . $passCount . "\n";
echo "Failed: " . $failCount . "\n";

if (count($failures) > 0) {
    echo "\nFailed tests:\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
}

echo str_repeat('=', 60) . "\n";

exit($failCount > 0 ? 1 : 0);
