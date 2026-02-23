# US-009: Pipeline Health Check Column - TDD Test Summary

## Test Files Created

1. **custom/tests/US009_PipelineHealthView.test.php** - Server-side PHP view logic tests (56 assertions across 10 sections)
2. **custom/tests/US009_PipelineHealthView.test.js** - Client-side JavaScript rendering tests (6 test cases)

## Test Execution Status

✅ All tests are **FAILING** as expected in TDD-RED phase (implementation not yet written)

### PHP Test Results
```
Exit Code: 255 (FAILED)
First Failure: Section 2.1 - default_annual_quota config not retrieved
Total Sections: 10
Assertions Passed Before Failure: 3/56
```

### JavaScript Test Results
```
Exit Code: 1 (FAILED)
Passed: 0/6
Failed: 6/6
All tests failing because dashboard.js doesn't implement Pipeline Health Check rendering
```

## Acceptance Criteria → Test Mapping

| AC # | Acceptance Criterion | Test File | Test Name/Section |
|------|---------------------|-----------|-------------------|
| 1 | Pipeline Health Check renders as first column | PHP | Section 4.1: pipeline-health-column container |
| 1 | Pipeline Health Check renders as first column | JS | Container existence in sandbox |
| 2 | Team View shows aggregated data | PHP | Section 2.2: Active reps retrieval |
| 2 | Team View shows aggregated data | JS | Test: "should render Team Quota calculation" |
| 3 | Closed YTD with year label | PHP | Section 2.3: closedYTD data gathering |
| 3 | Closed YTD with year label | PHP | Section 4.2: "Closed for" label check |
| 4 | Team Quota calculation | PHP | Section 2.1: default_annual_quota config |
| 4 | Team Quota calculation | PHP | Section 6.1: Team Quota logic |
| 4 | Team Quota calculation | JS | Test: "should render Team Quota calculation" |
| 5 | Target formula: (Quota - Closed YTD) × multiplier | PHP | Section 2.1: pipeline_coverage_multiplier config |
| 5 | Target formula: (Quota - Closed YTD) × multiplier | PHP | Section 6.2: Target calculation logic |
| 5 | Target formula: (Quota - Closed YTD) × multiplier | JS | Test: "should calculate and display Target" |
| 6 | Stacked bar chart using CSS divs | PHP | Section 4.3: Stacked bar container |
| 6 | Stacked bar chart using CSS divs | PHP | Section 9.2: NO charting library |
| 6 | Stacked bar chart using CSS divs | JS | Test: "should render Stacked Bar Chart using CSS widths" |
| 7 | Each stage with color, label, amount | PHP | Section 2.4: pipelineByStage data |
| 7 | Each stage with color, label, amount | JS | Test: Width percentages (20%, 30%, 50%) |
| 8 | Gap to Target SEPARATE callout with red styling | PHP | Section 4.2: "Gap to Target" label |
| 8 | Gap to Target SEPARATE callout with red styling | PHP | Section 4.4: Red styling class |
| 8 | Gap to Target SEPARATE callout with red styling | PHP | Section 9.1: NOT a bar segment |
| 8 | Gap to Target SEPARATE callout with red styling | JS | Test: "should display Gap to Target as separate callout" |
| 9 | Pipeline by Rep stacked bars | PHP | Section 2.5: pipelineByRep data |
| 9 | Pipeline by Rep stacked bars | PHP | Section 4.2: "Pipeline by Rep" label |
| 9 | Pipeline by Rep stacked bars | JS | Test: "should render Pipeline by Rep section" |
| 10 | Coverage Ratio calculation | PHP | Section 6.4: Coverage ratio logic |
| 10 | Coverage Ratio calculation | PHP | Section 4.2: "Coverage Ratio" label |
| 10 | Coverage Ratio calculation | JS | Test: "should display Coverage Ratio" |
| 11 | Rep View filtering | PHP | Section 7.2: Rep View mode support |
| 11 | Rep View filtering | PHP | Section 7.3: Rep selector element |
| 12 | Bars based on pipeline total NOT target | JS | Test: Width percentages calculated from pipeline |

## Edge Cases Covered

### PHP Tests
1. **Division by zero protection** (Section 10.1)
   - Coverage Ratio when remaining quota = 0
   - Percentage calculations when pipeline = 0

2. **Empty/missing data handling** (Section 10.2)
   - No active reps scenario
   - Empty pipeline data
   - Missing config values

3. **Security** (Section 8)
   - XSS protection via htmlspecialchars
   - Current user validation
   - Input sanitization

4. **Negative tests** (Section 9)
   - Gap to Target NOT as bar segment
   - NO charting libraries used

### JavaScript Tests
All tests use mock data with realistic scenarios:
- Multiple reps with different quotas
- Mixed pipeline stages with different amounts
- Closed YTD data
- Edge case: Rep with empty pipeline

## Test Categories

### Happy Path Tests (JavaScript)
1. Team Quota calculation with 2 reps
2. Target calculation: (220k - 25k) × 3.0 = 585k
3. Gap to Target: 585k - 100k = 485k
4. Coverage Ratio: 100k / 195k = 0.51
5. Stacked bar widths: 20%, 30%, 50%
6. Pipeline by Rep rendering

### Structural Tests (PHP)
1. File structure and guards (Section 1)
2. Data gathering methods (Section 2)
3. Data injection format (Section 3)
4. HTML structure (Section 4)
5. External resources (Section 5)

### Business Logic Tests (PHP)
1. Calculation logic (Section 6)
2. View mode support (Section 7)
3. Security (Section 8)

### Negative Tests (PHP)
1. What should NOT exist (Section 9)
2. Edge case validation (Section 10)

## Expected Implementation Files

Based on tests, the implementation will need to modify/create:

1. **custom/modules/LF_WeeklyPlan/views/view.dashboard.php**
   - Add default_annual_quota and pipeline_coverage_multiplier config retrieval
   - Calculate Team Quota, Target, Gap to Target, Coverage Ratio
   - Render Pipeline Health Check column HTML with all required labels

2. **custom/modules/LF_WeeklyPlan/js/dashboard.js**
   - Implement renderPipelineHealthColumn() function
   - Calculate stacked bar widths as percentages
   - Render Gap to Target as separate callout with red styling
   - Render Pipeline by Rep section

3. **custom/themes/lf_dashboard.css** (if not exists)
   - Stacked bar container styles
   - Stage color definitions
   - Gap to Target red styling

## Next Phase: TDD-GREEN

After approval, the TDD-GREEN phase will:
1. Implement server-side data gathering for missing config values
2. Add calculation logic for Team Quota, Target, Gap, Coverage Ratio
3. Implement JavaScript rendering functions
4. Add CSS styles
5. Verify all tests pass
6. Run full test suite to ensure no regressions
