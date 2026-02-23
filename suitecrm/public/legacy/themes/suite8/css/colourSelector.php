<?php
/**
 * Logical Front Theme - Version 8 (Consolidated)
 * Doc 251 - Merged colourSelector.php + dashboard-override.css
 * All dashboard CSS in one tokenized file
 * LOADS LAST in CSS cascade
 *
 * !important USAGE DOCUMENTATION:
 * All !important declarations are required to override:
 * 1. logical-front-theme.css - Has high specificity rules with #dashboard prefix
 * 2. SuiteCRM default styles - Inline styles and legacy CSS
 * 3. Bootstrap styles - Framework defaults
 *
 * Without !important, these rules would not apply due to CSS cascade order
 * and specificity conflicts in the multi-layer architecture.
 */
header('Content-type: text/css; charset: UTF-8');

if (is_file('../../../config.php')) {
    require_once '../../../config.php';
}
if (is_file('../../../config_override.php')) {
    require_once '../../../config_override.php';
}

/* ==============================================
   TOKEN DEFINITIONS - Dashboard Theme
   All tokens from both original files
   ============================================== */

// Widget Panel - Uses --border-color (#edebe9) from prototype
$dashlet_panel_bg = '#ffffff';
$dashlet_panel_border = '1px solid #edebe9';
$dashlet_panel_shadow = '0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05)';

// Widget Header
$dashlet_header_bg = '#125EAD';
$dashlet_header_text = '#ffffff';

// Widget Body
$dashlet_body_bg = '#ffffff';

// Pagination Row - Uses --gray-300 (#e1dfdd) from prototype
$pagination_row_bg = '#e1dfdd';

// Pagination Text
$pagination_text = '#333333';

// Pagination Buttons - Uses --border-color (#edebe9) from prototype
$pagination_button_bg = '#ffffff';
$pagination_button_border = '1px solid #edebe9';

// Pagination Arrows (each arrow is a separate token)
$pagination_arrow_first = '#333333';
$pagination_arrow_prev = '#333333';
$pagination_arrow_next = '#333333';
$pagination_arrow_last = '#333333';

// Table Headers - Uses --border-color (#edebe9) from prototype
$table_header_bg = '#ffffff';
$table_header_text = '#125EAD';
$table_header_border = '1px solid #edebe9';

// Title Card
$title_card_bg = '#ffffff';

// Tool Set (header buttons)
$dashlet_tool_color = '#ffffff';
$dashlet_tool_opacity = '0.8';
$dashlet_tool_hover_opacity = '1';

?>
/* ==============================================
   DOC 251 - CONSOLIDATED DASHBOARD CSS
   Merged from colourSelector.php + dashboard-override.css
   ============================================== */

/* ==============================================
   1. DASHBOARD CONTAINER - TRANSPARENT
   From dashboard-override.css section 1
   ============================================== */
html, body,
#dashboard,
.dashboard,
div#dashboard,
div.dashboard,
body #dashboard,
body .dashboard,
.view-module-Home #dashboard,
#pagecontent #dashboard,
.pagecontent .dashboard,
#pagecontent,
#pageContainer,
#bootstrap-container {
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
}

.dashletTable,
table.dashletTable,
#dashletsSidebar,
.tab-content,
.tab-pane,
.dashlet-page,
td.dashboard-col,
.dashboard-col,
td[valign="top"] {
    background: transparent !important;
    background-color: transparent !important;
}

table,
tbody,
tr:not(.pagination),
td:not(.pagination td) {
    background: transparent !important;
}

/* ==============================================
   2. REMOVE WHITE LINES AROUND DASHBOARD
   Target body, html and all container elements
   ============================================== */

/* Body and HTML - transparent with zero margin/padding */
html,
body,
body.yui-skin-sam {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    outline: none !important;
    background: transparent !important;
    background-color: transparent !important;
}

/* Critical: body > div often has a beige background - make it transparent */
body > div,
body > div:first-child,
#wrapper,
.wrapper,
#container,
.container {
    background: transparent !important;
    background-color: transparent !important;
    border: none !important;
}

