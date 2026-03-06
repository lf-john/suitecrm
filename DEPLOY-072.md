# Deployment Notes — CRM Issues 062–074

**Date:** 2026-03-06
**Previous production commit:** `7137a9fc` — Security, reliability, and data integrity fixes (P1-P6) (2026-03-01)
**This commit:** UI fixes for filter panels, report detail page, and cross-browser compatibility

---

## Changes Summary

### 1. Filter Panel — Composite Field Layout (dist CSS)
**File:** `public/dist/logical-front-theme.css`

Composite fields (Opportunity Amount, Expected Close Date, Valid Until) in list view filters now show both the operator dropdown and value input. Previously, the value input was pushed off-screen because both items used `w-100` (100% width) in a `flex-row` container — the second item overflowed invisibly to the right.

**Fix:** Force `flex-direction: column` on composite and group fields within filter context, so operator stacks above value input.

**Risk:** Low. Only affects `scrm-composite-field` and `scrm-group-field` inside `scrm-list-filter` / `.filter-panel`. No impact on record edit/detail views.

### 2. Filter Panel — Checkbox Layout (dist CSS)
**File:** `public/dist/logical-front-theme.css`

Special filter fields (My Items, Open Items, My Favorites) now render label and checkbox side-by-side in a compact row instead of stacked vertically. The checkbox container (`.checkbox-container`) had zero height because its `.checkmark` child is absolutely positioned — fixed with explicit `min-height` and `min-width`.

**Risk:** Low. Scoped to `.special-field-grid` within filter panels only.

### 3. Report Detail — ACTIONS Button Firefox Visibility (Dawn/style.css)
**File:** `public/legacy/cache/themes/suite8/css/Dawn/style.css`

The ACTIONS button on report detail pages was invisible in Firefox. The Dawn theme's compiled CSS had `body:has(#report_module) .detail-view .nav-tabs{display:none !important}` which hid the entire `<ul class="nav-tabs">` — including the `<li id="tab-actions">` that contains the ACTIONS button. Changed to target `li:not(#tab-actions)` so only non-action tabs are hidden.

**Risk:** Low. Only affects report detail view. The ACTIONS dropdown is now visible in both Chrome and Firefox.

### 4. Report Detail — White Line Removal (Dawn/style.css)
**File:** `public/legacy/cache/themes/suite8/css/Dawn/style.css`

A visible white gap (~30px) existed between the report title card and the details card. Caused by:
- `.moduleTitle` having `margin-bottom: 20px` (from Dawn theme)
- An empty `<table>` with `<td class="buttons">` (10px padding) between the two cards

**Fix:** Set `margin-bottom: 20px` (controlled gap) on `.moduleTitle` for report DetailView, collapse the empty buttons table to zero height (using `max-height:0; overflow:hidden; visibility:hidden` — `display:none` was blocked by a competing `table{display:table !important}` rule).

**Risk:** Low. Scoped to `#pagecontent.view-module-AOR_Reports.view-action-DetailView` only.

### 5. Report Condition Delete — tbody Exclusion (legacy CSS)
**File:** `public/legacy/themes/suite8/css/logical-front-theme.css`, `public/legacy/custom/themes/SuiteP/css/logical-front-theme.css`

The global rule `tbody tr { display: table-row !important }` was preventing `markConditionLineDeleted()` from hiding deleted condition rows. Added `:not()` exclusions for report-specific table bodies (`#aor_conditions_body`, `#aor_fieldLines_body`, `.connectedSortableConditions`, `.connectedSortableFields`).

**Risk:** Low. The `:not()` exclusion only exempts report editor tables from the forced `display: table-row` rule.

### 6. Report Detail — Action Button Positioning (dist CSS)
**File:** `public/dist/logical-front-theme.css`

Added several fix blocks from sessions 054–061 that were deployed to the container but hadn't been committed:
- Report ACTIONS button restoration (overrides hidden-table rules)
- Filter dropdown white backgrounds (PrimeNG multiselect)
- Subpanel left-alignment for Firefox
- Report conditions panel visibility
- Condition line delete button visibility

### 7. Cache Bust Updates
**Files:** `public/dist/index.html`, `public/legacy/custom/themes/suite8/tpls/_head.tpl`

Updated `?v=` timestamp parameters on CSS file references to force browser cache refresh.

---

## Concerns and Caveats

### Dawn/style.css Is a Cache File
The most critical concern: `public/legacy/cache/themes/suite8/css/Dawn/style.css` is a **compiled theme cache file** that SuiteCRM regenerates under certain conditions:
- Quick Repair & Rebuild
- Theme changes in Admin > Display Settings
- SuiteCRM upgrades

**If regenerated, fixes #3 and #4 (ACTIONS button Firefox + white line) will be lost.**

This commit force-adds `Dawn/style.css` to git (it's normally gitignored) so the fixes are preserved in version control. After any cache regeneration, the file must be re-deployed:
```bash
docker cp /opt/suitecrm/suitecrm/public/legacy/cache/themes/suite8/css/Dawn/style.css \
  suitecrm_app:/var/www/html/public/legacy/cache/themes/suite8/css/Dawn/style.css
```

**Permanent solution (future work):** Move these fixes to a JS-based approach in `_head.tpl` that injects CSS after page load, making them immune to cache regeneration.

### index.html Cache Bust Method
The cache bust in `index.html` uses a `?v=TIMESTAMP` parameter on the CSS link. The previous `sed` command that applied the timestamp was too broad and corrupted `http-equiv` attributes — this has been fixed. Future cache busts should target only the CSS link line, not the entire file.

### No Functional Code Changes
This commit contains **only CSS and template changes**. No PHP, no JavaScript logic changes, no database schema changes, no configuration changes. All fixes are purely visual/layout.

### Cross-Browser Testing
All fixes verified with Playwright automation in both Chromium and Firefox (headless). The user has also manually verified in Chrome and Firefox.

---

## Deployment Steps

```bash
# 1. Pull the latest commit
cd /opt/suitecrm
git pull origin main

# 2. Copy changed files into the container
docker cp suitecrm/public/dist/logical-front-theme.css \
  suitecrm_app:/var/www/html/public/dist/logical-front-theme.css
docker cp suitecrm/public/dist/index.html \
  suitecrm_app:/var/www/html/public/dist/index.html
docker cp suitecrm/public/legacy/themes/suite8/css/logical-front-theme.css \
  suitecrm_app:/var/www/html/public/legacy/themes/suite8/css/logical-front-theme.css
docker cp suitecrm/public/legacy/custom/themes/SuiteP/css/logical-front-theme.css \
  suitecrm_app:/var/www/html/public/legacy/custom/themes/SuiteP/css/logical-front-theme.css
docker cp suitecrm/public/legacy/custom/themes/suite8/tpls/_head.tpl \
  suitecrm_app:/var/www/html/public/legacy/custom/themes/suite8/tpls/_head.tpl
docker cp suitecrm/public/legacy/cache/themes/suite8/css/Dawn/style.css \
  suitecrm_app:/var/www/html/public/legacy/cache/themes/suite8/css/Dawn/style.css

# 3. Fix permissions
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/public/

# 4. Users should hard refresh (Ctrl+Shift+R) after deployment
```
