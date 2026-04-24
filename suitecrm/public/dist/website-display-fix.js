(function () {
    var BARE_SCHEMES = /^https?:\/\/?$/i;

    function fixWebsiteFields() {
        // Hide URL field anchors that contain only a bare scheme (http:// etc.)
        var anchors = document.querySelectorAll('scrm-url-detail-field a, [class*="url"] a');
        anchors.forEach(function (a) {
            var href = (a.href || '').trim();
            var text = (a.textContent || '').trim();
            if (BARE_SCHEMES.test(href) || BARE_SCHEMES.test(text)) {
                var cell = a.closest('.detail-field-value, td, .field-value, scrm-url-detail-field');
                if (cell && !cell.dataset.lfWebsiteFixed) {
                    cell.dataset.lfWebsiteFixed = '1';
                    a.style.display = 'none';
                }
            }
        });
    }

    var observer = new MutationObserver(function () { fixWebsiteFields(); });

    function init() {
        observer.observe(document.body, { childList: true, subtree: true });
        fixWebsiteFields();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