/* All major containers - NO background, NO border, NO padding */
#pageContainer,
#pagecontent,
#bootstrap-container,
.tab-content,
.tab-pane,
.dashlet-page,
.dashletTable,
table.dashletTable,
#dashboard,
.dashboard,
body > table,
body > div > table,
.yui-skin-sam,
.yui-module,
#moduleList,
.moduleList,
#content,
.content,
#main,
.main {
    background: transparent !important;
    background-color: transparent !important;
    box-shadow: none !important;
    border: none !important;
    outline: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Override any inherited padding on pagecontent - zero to eliminate edge gaps */
/* Using multiple selectors for higher specificity */
html body #pageContainer #pagecontent,
body #pageContainer #pagecontent,
#pageContainer #pagecontent,
body #pagecontent,
html #pagecontent,
#pagecontent {
    padding: 0 !important;
    margin: 0 !important;
}

/* Remove box-shadow AND border-radius from dashboard container - HIGHER SPECIFICITY */
/* This eliminates the U-shaped white line around the content */
html body #dashboard,
body #dashboard,
#pageContainer #dashboard,
#pagecontent #dashboard,
body .dashboard,
#dashboard,
.dashboard {
    box-shadow: none !important;
    margin: 0 !important;
    border-radius: 0 !important;
    border: none !important;
}

/* Widget columns get internal padding instead */
td.dashboard-col,
.dashboard-col {
    padding: 10px !important;
}

/* Table and cell elements */
#pagecontent > table,
#pagecontent > div,
#dashboard > table,
#dashboard > tbody,
#dashboard > tbody > tr,
#dashboard > tbody > tr > td,
.dashletTable > tbody,
.dashletTable > tbody > tr,
.dashletTable > tbody > tr > td,
table[border],
table[border="0"],
table[border="1"],
table[cellspacing] {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
}

/* Column containers - zero margins */
td.dashboard-col,
.dashboard-col,
td[valign="top"][width] {
    padding: 10px !important;
    margin: 0 !important;
    border: none !important;
}

/* ==============================================
   3. WIDGET PANELS - WITH TOKENS
   From dashboard-override.css section 2 + colourSelector
   ============================================== */
.dashletPanel {
    background: <?php echo $dashlet_panel_bg; ?> !important;
    border-radius: 12px !important;
    box-shadow: <?php echo $dashlet_panel_shadow; ?> !important;
    overflow: hidden !important;
    border: none !important;
    margin-bottom: 20px !important;
}

/* ==============================================
   4. WIDGET HEADERS - WITH TOKENS
   From dashboard-override.css section 3
   Higher specificity with #dashboard prefix
   ============================================== */
#dashboard .hd.dashlet,
#dashboard .dashletPanel .hd,
#dashboard .dashlet .hd,
body .hd.dashlet,
body .dashletPanel .hd,
body .dashlet .hd,
.hd.dashlet,
.dashletPanel .hd,
.dashlet .hd {
    background: <?php echo $dashlet_header_bg; ?> !important;
    color: <?php echo $dashlet_header_text; ?> !important;
    border-radius: 12px 12px 0 0 !important;
    padding: 0 !important;
}

#dashboard .hd-center,
.hd-center {
    background: transparent !important;
    padding: 10px 12px !important;
}

/* Title text - Higher specificity */
#dashboard .dashlet-title,
#dashboard .dashboard-title,
#dashboard td.dashlet-title,
#dashboard .dashlet-title span,
#dashboard .dashboard-title span,
.dashlet-title,
.dashboard-title,
td.dashlet-title,
.dashlet-title span,
.dashboard-title span {
    background: transparent !important;
    color: <?php echo $dashlet_header_text; ?> !important;
}

/* Title row */
#dashboard .formHeader.h3Row,
#dashboard .formHeader.h3Row tr,
#dashboard .formHeader.h3Row tbody,
#dashboard .formHeader.h3Row td,
.formHeader.h3Row,
.formHeader.h3Row tr,
.formHeader.h3Row tbody,
.formHeader.h3Row td {
    background: transparent !important;
    color: <?php echo $dashlet_header_text; ?> !important;
}

/* Icon elements */
#dashboard .suitepicon,
#dashboard .hd.dashlet .suitepicon,
.suitepicon,
.hd.dashlet .suitepicon {
    color: <?php echo $dashlet_header_text; ?> !important;
    background: transparent !important;
}

/* Hide decorative corners */
.tl, .tr {
    display: none !important;
}

/* Tool buttons - Higher specificity */
#dashboard .dashletToolSet,
.dashletToolSet {
    background: transparent !important;
}

#dashboard .dashletToolSet a,
.dashletToolSet a {
    color: <?php echo $dashlet_tool_color; ?> !important;
    opacity: <?php echo $dashlet_tool_opacity; ?> !important;
}

