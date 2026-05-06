# Deployment Notes — Bug Fixes & Layout Corrections

**Date:** 2026-05-06
**Previous commit:** `42fe59d9` — fix: Quote Actions dropdown + PDF notes field (v13-v18)

---

## Changes Summary

### 1. Fix: EmailAddress class not found on Accounts detail page (FATAL)
**File:** `custom/modules/Accounts/metadata/detailviewdefs.php`

**Root cause:** The custom `detailviewdefs.php` introduced in commit `148cfb87` calls `EmailAddress::getSendConfirmOptInEmailActionLinkDefs('Accounts')` at file parse time. The stock SuiteCRM `detailviewdefs.php` has the same call and works because the legacy MVC framework autoloads the `EmailAddress` class before including metadata files. However, the SuiteCRM 8 Angular frontend fetches metadata via a different API code path (`/api/v8/meta/...`) that does not bootstrap the legacy autoloader in the same order. This causes a fatal `Class "EmailAddress" not found` error, making the Accounts detail page completely unusable in the Angular UI.

**Fix:** Wrapped the static method call in a `class_exists('EmailAddress')` guard with an empty array fallback. When loaded via the Angular metadata API (where `EmailAddress` is not yet autoloaded), the button definition is omitted gracefully. When loaded via the legacy MVC (where `EmailAddress` is available), the button renders normally.

```php
// Before (fatal in Angular API context):
'SEND_CONFIRM_OPT_IN_EMAIL' => EmailAddress::getSendConfirmOptInEmailActionLinkDefs('Accounts'),

// After (safe in both contexts):
'SEND_CONFIRM_OPT_IN_EMAIL' => class_exists('EmailAddress') ? EmailAddress::getSendConfirmOptInEmailActionLinkDefs('Accounts') : array(),
```

**Risk:** None. The `SEND_CONFIRM_OPT_IN_EMAIL` button is a GDPR opt-in feature used only in the legacy DetailView. In the Angular UI this button is not rendered regardless. The fallback produces identical behavior to not having a custom `detailviewdefs.php` at all.

---

### 2. Fix: Accounts detail — IT Spend field not visible (layout error)
**File:** `custom/modules/Accounts/metadata/detailviewdefs.php`

**Root cause:** The `it_spend_c` field was nested as a third element inside the `employees` row array in the `LBL_PANEL_ADVANCED` panel. The panel has `maxColumns => 2`, so SuiteCRM silently ignores any element beyond index 1 in a row. The field was defined in the metadata but never rendered.

**Fix:** Moved `it_spend_c` to its own row (index 2) in the Advanced panel with an empty second column. Renumbered subsequent rows (`parent_name` to index 3, `campaign_name` to index 4) to avoid duplicate array keys.

**Risk:** None. Only changes field positioning within the Advanced tab. No data or logic changes.

---

### 3. Fix: Opportunities detail/edit — Referred By field not visible (layout error)
**Files:**
- `custom/modules/Opportunities/metadata/detailviewdefs.php`
- `custom/modules/Opportunities/metadata/editviewdefs.php`

**Root cause:** The `referred_by_c` field was added as a third element in the `probability` / `lead_source` row. With `maxColumns => 2`, the third field was silently dropped by SuiteCRM's layout renderer.

**Fix (detail view):** Moved `referred_by_c` to its own row (index 4) paired with `campaign_name`. `next_step` moved to row 5 with an empty second column. `description` and `assigned_user_name` renumbered to rows 6 and 7.

**Fix (edit view):** Same restructuring — `referred_by_c` paired with `campaign_name` on its own row, `probability` paired with `next_step` on the following row.

**Risk:** None. Only changes field positioning. All fields that were previously visible remain visible; `referred_by_c` is now additionally visible.

---

### 4. Fix: Undefined variable $input in report save (PHP Warning)
**File:** `public/legacy/custom/modules/LF_WeeklyPlan/views/view.report_save_json.php`

**Root cause:** The debug log line on line 33 referenced `$input` and `$data` before they were assigned on lines 34-35. The log line was placed above the variable assignments.

**Fix:** Moved `$input = file_get_contents('php://input')` and `$data = json_decode(...)` above the debug log line so both variables are defined before use.

**Risk:** None. The CSRF validation above this block does not depend on `$input` or `$data`. The debug log now correctly captures the raw request body and parsed action. Functional save behavior is unchanged.

---

### 5. Fix: Undefined array key "value" in rep report week filter (PHP Warning)
**File:** `public/legacy/custom/modules/LF_WeeklyPlan/views/view.rep_report.php`

**Root cause:** The `array_filter` callback on the week list accessed `$w['value']`, but `WeekHelper::getWeekList()` returns arrays with keys `weekStart`, `weekEnd`, `label`, and `isCurrent` — there is no `value` key. In PHP, accessing an undefined array key returns `null`, and `null <= $currentWeekStart` evaluates to `true` (null coerces to 0, and 0 <= any date string is true in loose comparison). This meant the filter was a no-op — all weeks passed through, including future weeks that should have been excluded.

