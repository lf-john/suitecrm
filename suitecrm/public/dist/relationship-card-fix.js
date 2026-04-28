(function () {
    function processGridWidget() {
        var rows = document.querySelectorAll(
            'scrm-grid-widget .statistics-sidebar-widget-row:not([data-lf-rel-processed])'
        );
        rows.forEach(function (row) {
            var intDetail = row.querySelector('scrm-int-detail');
            if (!intDetail) return;

            var text = intDetail.textContent.trim();
            // Skip if Angular hasn't populated the value yet
            if (text === '' || text === '\n') return;

            // Value is present — mark processed and apply class
            row.setAttribute('data-lf-rel-processed', '1');

            var count = parseInt(text, 10);
            if (isNaN(count) || count === 0) {
                row.classList.add('lf-zero-count');
            } else {
                row.classList.add('lf-has-count');
            }

            // Add tooltip for truncated label
            var labelEl = row.querySelector('.widget-entry-label');
            if (labelEl) {
                var scrmLabel = labelEl.querySelector('scrm-label');
                if (scrmLabel) {
                    labelEl.title = scrmLabel.textContent.trim();
                }
            }
        });
    }

    var observer = new MutationObserver(function () {
        processGridWidget();
    });

    function init() {
        observer.observe(document.body, { childList: true, subtree: true });
        processGridWidget();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