#dashboard .dashletToolSet a:hover,
.dashletToolSet a:hover {
    opacity: <?php echo $dashlet_tool_hover_opacity; ?> !important;
}

#dashboard .dashletToolSet svg,
.dashletToolSet svg {
    fill: <?php echo $dashlet_tool_color; ?> !important;
}

/* ==============================================
   5. WIDGET BODY - WITH TOKENS
   From dashboard-override.css section 5
   Higher specificity with #dashboard prefix
   ============================================== */
#dashboard .bd.dashlet,
#dashboard .dashletPanel .bd,
#dashboard .dashlet .bd,
#dashboard .bd-center,
#dashboard .dashletNonTable,
body .bd.dashlet,
body .dashletPanel .bd,
body .dashlet .bd,
.bd.dashlet,
.dashletPanel .bd,
.dashlet .bd,
.dashlet-body {
    background: <?php echo $dashlet_body_bg; ?> !important;
    border-radius: 0 0 12px 12px !important;
}

/* ==============================================
   6. PAGINATION ROW - FULL WIDTH
   From colourSelector + dashboard-override section 6
   Higher specificity with #dashboard to override logical-front-theme.css
   ============================================== */
#dashboard tr.pagination,
#dashboard thead tr.pagination,
#dashboard .dashletPanel tr.pagination,
#dashboard .dashletPanel thead tr.pagination,
#dashboard .dashletPanel tbody tr.pagination,
#dashboard .dashletPanel .pagination,
#dashboard .dashletPanel #paginationDiv,
#dashboard .bd > #paginationDiv,
tr.pagination,
thead tr.pagination,
.dashletPanel tr.pagination,
.dashletPanel thead tr.pagination,
.dashletPanel tbody tr.pagination,
.dashletPanel .pagination,
.dashletPanel #paginationDiv,
.bd > #paginationDiv {
    display: block !important;
    background: <?php echo $pagination_row_bg; ?> !important;
    background-color: <?php echo $pagination_row_bg; ?> !important;
    width: 100% !important;
    padding: 6px 12px !important;
    box-sizing: border-box !important;
}

#dashboard tr.pagination td,
#dashboard tr.pagination > td,
#dashboard .dashletPanel tr.pagination td,
#dashboard .dashletPanel tr.pagination > td,
tr.pagination td,
tr.pagination > td,
.dashletPanel tr.pagination td,
.dashletPanel tr.pagination > td {
    display: block !important;
    background: transparent !important;
    padding: 0 !important;
    border: none !important;
    width: 100% !important;
}

tr.pagination table,
tr.pagination td table,
tr.pagination td > table {
    display: block !important;
    width: 100% !important;
    background: transparent !important;
    border: none !important;
}

tr.pagination table tr,
tr.pagination table tbody tr {
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
    width: 100% !important;
    background: transparent !important;
}

tr.pagination table td[align="left"],
tr.pagination table td:first-child:not([align="right"]):not(:only-child) {
    display: none !important;
    width: 0 !important;
}

tr.pagination table td[align="right"],
tr.pagination table td:last-child {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
    background: transparent !important;
    padding: 0 !important;
    border: none !important;
}

/* ==============================================
   7. PAGE NUMBERS - BOLD
   Higher specificity with #dashboard prefix
   ============================================== */
#dashboard .pageNumbers,
#dashboard span.pageNumbers,
#dashboard tr.pagination .pageNumbers,
#dashboard tr.pagination span.pageNumbers,
.pageNumbers,
span.pageNumbers,
tr.pagination .pageNumbers,
tr.pagination span.pageNumbers {
    color: <?php echo $pagination_text; ?> !important;
    font-weight: 900 !important;
    font-size: 12px !important;
    display: inline-block !important;
    vertical-align: middle !important;
    padding: 0 8px !important;
    font-family: Arial, Helvetica, sans-serif !important;
}

/* ==============================================
   8. PAGINATION BUTTONS
   Higher specificity with #dashboard prefix
   ============================================== */
#dashboard tr.pagination button,
#dashboard tr.pagination button.button,
#dashboard tr.pagination .button,
tr.pagination button,
tr.pagination button.button,
tr.pagination .button {
    background: <?php echo $pagination_button_bg; ?> !important;
    border: <?php echo $pagination_button_border; ?> !important;
    border-radius: 3px !important;
    padding: 3px 8px !important;
    margin: 0 2px !important;
    min-width: 28px !important;
    height: 26px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    vertical-align: middle !important;
    cursor: pointer !important;
}

