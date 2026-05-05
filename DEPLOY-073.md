# Deployment Notes — Quote Actions & PDF Notes Fix

**Date:** 2026-05-05
**Previous commit:** `75b721b8` — Add Referred By field to Opportunities
**Sessions:** CRM 065–072

---

## Changes Summary

### 1. Quote Detail — Actions Dropdown (v13 → v18)
**File:** `public/dist/quotes-layout.js`

All Actions dropdown items on the Quote detail page are now functional. Five versions of fixes were required:

**Root causes discovered:**
- **v14:** jQuery's `sugarMenu` plugin converts `<input>` buttons to `<a>` tags at DOMContentLoaded, before `createTitleCard` runs — so all `querySelector('input[value="..."]')` returned null and `wireButton` silently did nothing. Replaced the entire wireButton pattern with direct navigation and `showPopup()` calls.
- **v15:** Duplicate URL was `DuplicateClassic` (doesn't exist in SuiteCRM 8). Fixed to `EditView&isDuplicate=true`. Also removed `#popupDiv_ara` / `#popupDivBack_ara` from `immediateHideCSS !important` rule, which was blocking `showPopup()`.
- **v16:** `#popupDiv_ara` has no positioning CSS in SuiteCRM's own stylesheets — it renders in document flow behind the `position:fixed` backdrop. Added `position:fixed; z-index:9999; top:50%; left:50%`.
- **v17:** `position:fixed` inside an iframe is relative to the iframe's full coordinate space (~3755px tall), not the viewport. `top:50%` placed the popup ~1877px below the visible area. Also, `Dawn/style.css` has `#pagecontent > :first-child:not(.row):not(.col) { display:none !important }` which matches `#popupDiv_ara`. Non-`!important` inline styles can't beat this. Fixed with `setProperty('display','block','important')` and `position:absolute` with coordinates calculated from the outer window scroll position.
- **v18:** `showPopup()` (SuiteCRM's own function) does `ppd.style.display='block'` (plain assignment), which removes the `!important` priority flag set by our earlier `setProperty`. Called `showPopup()` first (it still sets `form.task.value`), then re-applied `setProperty(...,'important')` after.

**All Actions verified working:**
- Edit → SPA hash navigation
- Duplicate → `EditView&isDuplicate=true`
- Delete → confirm dialog → `action=Delete`
- Print as PDF / Email PDF / Email Quotation → `showPopup()` with correct modal positioning
- Create Contract / Convert to Invoice → form hidden input + `form.submit()`

**Deployment:**
```bash
docker cp public/dist/quotes-layout.js suitecrm_app:/var/www/html/public/dist/
```

---

### 2. Quote Detail — Cache Bust
**File:** `public/dist/index.html`

`quotes-layout.js` reference updated to `?v=18` for browser cache busting.

**Deployment:**
```bash
docker cp public/dist/index.html suitecrm_app:/var/www/html/public/dist/
```

---

### 3. PDF Templates — `$aos_quotes_quotes_notes` Unresolved Variable
**Files:**
- `public/legacy/custom/Extension/modules/AOS_Quotes/Ext/Vardefs/quotes_notes.php` *(new)*
- `public/legacy/custom/modules/AOS_Quotes/Ext/Vardefs/vardefs.ext.php` *(new)*

**Root cause:** The `quotes_notes` column exists in the `aos_quotes` DB table and has content in many records. Both PDF templates ("Quote Default" and "Quote Added Details") reference it as `$aos_quotes_quotes_notes` at the bottom of the Notes section. However, the field was never declared in the AOS_Quotes module's vardefs, so SuiteCRM's Smarty PDF engine had no entry for it and output the raw variable name as literal text.

**Fix:** Added a vardef extension declaring `quotes_notes` as a `text` field. SuiteCRM's Extension framework merges `custom/Extension/modules/<Module>/Ext/Vardefs/*.php` files into `custom/modules/<Module>/Ext/Vardefs/vardefs.ext.php` during Quick Repair & Rebuild. Both files are included here.

After deployment, delete the vardef cache to force rebuild on next request:
```bash
docker cp public/legacy/custom/Extension/modules/AOS_Quotes/Ext/Vardefs/quotes_notes.php \
    suitecrm_app:/var/www/html/public/legacy/custom/Extension/modules/AOS_Quotes/Ext/Vardefs/

docker cp public/legacy/custom/modules/AOS_Quotes/Ext/Vardefs/vardefs.ext.php \
    suitecrm_app:/var/www/html/public/legacy/custom/modules/AOS_Quotes/Ext/Vardefs/

docker exec suitecrm_app rm -f /var/www/html/public/legacy/cache/modules/AOS_Quotes/AOS_Quotesvardefs.php
```

Then load any Quote detail page once to trigger cache rebuild. Subsequent PDF generation will substitute the actual notes content.

**Verification:** Quotes with notes content (e.g. "San Jacinto College FX Quote", "Conroe ISD VMWare 1 Year") will now show their notes text in the PDF Notes section. Quotes with no notes content will show a blank Notes section, which is correct.
