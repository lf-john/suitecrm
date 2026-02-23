/**
 * State Abbreviation Mapping and Address Layout Fix
 * Converts full state names to abbreviations in address displays
 * Also applies address field layout (Street on line 1, City State Zip on line 2)
 */
(function() {
    'use strict';

    console.log('LF: State abbreviations and address layout script loaded');

    // Inject dynamic CSS for address layout
    function injectAddressCSS() {
        if (document.getElementById('lf-address-css')) return;

        const style = document.createElement('style');
        style.id = 'lf-address-css';
        style.textContent = `
            /* Address group - horizontal layout */
            scrm-group-field .field-group.flex-column {
                flex-direction: row !important;
                flex-wrap: wrap !important;
                gap: 2px 4px !important;
                align-items: baseline !important;
            }
            /* All field-group-items default to auto width */
            scrm-group-field .field-group .field-group-item,
            scrm-group-field .field-group .field-group-item.w-100 {
                width: auto !important;
                flex: 0 0 auto !important;
            }
            /* Street takes full row */
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-billing_address_street),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-shipping_address_street),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-primary_address_street),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-alt_address_street) {
                order: 0 !important;
                flex: 0 0 100% !important;
                width: 100% !important;
            }
            /* City */
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-billing_address_city),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-shipping_address_city),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-primary_address_city),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-alt_address_city) {
                order: 1 !important;
                margin-right: 0 !important;
            }
            /* State */
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-billing_address_state),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-shipping_address_state),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-primary_address_state),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-alt_address_state) {
                order: 2 !important;
            }
            /* Postal code */
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-billing_address_postalcode),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-shipping_address_postalcode),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-primary_address_postalcode),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-alt_address_postalcode) {
                order: 3 !important;
            }
            /* Country takes full row */
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-billing_address_country),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-shipping_address_country),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-primary_address_country),
            scrm-group-field .field-group .field-group-item:has(.dynamic-field-name-alt_address_country) {
                order: 4 !important;
                flex: 0 0 100% !important;
                width: 100% !important;
            }
        `;
        document.head.appendChild(style);
        console.log('LF: Address CSS injected');
    }

    // Inject CSS immediately
    if (document.head) {
        injectAddressCSS();
    } else {
        document.addEventListener('DOMContentLoaded', injectAddressCSS);
    }

    const stateAbbreviations = {
        'Alabama': 'AL',
        'Alaska': 'AK',
        'Arizona': 'AZ',
        'Arkansas': 'AR',
        'California': 'CA',
        'Colorado': 'CO',
        'Connecticut': 'CT',
        'Delaware': 'DE',
        'Florida': 'FL',
        'Georgia': 'GA',
        'Hawaii': 'HI',
        'Idaho': 'ID',
        'Illinois': 'IL',
        'Indiana': 'IN',
        'Iowa': 'IA',
        'Kansas': 'KS',
        'Kentucky': 'KY',
        'Louisiana': 'LA',
        'Maine': 'ME',
        'Maryland': 'MD',
        'Massachusetts': 'MA',
        'Michigan': 'MI',
        'Minnesota': 'MN',
        'Mississippi': 'MS',
        'Missouri': 'MO',
        'Montana': 'MT',
        'Nebraska': 'NE',
        'Nevada': 'NV',
        'New Hampshire': 'NH',
        'New Jersey': 'NJ',
        'New Mexico': 'NM',
        'New York': 'NY',
        'North Carolina': 'NC',
        'North Dakota': 'ND',
        'Ohio': 'OH',
        'Oklahoma': 'OK',
        'Oregon': 'OR',
        'Pennsylvania': 'PA',
        'Rhode Island': 'RI',
        'South Carolina': 'SC',
        'South Dakota': 'SD',
        'Tennessee': 'TN',
        'Texas': 'TX',
        'Utah': 'UT',
        'Vermont': 'VT',
        'Virginia': 'VA',
        'Washington': 'WA',
        'West Virginia': 'WV',
        'Wisconsin': 'WI',
        'Wyoming': 'WY',
        // Territories
        'District of Columbia': 'DC',
        'Puerto Rico': 'PR',
        'Guam': 'GU',
        'American Samoa': 'AS',
        'U.S. Virgin Islands': 'VI',
        'Northern Mariana Islands': 'MP'
    };

    function abbreviateState(text) {
        if (!text) return text;
        const trimmed = text.trim();
        return stateAbbreviations[trimmed] || trimmed;
    }

    function processStateFields() {
        // Find all state fields in detail view (Account and Contact addresses)
        const stateFields = document.querySelectorAll(
            '.dynamic-field-name-billing_address_state, ' +
            '.dynamic-field-name-shipping_address_state, ' +
            '.dynamic-field-name-primary_address_state, ' +
            '.dynamic-field-name-alt_address_state'
        );

        stateFields.forEach(field => {
            // Find the text content element (usually scrm-field-value or similar)
            const valueElements = field.querySelectorAll('scrm-field-value, .field-value, span:not(.label)');
            valueElements.forEach(el => {
                // Only process text nodes, not nested elements
                el.childNodes.forEach(node => {
                    if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
                        const original = node.textContent.trim();
                        const abbreviated = abbreviateState(original);
                        if (abbreviated !== original) {
                            node.textContent = abbreviated;
                        }
                    }
                });
            });

            // Also check direct text content
            const directText = field.textContent.trim();
            if (stateAbbreviations[directText]) {
                // Find the deepest element containing only this text
                const walker = document.createTreeWalker(
                    field,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );
                let node;
                while (node = walker.nextNode()) {
                    if (node.textContent.trim() === directText) {
                        node.textContent = stateAbbreviations[directText];
                        break;
                    }
                }
            }
        });
    }

    // Run on page load and after Angular updates
    function init() {
        // Initial processing
        processStateFields();

        // Watch for DOM changes (Angular updates)
        const observer = new MutationObserver((mutations) => {
            let shouldProcess = false;
            mutations.forEach(mutation => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.classList?.contains('dynamic-field-name-billing_address_state') ||
                                node.classList?.contains('dynamic-field-name-shipping_address_state') ||
                                node.classList?.contains('dynamic-field-name-primary_address_state') ||
                                node.classList?.contains('dynamic-field-name-alt_address_state') ||
                                node.querySelector?.('.dynamic-field-name-billing_address_state, .dynamic-field-name-shipping_address_state, .dynamic-field-name-primary_address_state, .dynamic-field-name-alt_address_state')) {
                                shouldProcess = true;
                            }
                        }
                    });
                }
            });
            if (shouldProcess) {
                setTimeout(processStateFields, 100);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Also run periodically to catch any missed updates
    setInterval(processStateFields, 2000);

    // ========================================
    // ADDRESS LAYOUT FIX
    // Applies inline styles for address field layout
    // Works for both Account and Contact addresses
    // ========================================

    const addressFieldConfigs = [
        // Billing Address (Account)
        { name: 'billing_address_street', order: 0, flex: '0 0 100%', width: '100%' },
        { name: 'billing_address_city', order: 1, flex: '0 0 auto', width: 'auto' },
        { name: 'billing_address_state', order: 2, flex: '0 0 auto', width: 'auto' },
        { name: 'billing_address_postalcode', order: 3, flex: '0 0 auto', width: 'auto' },
        { name: 'billing_address_country', order: 4, flex: '0 0 100%', width: '100%' },
        // Shipping Address (Account)
        { name: 'shipping_address_street', order: 0, flex: '0 0 100%', width: '100%' },
        { name: 'shipping_address_city', order: 1, flex: '0 0 auto', width: 'auto' },
        { name: 'shipping_address_state', order: 2, flex: '0 0 auto', width: 'auto' },
        { name: 'shipping_address_postalcode', order: 3, flex: '0 0 auto', width: 'auto' },
        { name: 'shipping_address_country', order: 4, flex: '0 0 100%', width: '100%' },
        // Primary Address (Contact)
        { name: 'primary_address_street', order: 0, flex: '0 0 100%', width: '100%' },
        { name: 'primary_address_city', order: 1, flex: '0 0 auto', width: 'auto' },
        { name: 'primary_address_state', order: 2, flex: '0 0 auto', width: 'auto' },
        { name: 'primary_address_postalcode', order: 3, flex: '0 0 auto', width: 'auto' },
        { name: 'primary_address_country', order: 4, flex: '0 0 100%', width: '100%' },
        // Other/Alt Address (Contact)
        { name: 'alt_address_street', order: 0, flex: '0 0 100%', width: '100%' },
        { name: 'alt_address_city', order: 1, flex: '0 0 auto', width: 'auto' },
        { name: 'alt_address_state', order: 2, flex: '0 0 auto', width: 'auto' },
        { name: 'alt_address_postalcode', order: 3, flex: '0 0 auto', width: 'auto' },
        { name: 'alt_address_country', order: 4, flex: '0 0 100%', width: '100%' }
    ];

    function applyAddressLayout() {
        const foundFields = [];
        addressFieldConfigs.forEach(config => {
            const dynamicField = document.querySelector('.dynamic-field-name-' + config.name);
            if (!dynamicField) return;

            // Walk up to find the field-group-item
            let current = dynamicField;
            let fieldGroupItem = null;
            while (current) {
                if (current.classList && current.classList.contains('field-group-item')) {
                    fieldGroupItem = current;
                    break;
                }
                current = current.parentElement;
            }

            if (fieldGroupItem) {
                fieldGroupItem.style.setProperty('order', config.order, 'important');
                fieldGroupItem.style.setProperty('flex', config.flex, 'important');
                fieldGroupItem.style.setProperty('width', config.width, 'important');
                foundFields.push(config.name);
            }
        });
        if (foundFields.length > 0) {
            console.log('LF: Applied address layout to:', foundFields);
        }

        // Also apply styles to the parent field-group containers
        document.querySelectorAll('scrm-group-field .field-group.flex-column').forEach(fieldGroup => {
            // Check if this contains address fields
            if (fieldGroup.querySelector('[class*="dynamic-field-name-"][class*="_address_"]')) {
                fieldGroup.style.setProperty('flex-direction', 'row', 'important');
                fieldGroup.style.setProperty('flex-wrap', 'wrap', 'important');
                fieldGroup.style.setProperty('gap', '2px 4px', 'important');
                fieldGroup.style.setProperty('align-items', 'baseline', 'important');
            }
        });
    }

    // Run address layout fix on init and periodically
    function initAddressLayout() {
        applyAddressLayout();

        // Watch for DOM changes
        const addressObserver = new MutationObserver((mutations) => {
            let shouldApply = false;
            mutations.forEach(mutation => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.querySelector?.('[class*="dynamic-field-name-"][class*="_address_"]') ||
                                (node.className && typeof node.className === 'string' &&
                                 node.className.includes('dynamic-field-name-') &&
                                 node.className.includes('_address_'))) {
                                shouldApply = true;
                            }
                        }
                    });
                }
            });
            if (shouldApply) {
                setTimeout(applyAddressLayout, 100);
            }
        });

        addressObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Initialize address layout on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAddressLayout);
    } else {
        initAddressLayout();
    }

    // Also run periodically to catch any missed updates
    setInterval(applyAddressLayout, 2000);

})();
