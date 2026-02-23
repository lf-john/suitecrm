<?php
$sugar_config['cron']['allowed_cron_users'] = array('www-data', 'root');

// Disable Line Item Groups requirement in Quotes
$sugar_config['aos']['lineItems']['enableGroups'] = false;

// Configure classic view for AOS modules (not fully supported in Angular UI)
$sugar_config['classic_view_modules'] = array(
    'AOS_Quotes',
    'AOS_Contracts',
    'AOS_Invoices',
    'AOS_Products',
    'AOS_Product_Categories',
    'AOS_PDF_Templates',
    'AOR_Reports',
    'AOW_WorkFlow',
    'AM_ProjectTemplates'
);

// Allow crm2.logicalfront.com as a valid referer for XSRF protection
$sugar_config['http_referer']['list'] = array(
    'crm2.logicalfront.com',
    'crm.logicalfront.com',
    'localhost',
    '127.0.0.1'
);

// Debug - enable error display
$sugar_config['logger']['level'] = 'debug';
$sugar_config['display_inbound_email_buttons'] = true;
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Suppress PHP deprecation notices (Smarty file_exists compatibility)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
