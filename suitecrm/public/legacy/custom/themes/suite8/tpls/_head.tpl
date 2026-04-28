{*
/**
 * Custom _head.tpl - Logical Front Theme
 * Adds logical-front-theme.css for legacy pages (Quotes, Reports, etc.)
 */
*}
<!DOCTYPE html>
<html {$langHeader}>
<head>
    <link rel="SHORTCUT ICON" href="{$FAVICON_URL}">
    <meta http-equiv="Content-Type" content="text/html; charset={$APP.LBL_CHARSET}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1" />
    <!-- Bootstrap -->
    <link href="themes/suite8/css/normalize.css" rel="stylesheet" type="text/css"/>
    <link href='themes/suite8/css/fonts.css' rel='stylesheet' type='text/css'>
    <link href="themes/suite8/css/grid.css" rel="stylesheet" type="text/css"/>
    <link href="themes/suite8/css/footable.core.css" rel="stylesheet" type="text/css"/>
    <!-- Logical Front Theme -->
    <link href="themes/suite8/css/logical-front-theme.css?v=1772754631" rel="stylesheet" type="text/css"/>
    <title>{if $BROWSER_TITLE}{$BROWSER_TITLE}{else}{$APP.LBL_BROWSER_TITLE}{/if}</title>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    {$SUGAR_JS}
    {literal}
    <script type="text/javascript">
        <!--
        SUGAR.themes.theme_name = '{/literal}{$THEME}{literal}';
        SUGAR.themes.theme_ie6compat = '{/literal}{$THEME_IE6COMPAT}{literal}';
        SUGAR.themes.hide_image = '{/literal}{sugar_getimagepath file="hide.gif"}{literal}';
        SUGAR.themes.show_image = '{/literal}{sugar_getimagepath file="show.gif"}{literal}';
        SUGAR.themes.loading_image = '{/literal}{sugar_getimagepath file="img_loading.gif"}{literal}';

        if (YAHOO.env.ua)
            UA = YAHOO.env.ua;
        -->
    </script>
    {/literal}
    {$SUGAR_CSS}
    <link rel="stylesheet" type="text/css" href="themes/suite8/css/colourSelector.php">
    <script type="text/javascript" src='{sugar_getjspath file="themes/suite8/js/jscolor.js"}'></script>
    <script type="text/javascript" src='{sugar_getjspath file="cache/include/javascript/sugar_field_grp.js"}'></script>
    <script type="text/javascript" src='{sugar_getjspath file="vendor/tinymce/tinymce/tinymce.min.js"}'></script>
    {literal}
    <script type="text/javascript">
    /* Fix #8: Patch markFieldLineDeleted & markConditionLineDeleted to use !important
       The global CSS rule 'tbody tr { display: table-row !important }' prevents the
       inline style.display='none' from working. This patch uses setProperty with !important. */
    document.addEventListener('DOMContentLoaded', function() {
        /* Fix: dashlet header tr white background — CSS (2,2,2) specificity blocks rules;
           two tbody ancestors make :not() exclusion ineffective; use inline style instead */
        function fixDashletHeaderRows() {
            document.querySelectorAll('.hd.dashlet tr').forEach(function(tr) {
                tr.style.setProperty('background-color', 'transparent', 'important');
                tr.style.setProperty('background', 'transparent', 'important');
            });
        }
        fixDashletHeaderRows();
        var dashletObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.addedNodes.length) fixDashletHeaderRows();
            });
        });
        dashletObserver.observe(document.body || document.documentElement, { childList: true, subtree: true });

        var patchInterval = setInterval(function() {
            if (typeof markFieldLineDeleted === 'function' && !markFieldLineDeleted._lfPatched) {
                var _origField = markFieldLineDeleted;
                markFieldLineDeleted = function(ln) {
                    _origField(ln);
                    var row = document.getElementById('field_line' + ln);
                    if (row) row.style.setProperty('display', 'none', 'important');
                };
                markFieldLineDeleted._lfPatched = true;
            }
            if (typeof markConditionLineDeleted === 'function' && !markConditionLineDeleted._lfPatched) {
                var _origCond = markConditionLineDeleted;
                markConditionLineDeleted = function(ln) {
                    _origCond(ln);
                    /* Row ID is 'product_line' + ln (from conditionLines.js insertConditionLine) */
                    var row = document.getElementById('product_line' + ln);
                    if (row) row.style.setProperty('display', 'none', 'important');
                };
                markConditionLineDeleted._lfPatched = true;
            }
            /* Stop polling once both are patched or after 30 seconds */
            if ((typeof markFieldLineDeleted !== 'undefined' && markFieldLineDeleted._lfPatched) ||
                patchInterval._elapsed > 30000) {
                clearInterval(patchInterval);
            }
            patchInterval._elapsed = (patchInterval._elapsed || 0) + 500;
        }, 500);
    });
    </script>
    {/literal}
</head>
