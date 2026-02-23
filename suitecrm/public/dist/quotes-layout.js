/**
 * Quotes Detail View Layout Enhancement v13
 * Injects styles and reorganizes the legacy Quotes detail view
 * to match the Contacts/Accounts detail pages
 *
 * v13 changes:
 * - CRITICAL: Fixed 2-column layout (forced 50/50 split with flexbox)
 * - Fixed drop shadow containment
 * - Content padding verified at 20px
 *
 * v12 changes:
 * - Fixed font specificity for labels (now 12px, weight 700, #605e5c)
 * - Fixed content padding (20px)
 * - Hide empty container below Relationships section
 *
 * v10/v11 changes:
 * - Tab section transparent (no white box behind tabs)
 * - Inactive tabs: #f3f2f1 gray background (matching Contacts)
 * - Tab gap: 1px
 * - Content padding: 20px left/right
 * - Lato/system fonts
 * - Border separator between rows
 * - Hide Relationship Card
 */
(function() {
    'use strict';

    // Only run on Quotes detail pages
    function isQuotesDetailPage() {
        return window.location.hash.includes('/quotes/record/');
    }

    // CSS to inject IMMEDIATELY - hides unwanted elements
    const immediateHideCSS = `
        /* IMMEDIATE HIDE - Prevents flash of unwanted content */

        /* Hide "Quotes" module header */
        .header-module-title {
            display: none !important;
            visibility: hidden !important;
        }

        /* Hide template selector section completely */
        #popupDiv_ara,
        .pdf-templates-modal,
        #popupDivBack_ara {
            display: none !important;
        }

        /* Hide action buttons sidebar - we recreate in title area */
        #tab-actions,
        li#tab-actions,
        li#tab-actions.dropdown,
        .nav.nav-tabs > li#tab-actions,
        .tab-inline-pagination {
            display: none !important;
            visibility: hidden !important;
        }

        /* Hide broken images and icons */
        img[src*="themes/SuiteP"],
        img[src*="SuiteP"],
        img.suitepicon,
        [class*="suitepicon"] {
            display: none !important;
        }

        /* Hide inline edit icons (pencil SVGs) */
        .inlineEditIcon,
        .inlineEditIcon svg,
        span.inlineEditIcon {
            display: none !important;
        }

        /* Hide favorite icons */
        .favorite_icon_outline,
        .favorite_icon_fill {
            display: none !important;
        }

        /* Hide Relationship Card / Subpanels */
        #subpanel_list,
        .subpanel_list,
        ul.noBullet,
        #groupTabs,
        .subpanelTabForm {
            display: none !important;
            visibility: hidden !important;
        }
    `;

    // Full styling CSS
    const fullStyleCSS = `
        /* FULL STYLING */

        /* Fonts - match Contacts/Accounts pages (Lato) */
        html, body, * {
            font-family: Lato, 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', sans-serif !important;
        }

        /* Transparent background for iframe */
        html, body {
            background: transparent !important;
        }

        #pagecontent, .pagecontent {
            background: transparent !important;
            margin: 0 !important;
        }

        #content {
            background: transparent !important;
            padding: 20px !important;
            margin: 0 !important;
        }

        #bootstrap-container {
            background: transparent !important;
            padding: 0 !important;
        }

        /* Title Card Container */
        .lf-title-card {
            background: #ffffff !important;
            padding: 12px 20px !important;
            margin: 0 0 20px 0 !important;
            border-radius: 12px !important;
            
            box-shadow: 0 2px 4px rgba(0,0,0,0.08) !important;
            border: none !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }

        .lf-title-left {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }

        .lf-title-text {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #323130 !important;
        }

        .lf-title-right {
            display: flex !important;
            gap: 8px !important;
        }

        /* Hide original module title */
        .moduleTitle,
        .moduleTitle h2,
        h2,
        .detail-view > .moduleTitle {
            display: none !important;
        }

        /* ====================================
           BOOTSTRAP TAB STYLING
           ==================================== */
        .nav.nav-tabs {
            background: transparent !important;
            border: none !important;
            
            padding: 0 !important;
            margin: 0 0 0 0 !important;
            display: flex !important;
            list-style: none !important;
        }

        .nav.nav-tabs > li {
            margin: 0 1px 0 0 !important;
            padding: 0 !important;
            background: none !important;
            border: none !important;
            float: none !important;
        }

        /* Hide mobile/xs tabs and dropdown elements */
        .nav.nav-tabs > li > a.visible-xs,
        .nav.nav-tabs > li > a[id^="xstab"],
        .nav.nav-tabs .dropdown-toggle,
        #first-tab-menu-xs,
        .visible-xs,
        li#tab-actions,
        .tab-inline-pagination {
            display: none !important;
        }

        /* Hide mobile dropdown items inside FIRST li only (tab1-4 without class) */
        .nav.nav-tabs > li[role="presentation"]:first-child > a:not(.hidden-xs):not(.visible-xs):not(.dropdown-toggle) {
            display: none !important;
        }

        /* DESKTOP: First tab (OVERVIEW) - a.hidden-xs inside li.active */
        .nav.nav-tabs > li.active > a.hidden-xs {
            background: #4A90E2 !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 10px 16px !important;
            text-decoration: none !important;
            display: block !important;
            text-transform: uppercase !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            margin: 0 !important;
        }

        /* DESKTOP: Other tabs (li.hidden-xs) - inactive state (gray like Contacts page) */
        .nav.nav-tabs > li.hidden-xs > a {
            background: #f3f2f1 !important;
            color: #605e5c !important;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 10px 16px !important;
            text-decoration: none !important;
            display: block !important;
            text-transform: uppercase !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            margin: 0 !important;
        }

        .nav.nav-tabs > li.hidden-xs > a:hover {
            background: #e8e6e4 !important;
            color: #125EAD !important;
        }

        /* DESKTOP: Other tabs (li.hidden-xs) - ACTIVE state (blue) */
        .nav.nav-tabs > li.hidden-xs.active > a,
        .nav.nav-tabs > li.hidden-xs.active > a:hover,
        .nav.nav-tabs > li.hidden-xs.active > a:focus {
            background: #4A90E2 !important;
            color: #ffffff !important;
            border: none !important;
        }

        /* When another tab is active, first tab should be gray */
        .nav.nav-tabs > li:not(.active) > a.hidden-xs {
            background: #f3f2f1 !important;
            color: #605e5c !important;
        }

        .nav.nav-tabs > li:not(.active) > a.hidden-xs:hover {
            background: #e8e6e4 !important;
            color: #125EAD !important;
        }

        /* Hide mobile dropdown li elements (indices 1-4, they're duplicates for mobile dropdown) */
        /* These have role="presentation" but no hidden-xs class AND don't contain a.hidden-xs */
        .nav.nav-tabs > li[role="presentation"]:not(:first-child):not(.hidden-xs) {
            display: none !important;
        }

        /* Tab content container (the white card area) */
        .tab-content {
            background: transparent !important;
            padding: 20px !important;
            border-radius: 0 12px 12px 12px !important;
            box-shadow: none !important;
            border: none !important;
            margin-bottom: 0 !important;
            overflow: hidden !important;  /* Contain shadow within card bounds */
        }

        /* Remove any extra spacing below tab-content */
        .tab-content::after {
            content: none !important;
            display: none !important;
        }

        /* Make detail-view transparent so tabs sit on page background */
        .detail-view {
            background: transparent !important;
        }

        /* Tab pane visibility */
        .tab-pane-NOBOOTSTRAPTOGGLER {
            display: none !important;
        }

        .tab-pane-NOBOOTSTRAPTOGGLER.active {
            display: block !important;
        }

        /* ====================================
           FIELD STYLING (Bootstrap grid layout)
           ==================================== */

        /* CRITICAL: Force 2-column layout with 50/50 split */
        .detail-view-row {
            display: flex !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
            border-bottom: 1px solid #edebe9 !important;
            padding-bottom: 15px !important;
            margin-bottom: 15px !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        /* Force each column to be exactly 50% width */
        .detail-view-row > .detail-view-row-item,
        .detail-view-row > .col-sm-6,
        .detail-view-row > [class*="col-sm-6"] {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            width: 50% !important;
            min-width: 50% !important;
            padding-left: 0 !important;
            padding-right: 15px !important;
            box-sizing: border-box !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        /* Inner label/value layout within each column */
        .detail-view-row-item {
            display: flex !important;
            flex-direction: column !important;
        }

        .detail-view-row-item .label,
        .detail-view-row-item .col-1-label,
        .detail-view-row-item .col-2-label {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
        }

        .detail-view-row-item .detail-view-field {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
        }

        /* Remove border from last row */
        .detail-view-row:last-child {
            
            margin-bottom: 0 !important;
        }

        /* Field labels - match Contacts page EXACTLY (Lato, 12.16px, bold 700, #605e5c)
           Using high specificity selectors to override SuiteCRM defaults
           Contacts uses: 12.16px, 700, rgb(96, 94, 92), lineHeight: 18.24px */
        .label,
        .col-1-label,
        .col-2-label,
        div.label,
        div.col-1-label,
        div.col-2-label,
        .detail-view-row .label,
        .detail-view-row .col-1-label,
        .detail-view-row .col-2-label,
        .detail-view-row-item .label,
        .detail-view-row-item .col-1-label,
        .detail-view-row-item .col-2-label,
        .tab-content .label,
        .tab-content .col-1-label,
        .tab-content .col-2-label,
        .tabDetailViewDL,
        td.tabDetailViewDL,
        .dataLabel {
            color: #605e5c !important;
            font-family: Lato, 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', sans-serif !important;
            font-size: 12.16px !important;
            font-weight: 700 !important;
            line-height: 18.24px !important;
            text-transform: uppercase !important;
            letter-spacing: normal !important;
            padding: 0 0 4px 0 !important;
            background: transparent !important;
            border: none !important;
        }

        /* Field values - match Contacts page EXACTLY (Lato, 13px, normal, gray)
           Contacts uses: 13px, 400, rgb(138, 136, 134), lineHeight: 19.5px */
        .detail-view-field,
        .sugar_field,
        div.detail-view-field,
        div.sugar_field,
        .detail-view-row .detail-view-field,
        .detail-view-row .sugar_field,
        .detail-view-row-item .detail-view-field,
        .detail-view-row-item .sugar_field,
        .tab-content .detail-view-field,
        .tab-content .sugar_field,
        .tabDetailViewDF,
        td.tabDetailViewDF,
        .dataField {
            color: #8a8886 !important;
            font-family: Lato, 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', sans-serif !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            line-height: 19.5px !important;
            padding: 0 !important;
            background: transparent !important;
        }

        /* Links in field values should be styled */
        .detail-view-field a,
        .sugar_field a {
            color: #125EAD !important;
        }

        /* Remove dotted borders that SuiteCRM adds */
        .dotted-border {
            display: none !important;
        }

        /* Ensure 2-column layout works */
        .detail-view-row-item {
            padding: 0 15px !important;
        }

        /* Legacy table styles (fallback) */
        table.tabDetailView,
        table.detail.view {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        /* Links */
        a {
            color: #125EAD !important;
            text-decoration: none !important;
        }

        a:hover {
            text-decoration: underline !important;
        }

        /* ====================================
           SUBPANELS / RELATIONSHIP CARD - HIDDEN
           ==================================== */
        #subpanel_list,
        .subpanel_list,
        ul.noBullet,
        #groupTabs,
        .subpanelTabForm,
        table.subpanelTabForm,
        .lf-relationship-header {
            display: none !important;
            visibility: hidden !important;
        }

        /* Hide empty containers below content (the rounded white box issue) */
        .panel,
        .panel-default,
        .panel-body,
        .card:empty,
        .card-body:empty,
        div.panel,
        div.card {
            display: none !important;
            visibility: hidden !important;
        }

        /* Hide any empty wrapper divs that might show up */
        #content > div:empty,
        #pagecontent > div:empty,
        .tab-content + div,
        .tab-content ~ .panel,
        .tab-content ~ .card,
        .detail-view + div:not(.lf-title-card),
        #EditView_tabs + div {
            display: none !important;
            visibility: hidden !important;
        }

        /* Ensure content padding is 20px */
        .tab-content,
        #EditView_tabs > .tab-content,
        .detail-view .tab-content {
            padding: 20px !important;
        }

        /* ====================================
           BUTTONS
           ==================================== */
        .lf-btn {
            padding: 8px 16px !important;
            border-radius: 6px !important;
            font-size: 13px !important;
            cursor: pointer !important;
            border: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            font-family: inherit !important;
        }

        .lf-btn-primary {
            background: #125EAD !important;
            color: #ffffff !important;
        }

        .lf-btn-primary:hover {
            background: #0A3D6B !important;
        }

        /* Dropdown styling */
        .lf-dropdown {
            position: relative !important;
            display: inline-block !important;
        }

        .lf-dropdown-menu {
            display: none !important;
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            background: #ffffff !important;
            border: 1px solid #edebe9 !important;
            border-radius: 6px !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12) !important;
            min-width: 180px !important;
            z-index: 1000 !important;
            margin-top: 4px !important;
        }

        .lf-dropdown-item {
            display: block !important;
            padding: 10px 16px !important;
            color: #605e5c !important;
            text-decoration: none !important;
            cursor: pointer !important;
            font-size: 13px !important;
        }

        .lf-dropdown-item:hover {
            background: #faf9f8 !important;
            color: #125EAD !important;
        }

        .lf-dropdown-divider {
            border-top: 1px solid #edebe9 !important;
            margin: 4px 0 !important;
        }
    `;

    // Inject CSS into iframe document - APPEND to end of head for higher specificity
    function injectCSS(doc, css, id) {
        if (doc.getElementById(id)) return true;

        const style = doc.createElement('style');
        style.id = id;
        style.textContent = css;

        // Append to END of head for higher cascade priority
        doc.head.appendChild(style);
        return true;
    }

    // Create the Title Card with buttons
    function createTitleCard(doc) {
        if (doc.querySelector('.lf-title-card')) return;

        // Find the title text from the page
        const h2 = doc.querySelector('h2, .moduleTitle h2');
        let titleText = 'Quote';
        if (h2) {
            titleText = h2.textContent.trim();
        } else {
            // Try to get from tab content
            const titleField = doc.querySelector('#tab-content-0 td.tabDetailViewDF');
            if (titleField) titleText = titleField.textContent.trim();
        }

        // Find original buttons
        const editBtn = doc.querySelector('input[name="Edit"], input[value="Edit"]');
        const duplicateBtn = doc.querySelector('input[value="Duplicate"]');
        const deleteBtn = doc.querySelector('input[value="Delete"]');
        const printPdfBtn = doc.querySelector('input[value="Print as PDF"]');
        const emailPdfBtn = doc.querySelector('input[value="Email PDF"]');
        const emailQuoteBtn = doc.querySelector('input[value="Email Quotation"]');
        const createContractBtn = doc.querySelector('input[value="Create Contract"]');
        const convertInvoiceBtn = doc.querySelector('input[value="Convert to Invoice"]');

        // Create title card
        const titleCard = doc.createElement('div');
        titleCard.className = 'lf-title-card';
        titleCard.innerHTML = `
            <div class="lf-title-left">
                <span class="lf-title-text">${titleText}</span>
            </div>
            <div class="lf-title-right">
                <button class="lf-btn lf-btn-primary" id="lf-edit-btn">Edit</button>
                <div class="lf-dropdown">
                    <button class="lf-btn lf-btn-primary" id="lf-actions-btn">Actions ▾</button>
                    <div class="lf-dropdown-menu" id="lf-actions-menu">
                        <a class="lf-dropdown-item" id="lf-duplicate">Duplicate</a>
                        <a class="lf-dropdown-item" id="lf-delete">Delete</a>
                        <div class="lf-dropdown-divider"></div>
                        <a class="lf-dropdown-item" id="lf-print-pdf">Print as PDF</a>
                        <a class="lf-dropdown-item" id="lf-email-pdf">Email PDF</a>
                        <a class="lf-dropdown-item" id="lf-email-quote">Email Quotation</a>
                        <div class="lf-dropdown-divider"></div>
                        <a class="lf-dropdown-item" id="lf-create-contract">Create Contract</a>
                        <a class="lf-dropdown-item" id="lf-convert-invoice">Convert to Invoice</a>
                    </div>
                </div>
            </div>
        `;

        // Insert at beginning of content
        const content = doc.getElementById('content') || doc.getElementById('pagecontent') || doc.body;
        if (content.firstChild) {
            content.insertBefore(titleCard, content.firstChild);
        } else {
            content.appendChild(titleCard);
        }

        // Wire up buttons
        const lfEditBtn = doc.getElementById('lf-edit-btn');
        if (lfEditBtn && editBtn) {
            lfEditBtn.onclick = () => editBtn.click();
        }

        // Actions dropdown toggle — use setProperty with !important to override CSS
        const actionsBtn = doc.getElementById('lf-actions-btn');
        const actionsMenu = doc.getElementById('lf-actions-menu');
        if (actionsBtn && actionsMenu) {
            actionsMenu.style.setProperty('display', 'none', 'important');

            actionsBtn.onclick = (e) => {
                e.stopPropagation();
                const isShown = actionsMenu.style.display === 'block';
                actionsMenu.style.setProperty('display', isShown ? 'none' : 'block', 'important');
            };

            doc.addEventListener('click', (e) => {
                if (!actionsBtn.contains(e.target) && !actionsMenu.contains(e.target)) {
                    actionsMenu.style.setProperty('display', 'none', 'important');
                }
            });
        }

        // Wire dropdown items
        const wireButton = (lfId, originalBtn) => {
            const lfBtn = doc.getElementById(lfId);
            if (lfBtn && originalBtn) {
                lfBtn.onclick = (e) => {
                    e.preventDefault();
                    originalBtn.click();
                    actionsMenu.style.display = 'none';
                };
            }
        };

        wireButton('lf-duplicate', duplicateBtn);
        wireButton('lf-delete', deleteBtn);
        wireButton('lf-print-pdf', printPdfBtn);
        wireButton('lf-email-pdf', emailPdfBtn);
        wireButton('lf-email-quote', emailQuoteBtn);
        wireButton('lf-create-contract', createContractBtn);
        wireButton('lf-convert-invoice', convertInvoiceBtn);

        console.log('[LF Quotes] Created title card');
    }

    // Fix tab functionality
    function fixTabs(doc) {
        const navTabs = doc.querySelector('.nav.nav-tabs');
        if (!navTabs) return;

        const tabPanes = doc.querySelectorAll('.tab-pane-NOBOOTSTRAPTOGGLER');

        // Desktop tab structure:
        // - li.active a.hidden-xs#tab0 (OVERVIEW) → tab-content-0
        // - li.hidden-xs a#tab1 (Quote To) → tab-content-1
        // - li.hidden-xs a#tab2 (Line Items) → tab-content-2
        // - li.hidden-xs a#tab3 (OTHER) → tab-content-3

        // Get the first tab (OVERVIEW) - it's inside li.active with class hidden-xs on the anchor
        const firstTab = navTabs.querySelector('li.active > a.hidden-xs');
        if (firstTab) {
            firstTab.addEventListener('click', (e) => {
                e.preventDefault();
                switchToTab(doc, navTabs, firstTab.parentElement, 0);
            });
        }

        // Get other desktop tabs (li.hidden-xs)
        const otherDesktopTabs = navTabs.querySelectorAll('li.hidden-xs > a');
        otherDesktopTabs.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                // Extract tab number from id (tab1, tab2, tab3)
                const tabNum = parseInt(link.id.replace('tab', ''), 10);
                switchToTab(doc, navTabs, link.parentElement, tabNum);
            });
        });

        console.log('[LF Quotes] Tab functionality fixed');
    }

    // Switch to a specific tab
    function switchToTab(doc, navTabs, clickedLi, tabNum) {
        const tabPanes = doc.querySelectorAll('.tab-pane-NOBOOTSTRAPTOGGLER');

        // Remove active from all tab LIs
        // First tab (li.active with a.hidden-xs)
        const firstLi = navTabs.querySelector('li[role="presentation"].active');
        if (firstLi) firstLi.classList.remove('active');

        // Other desktop tabs (li.hidden-xs)
        navTabs.querySelectorAll('li.hidden-xs').forEach(li => li.classList.remove('active'));

        // Add active to clicked li
        clickedLi.classList.add('active');

        // Hide all panes
        tabPanes.forEach(pane => {
            pane.classList.remove('active', 'in');
        });

        // Show target pane
        const targetPane = doc.getElementById('tab-content-' + tabNum);
        if (targetPane) {
            targetPane.classList.add('active', 'in');
        }

        console.log('[LF Quotes] Switched to tab', tabNum);
    }

    // DOM manipulation
    function manipulateDOM(doc) {
        console.log('[LF Quotes] Running DOM manipulation...');

        // Force hide elements with inline styles (backup for CSS)
        const forceHide = (selector) => {
            doc.querySelectorAll(selector).forEach(el => {
                el.style.setProperty('display', 'none', 'important');
                el.style.setProperty('visibility', 'hidden', 'important');
            });
        };

        // Hide "Quotes" module header
        forceHide('.header-module-title');

        // Hide original title elements
        forceHide('.moduleTitle');
        forceHide('h2');

        // Hide the original buttons sidebar (multiple selectors)
        forceHide('#tab-actions');
        forceHide('li#tab-actions');

        // Hide template selector
        forceHide('#popupDiv_ara');
        forceHide('#popupDivBack_ara');

        // Hide inline edit icons
        forceHide('.inlineEditIcon');

        // Hide favorite icons
        forceHide('.favorite_icon_outline');
        forceHide('.favorite_icon_fill');

        // Hide Relationship Card / Subpanels
        forceHide('#subpanel_list');
        forceHide('.subpanel_list');
        forceHide('ul.noBullet');
        forceHide('#groupTabs');
        forceHide('.subpanelTabForm');

        // Create title card
        createTitleCard(doc);

        // Fix tab functionality
        fixTabs(doc);
    }

    // Main function to process iframe
    function processIframe(iframe) {
        try {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc || !doc.head) {
                setTimeout(() => processIframe(iframe), 200);
                return;
            }

            // Inject CSS
            injectCSS(doc, immediateHideCSS, 'lf-quotes-hide');
            injectCSS(doc, fullStyleCSS, 'lf-quotes-style');

            // Run DOM manipulation
            if (doc.readyState === 'complete') {
                manipulateDOM(doc);
            } else {
                doc.addEventListener('DOMContentLoaded', () => manipulateDOM(doc));
                iframe.addEventListener('load', () => {
                    setTimeout(() => manipulateDOM(doc), 100);
                });
            }

            console.log('[LF Quotes] Processing complete');

        } catch (e) {
            console.error('[LF Quotes] Error:', e);
        }
    }

    // Initialize
    function init() {
        if (!isQuotesDetailPage()) return;

        console.log('[LF Quotes] Initializing...');

        const classicView = document.querySelector('scrm-classic-view-ui');
        if (!classicView) {
            setTimeout(init, 300);
            return;
        }

        const iframe = classicView.querySelector('iframe');
        if (!iframe) {
            setTimeout(init, 300);
            return;
        }

        if (iframe.contentDocument && iframe.contentDocument.readyState !== 'loading') {
            processIframe(iframe);
        }

        iframe.addEventListener('load', () => {
            setTimeout(() => processIframe(iframe), 50);
        });
    }

    // Watch for navigation
    function setupWatcher() {
        let lastHash = window.location.hash;

        window.addEventListener('hashchange', () => {
            if (window.location.hash !== lastHash) {
                lastHash = window.location.hash;
                setTimeout(init, 500);
            }
        });

        const observer = new MutationObserver(() => {
            if (isQuotesDetailPage()) {
                const iframe = document.querySelector('scrm-classic-view-ui iframe');
                if (iframe && !iframe.contentDocument?.getElementById('lf-quotes-hide')) {
                    setTimeout(init, 300);
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    // Start
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init();
            setupWatcher();
        });
    } else {
        init();
        setupWatcher();
    }

})();