**Fix:** Changed `$w['value']` to `$w['weekStart']`, which is the correct key containing the `Y-m-d` date string. The filter now correctly excludes future weeks from the Rep Report week selector dropdown.

**Risk:** Very low. The only behavioral change is that the Rep Report week selector will no longer show future weeks. This is the intended behavior per the filter's purpose. Users who previously saw future weeks in the dropdown (which would show empty/meaningless data) will no longer see them.

---

### 6. Database: Disabled broken AOD Lucene schedulers
**Not a code change — applied directly to the database.**

**Root cause:** The `schedulers` table contained an active "Perform Lucene Index" job (`function::aodIndexUnindexed`) and an inactive "Optimise AOD Index" job (`function::aodOptimiseIndex`). Neither function exists anywhere in the codebase — the AOD_Index module directory does not exist in this SuiteCRM installation. Every cron cycle, the active scheduler triggered a fatal `TypeError: call_user_func_array()` error because the callback function could not be found.

**Fix:** Set the "Perform Lucene Index" scheduler status to `Inactive`:
```sql
UPDATE schedulers SET status = 'Inactive'
WHERE job = 'function::aodIndexUnindexed' AND deleted = 0;
```

**Risk:** None. The function does not exist, so the job has never successfully executed. Disabling it only stops the guaranteed-to-fail execution and the resulting fatal error in the PHP log. If AOD search is installed in the future, the scheduler can be re-enabled from Admin > Schedulers.

---

## Important Note: File Path Convention

Commits `148cfb87` and `75b721b8` placed Extension and metadata files under `suitecrm/custom/` in the repository. The correct path for SuiteCRM legacy customizations is `suitecrm/public/legacy/custom/`. In the container:

- `suitecrm/custom/` maps to `/var/www/html/custom/` — **not read by SuiteCRM legacy**
- `suitecrm/public/legacy/custom/` maps to `/var/www/html/public/legacy/custom/` — **correct location**

During deployment, files from `suitecrm/custom/` were copied to `/var/www/html/public/legacy/custom/` in the container to work around this path issue. Future commits should place all legacy customization files under `suitecrm/public/legacy/custom/`.

---

## Database Changes Applied During Deployment

These columns were added to support the new custom fields introduced in commits `148cfb87` and `75b721b8`. The vardef Extension files declare the fields to SuiteCRM, but the database columns must also exist:

```sql
ALTER TABLE accounts ADD COLUMN it_spend_c VARCHAR(255) DEFAULT NULL
  COMMENT 'Estimated annual IT spend';

ALTER TABLE opportunities ADD COLUMN referred_by_c VARCHAR(255) DEFAULT NULL
  COMMENT 'Name of the customer who referred this opportunity';
```

After a fresh database import, these columns can be created automatically by running Quick Repair & Rebuild in Admin, which detects vardef-to-schema mismatches and offers to execute the ALTER statements.

---

## Deployment Steps

```bash
# 1. Pull latest
cd /opt/suitecrm
git pull origin main

# 2. Deploy code to container
# Note: files under suitecrm/custom/ must go to public/legacy/custom/ in the container
docker cp suitecrm/custom/modules/Accounts/metadata/detailviewdefs.php \
  suitecrm_app:/var/www/html/public/legacy/custom/modules/Accounts/metadata/
docker cp suitecrm/custom/modules/Opportunities/metadata/detailviewdefs.php \
  suitecrm_app:/var/www/html/public/legacy/custom/modules/Opportunities/metadata/
docker cp suitecrm/custom/modules/Opportunities/metadata/editviewdefs.php \
  suitecrm_app:/var/www/html/public/legacy/custom/modules/Opportunities/metadata/
docker cp suitecrm/public/legacy/custom/modules/LF_WeeklyPlan/views/view.rep_report.php \
  suitecrm_app:/var/www/html/public/legacy/custom/modules/LF_WeeklyPlan/views/
docker cp suitecrm/public/legacy/custom/modules/LF_WeeklyPlan/views/view.report_save_json.php \
  suitecrm_app:/var/www/html/public/legacy/custom/modules/LF_WeeklyPlan/views/

# 3. Fix permissions
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/public/legacy/custom/

# 4. Add database columns (safe to re-run; will error harmlessly if columns exist)
docker exec suitecrm_db mysql -u root -prootpassword suitecrm_db -e "
  ALTER TABLE accounts ADD COLUMN it_spend_c VARCHAR(255) DEFAULT NULL;
  ALTER TABLE opportunities ADD COLUMN referred_by_c VARCHAR(255) DEFAULT NULL;
"

# 5. Disable broken AOD scheduler
docker exec suitecrm_db mysql -u root -prootpassword suitecrm_db -e "
  UPDATE schedulers SET status = 'Inactive'
  WHERE job = 'function::aodIndexUnindexed' AND deleted = 0;
"

# 6. Clear caches
docker exec suitecrm_redis redis-cli FLUSHALL
docker exec suitecrm_app rm -f /var/www/html/public/legacy/cache/modules/Accounts/*viewdefs*
docker exec suitecrm_app rm -f /var/www/html/public/legacy/cache/modules/Opportunities/*viewdefs*

# 7. Run Quick Repair & Rebuild from Admin UI
```
