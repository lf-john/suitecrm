/* 094 - Footer Replacement Script
   Replaces SuiteCRM/SugarCRM branding with Logical Front
   Runs repeatedly until footer is found and modified */
(function() {
    var footerFixed = false;
    var attempts = 0;
    var maxAttempts = 100;

    function fixFooter() {
        if (footerFixed || attempts >= maxAttempts) return;
        attempts++;

        // Find footer element
        var footer = document.querySelector('footer, .footer, #footer, scrm-footer, .app-footer');
        if (!footer) {
            setTimeout(fixFooter, 200);
            return;
        }

        // Find and replace text content
        var footerText = footer.innerHTML;
        if (footerText.indexOf('SuiteCRM') !== -1 || footerText.indexOf('SugarCRM') !== -1) {
            // Clear existing content and add our branding
            footer.innerHTML = '<a href="https://www.logicalfront.com" target="_blank" style="color: #ffffff; text-decoration: none; font-size: 13px;">&copy; Powered by Logical Front</a>';
            footerFixed = true;
            console.log('Footer replaced with Logical Front branding');
        } else {
            // Keep trying until Angular renders the footer
            setTimeout(fixFooter, 200);
        }
    }

    // Start checking
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixFooter);
    } else {
        fixFooter();
    }

    // Also watch for DOM changes
    var observer = new MutationObserver(function(mutations) {
        if (!footerFixed) {
            fixFooter();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Stop observer after footer is fixed or after 30 seconds
    setTimeout(function() {
        observer.disconnect();
    }, 30000);
})();
