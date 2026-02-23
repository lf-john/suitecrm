# US-015 Design Plan: Prospecting Results and Conversion

## 1. Analysis
- **Goal**: Extend Reporting View to show prospecting results and allow conversion to Opportunities.
- **Components**:
    - `view.reporting.php`: Display prospect items, "Convert" button (if planned), "No Opportunity" UI.
    - `view.save_json.php`: Handle AJAX requests for conversion.
    - `LF_PlanProspectItem`: Logic for conversion (already partly in place/tested by US008, but need to verify integration).
- **Acceptance Criteria Mapping**:
    - "Prospecting Results section...": Test `view.reporting.php` for HTML output of prospect items.
    - "Convert button visible only...": Test `view.reporting.php` logic for 'planned' status.
    - "Convert form accepts...": Test `view.reporting.php` for form fields.
    - "AJAX endpoint exists...": Test `view.save_json.php` existence and structure.
    - "Convert creates Account/Opp...": Test `view.save_json.php` calls `convertToOpportunity`.

## 2. Test Files to Create
1.  `tests/US015_ReportingView_Prospecting.test.php`:
    - Validates `custom/modules/LF_WeeklyReport/views/view.reporting.php` (updates/additions).
    - Checks for `lf_plan_prospect_items` loading.
    - Checks for table columns (Source, Day, Expected Value, etc.).
    - Checks for Convert button conditionality.
    - Checks for CSRF token usage.

2.  `tests/US015_AjaxEndpoint.test.php`:
    - Validates `custom/modules/LF_WeeklyReport/views/view.save_json.php` (New File).
    - Checks class name `LF_WeeklyReportViewSave_json`.
    - Checks `display()` reads JSON input.
    - Checks for `convertToOpportunity` call.
    - Checks JSON response format.

## 3. Existing Tests Review
- `US008_PlanProspectItemBeanTest.test.php`: Covers `convertToOpportunity` signature. No changes needed unless signature changes (AC implies match).
- `US013_ReportingViewTest.test.php`: Covers basic `view.reporting.php` structure. US015 extends this. I will create a separate test file to keep it atomic and additive.

## 4. Edge Cases
- Empty prospect list.
- Malformed JSON in AJAX.
- Missing configuration for default stage.