#dashboard tr.pagination button:disabled,
tr.pagination button:disabled {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
}

/* ==============================================
   9. PAGINATION ARROWS - CSS ICONS (Replace SVG)
   Hide SVG images and show CSS Unicode arrows
   ============================================== */

/* Hide all SVG/IMG images inside pagination buttons */
tr.pagination button img,
tr.pagination button span img,
tr.pagination button svg,
tr.pagination button span svg,
.suitepicon-action-first img,
.suitepicon-action-left img,
.suitepicon-action-right img,
.suitepicon-action-last img {
    display: none !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    opacity: 0 !important;
}

/* Base styles for suitepicon elements */
.suitepicon-action-first,
.suitepicon-action-left,
.suitepicon-action-right,
.suitepicon-action-last {
    font-size: 0 !important;
    line-height: 0 !important;
    color: transparent !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 16px !important;
    min-height: 16px !important;
}

/* CSS Unicode arrows with tokenized colors */
.suitepicon-action-first::after {
    content: "\00AB" !important;
    font-size: 16px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_first; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
    line-height: 1 !important;
    display: block !important;
}

.suitepicon-action-left::after {
    content: "\2039" !important;
    font-size: 18px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_prev; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
    line-height: 1 !important;
    display: block !important;
}

.suitepicon-action-right::after {
    content: "\203A" !important;
    font-size: 18px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_next; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
    line-height: 1 !important;
    display: block !important;
}

.suitepicon-action-last::after {
    content: "\00BB" !important;
    font-size: 16px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_last; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
    line-height: 1 !important;
    display: block !important;
}

/* Fallback: Target pagination buttons by position if suitepicon classes don't exist */
/* Button order: First(1), Prev(2), Next(3), Last(4) */
tr.pagination button:nth-of-type(1) span::after,
tr.pagination td[align="right"] button:nth-of-type(1) span::after {
    content: "\00AB" !important;
    font-size: 16px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_first; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
}

tr.pagination button:nth-of-type(2) span::after,
tr.pagination td[align="right"] button:nth-of-type(2) span::after {
    content: "\2039" !important;
    font-size: 18px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_prev; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
}

tr.pagination button:nth-of-type(3) span::after,
tr.pagination td[align="right"] button:nth-of-type(3) span::after {
    content: "\203A" !important;
    font-size: 18px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_next; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
}

tr.pagination button:nth-of-type(4) span::after,
tr.pagination td[align="right"] button:nth-of-type(4) span::after {
    content: "\00BB" !important;
    font-size: 16px !important;
    font-weight: bold !important;
    color: <?php echo $pagination_arrow_last; ?> !important;
    font-family: Arial, Helvetica, sans-serif !important;
}

/* ==============================================
   10. HIDE FOOTER DIV
   ============================================== */
.dashletPanel > .ft,
.dashletPanel .ft,
div.ft,
.ft,
.dashletPanel > div:last-child:not(.bd):not(.hd):not(table) {
    display: none !important;
    height: 0 !important;
    max-height: 0 !important;
    visibility: hidden !important;
    overflow: hidden !important;
    padding: 0 !important;
    margin: 0 !important;
}

/* ==============================================
   11. ROUNDED CORNERS ON PAGINATION
   ============================================== */
tr.pagination {
    border-bottom-left-radius: 12px !important;
    border-bottom-right-radius: 12px !important;
}

/* ==============================================
   12. WIDGET TABLE HEADERS - WITH TOKENS
   From dashboard-override.css section 4
   Higher specificity with #dashboard prefix
   ============================================== */
#dashboard .dashletPanel table th,
#dashboard .bd table th,
#dashboard .dashletPanel thead th,
#dashboard .dashlet table th,
#dashboard .dashlet th,
#dashboard .widget-table th,
body .dashletPanel table th,
body .bd table th,
body .dashletPanel thead th,
body .dashlet table th,
.dashletPanel table th,
.bd table th,
.dashletPanel thead th,
.dashlet table th {
    background: <?php echo $table_header_bg; ?> !important;
    color: <?php echo $table_header_text; ?> !important;
    font-weight: 700 !important;
    border-bottom: <?php echo $table_header_border; ?> !important;
    text-transform: uppercase !important;
    font-size: 12px !important;
}

/* ==============================================
   13. TITLE CARD
   ============================================== */
.moduleTitle,
h2.module-title-text,
.dashlet-title {
    background: <?php echo $title_card_bg; ?> !important;
    padding: 12px 20px !important;
    margin: 20px !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}
