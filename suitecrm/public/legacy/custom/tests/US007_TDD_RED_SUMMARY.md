# US-007: Create Planning Dashboard - Base View with Data Gathering
## TDD-RED Phase Complete

### Phase: DESIGN

#### Analysis of Acceptance Criteria

**Primary Requirements:**
1. **File Structure**: `custom/modules/LF_WeeklyPlan/views/view.dashboard.php`
2. **Class**: `LF_WeeklyPlanViewDashboard` extending `SugarView`
3. **Display Method** renders:
   - Title bar with "Weekly Planning Dashboard"
   - Team View / Rep View toggle buttons (Team active by default)
   - Rep dropdown (hidden in Team View, visible in Rep View)
   - Week selector (Back, Next, Current Week buttons + dropdown)
4. **Data Gathering** (server-side):
   - Config values (LF_PRConfig::getConfig/getAll)
   - Active reps with targets (LF_RepTargets::getActiveReps)
   - Current week info (WeekHelper::getCurrentWeekStart, getWeekList, formatWeekRange)
   - Pipeline by stage (OpportunityQuery::getPipelineByStage)
   - Pipeline by rep (OpportunityQuery::getPipelineByRep)
   - Stale deals (OpportunityQuery::getStaleDeals)
   - Plan items for selected week
   - Rep targets (LF_RepTargets::getTargetsForYear)
5. **Data Injection**: Single JSON object via `<script>window.LF_DASHBOARD_DATA = ...</script>`
6. **External Resources**: CSS link to `custom/themes/lf_dashboard.css`, JS script to `custom/modules/LF_WeeklyPlan/js/dashboard.js`
7. **Styling**: Use Logical Front brand colors (blue #125EAD, green #4BB74E)
8. **Inheritance**: SuiteCRM header, navigation, footer

#### Test Strategy

**Structural Tests** verify:
- File exists at correct path
- Class named correctly and extends SugarView
- Required methods exist (display, __construct)
- HTML output contains expected elements (title, buttons, dropdowns)
- Data injection script tag is present
- External CSS/JS includes are present
- Uses correct brand colors

**Edge Cases Tested:**
- Empty rep list
- Missing config values
- XSS prevention via JSON_HEX flags

---

### Phase: TDD-RED - Write Failing Tests

#### Test Files Created

**Primary Test File:**
- `custom/tests/view.dashboard.test.php` (35 tests)
- `custom/tests/view.dashboard.test-summary.php` (4 critical failure checks)

#### Test Coverage

All acceptance criteria mapped to tests:

| AC # | Requirement | Test # | Status |
|------|-------------|--------|--------|
| AC1 | File exists extending SugarView | 1, 3, 5 | ✅ PASS |
| AC2 | Class named LF_WeeklyPlanViewDashboard | 3 | ✅ PASS |
| AC3a | Gathers config values | 21 | ✅ PASS |
| AC3b | Gathers active reps | 22 | ✅ PASS |
| AC3c | Gathers week info | 23 | ✅ PASS |
| AC3d | Gathers pipeline by stage | 24 | ✅ PASS |
| AC3e | Gathers pipeline by rep | 25 | ✅ PASS |
| AC3f | Gathers stale deals | 26 | ✅ PASS |
| AC3g | Gathers plan items | 27 | ✅ PASS |
| AC3h | Gathers rep targets | 28 | ✅ PASS |
| AC4 | Injects data as JSON | 20, 32, 33 | ✅ PASS |
| AC5 | Team/Rep toggle buttons | 9, 10 | ✅ PASS |
| AC6 | Rep dropdown (ID: rep-selector) | 11, 12 | ❌ FAIL |
| AC7 | Week selector buttons | 13, 14, 15 | ❌ FAIL |
| AC8 | Week dropdown (ID: week-selector) | 16, 29, 30 | ❌ FAIL |
| AC9 | Inherits header/footer | 6 | ✅ PASS |
| AC10 | Includes external CSS | 17 | ✅ PASS |
| AC11 | Includes external JS | 18 | ✅ PASS |
| AC12 | Uses brand colors | 19 | ✅ PASS |

#### Test Execution Results

```
Running view.dashboard test summary...

===========================================
FAILING TESTS:
===========================================
✗ Test 11: FAIL - Rep dropdown must have id='rep-selector' (found id='rep-select' instead)
✗ Test 16: FAIL - Week dropdown must have id='week-selector' (found id='week-select' instead)
✗ Test: FAIL - Back button should use '&lt;' but uses '&laquo;' instead
✗ Test: FAIL - Next button should use '&gt;' but uses '&raquo;' instead

SUMMARY: 0 passing, 4 failing
```

#### Why Tests FAIL (Correct TDD-RED Behavior)

The implementation file `custom/modules/LF_WeeklyPlan/views/view.dashboard.php` exists but contains **incorrect element IDs** that violate the project's DOM Element ID Convention:

**Project Rule:**
> PHP-rendered HTML element IDs and JavaScript `getElementById` calls MUST match exactly.

**Acceptance Criteria Requires:**
- Rep dropdown: `id="rep-selector"`
- Week dropdown: `id="week-selector"`

**Current Implementation Has:**
- Rep dropdown: `id="rep-select"` ❌
- Week dropdown: `id="week-select"` ❌

**Button Symbol Issues:**
- Back button: Uses `&laquo;` instead of `&lt;` ❌
- Next button: Uses `&raquo;` instead of `&gt;` ❌

These failures are **expected and correct** for TDD-RED phase. The tests correctly identify that the implementation does not match the acceptance criteria.

---

### Files Involved

**Test Files:**
- `custom/tests/view.dashboard.test.php` - 35 comprehensive tests
- `custom/tests/view.dashboard.test-summary.php` - Critical failure summary

**Implementation File:**
- `custom/modules/LF_WeeklyPlan/views/view.dashboard.php` - EXISTS but needs fixes

**Dependencies:**
- `custom/include/LF_PlanningReporting/WeekHelper.php` ✅
- `custom/include/LF_PlanningReporting/OpportunityQuery.php` ✅
- `custom/modules/LF_PRConfig/LF_PRConfig.php` ✅
- `custom/modules/LF_RepTargets/LF_RepTargets.php` ✅
- `custom/modules/LF_WeeklyPlan/LF_WeeklyPlan.php` ✅
- `custom/themes/lf_dashboard.css` - may need creation
- `custom/modules/LF_WeeklyPlan/js/dashboard.js` - may need creation

---

### Next Phase: TDD-GREEN

**Implementation Tasks:**
1. Fix `id="rep-select"` → `id="rep-selector"` in view.dashboard.php
2. Fix `id="week-select"` → `id="week-selector"` in view.dashboard.php
3. Fix Back button: `&laquo;` → `&lt;`
4. Fix Next button: `&raquo;` → `&gt;`
5. Ensure all tests PASS
6. Run build command
7. Commit with `[CODE]` marker

---

### Completion Signal

✅ **TDD-RED PHASE COMPLETE**

- All tests written (35 tests)
- Tests confirmed FAILING (4 critical failures)
- Failures document implementation gaps
- Ready for TDD-GREEN phase

<promise>COMPLETE</promise>
