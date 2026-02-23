<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Admin-only menu for Weekly Plan Management
global $current_user;

// Only show menu items to admin users
if (!empty($current_user) && $current_user->is_admin) {
    $module_menu = [
        ['index.php?module=LF_PRConfig&action=config', 'Config', 'LF_PRConfig', 'config'],
        ['index.php?module=LF_RepTargets&action=manage', 'Rep Targets', 'LF_RepTargets', 'manage'],
    ];
} else {
    $module_menu = [];
}
