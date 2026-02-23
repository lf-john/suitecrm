<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_menu = array(
    array('index.php?module=LF_WeeklyReport&action=reporting', 'Reporting Tool', 'icon_LF_WeeklyReport'),
    array('index.php?module=LF_WeeklyReport&action=dashboard', 'Dashboard', 'icon_LF_WeeklyReport'),
);
