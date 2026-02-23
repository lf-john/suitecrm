/* 093 - Footer Link to Logical Front */
(function() {
    function setupFooterLink() {
        var footer = document.querySelector('footer, .footer, #footer, scrm-footer');
        if (footer) {
            footer.style.cursor = 'pointer';
            footer.addEventListener('click', function(e) {
                // Don't trigger if clicking "Back to Top" or other links
                if (e.target.tagName !== 'A') {
                    window.open('https://www.logicalfront.com', '_blank');
                }
            });
        }
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupFooterLink);
    } else {
        setupFooterLink();
    }

    // Also run after Angular renders
    setTimeout(setupFooterLink, 2000);
})();
