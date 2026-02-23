/* 288 - Footer completely disabled - using CSS body::after instead */
(function() {
    'use strict';

    // Remove any Angular or existing footer elements
    function removeFooters() {
        var selectors = [
            'scrm-footer',
            'scrm-footer-ui',
            '#lf-static-footer',
            '#lf-footer-white-bar'
        ];

        selectors.forEach(function(sel) {
            var elements = document.querySelectorAll(sel);
            elements.forEach(function(el) {
                el.remove();
            });
        });
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', removeFooters);
    } else {
        removeFooters();
    }

    // Keep checking
    setInterval(removeFooters, 1000);

    // Observe for new elements
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(removeFooters);

        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                observer.observe(document.body, { childList: true, subtree: true });
            });
        }
    }
})();
