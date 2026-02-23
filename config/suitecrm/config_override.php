<?php
/**
 * SuiteCRM v8 Configuration Override
 * This file contains custom configuration settings for SuiteCRM v8
 * based on the analysis of your v7 setup
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Database Configuration
$sugar_config['dbconfig']['db_host_name'] = 'db';
$sugar_config['dbconfig']['db_host_instance'] = '';
$sugar_config['dbconfig']['db_user_name'] = 'suitecrm_user';
$sugar_config['dbconfig']['db_password'] = 'suitecrm_password';
$sugar_config['dbconfig']['db_name'] = 'suitecrm_db';
$sugar_config['dbconfig']['db_type'] = 'mysql';
$sugar_config['dbconfig']['db_port'] = '3306';
$sugar_config['dbconfig']['db_manager'] = 'MysqliManager';

// Database Options (migrated from v7 config)
$sugar_config['dbconfigoption']['persistent'] = true;
$sugar_config['dbconfigoption']['autofree'] = false;
$sugar_config['dbconfigoption']['debug'] = 0;
$sugar_config['dbconfigoption']['ssl'] = false;
$sugar_config['dbconfigoption']['collation'] = 'utf8mb4_unicode_ci';

// Site Configuration
$sugar_config['site_url'] = 'http://localhost:8080';
$sugar_config['host_name'] = 'localhost';
$sugar_config['unique_key'] = 'suitecrm-v8-docker-migration-' . date('Y-m-d');

// Cache Configuration (Redis)
$sugar_config['external_cache_disabled'] = false;
$sugar_config['external_cache_disabled_redis'] = false;
$sugar_config['cache_expire_timeout'] = 300;
$sugar_config['cache_class'] = 'SugarCacheRedis';
$sugar_config['cache_redis_host'] = 'redis';
$sugar_config['cache_redis_port'] = 6379;

// Session Configuration (Redis)
$sugar_config['session_handler'] = 'redis';
$sugar_config['session_redis_host'] = 'redis';
$sugar_config['session_redis_port'] = 6379;

// Upload and File Configuration (matching v7 setup)
$sugar_config['upload_maxsize'] = 30000000; // 30MB
$sugar_config['upload_dir'] = 'upload/';
$sugar_config['upload_badext'] = [
    'php', 'php3', 'php4', 'php5', 'pl', 'cgi', 'py', 'asp', 'cfm', 
    'js', 'vbs', 'html', 'htm', 'phtml'
];

// Logger Configuration
$sugar_config['logger']['level'] = 'fatal';
$sugar_config['logger']['file']['ext'] = '.log';
$sugar_config['logger']['file']['name'] = 'suitecrm';
$sugar_config['logger']['file']['dateFormat'] = '%c';
$sugar_config['logger']['file']['maxSize'] = '10MB';
$sugar_config['logger']['file']['maxLogs'] = 10;
$sugar_config['logger']['file']['suffix'] = '';
$sugar_config['log_memory_usage'] = false;
$sugar_config['log_dir'] = './logs/';
$sugar_config['log_file'] = 'suitecrm.log';

// Performance Configuration (improved from v7)
$sugar_config['list_max_entries_per_page'] = 20;
$sugar_config['list_max_entries_per_subpanel'] = 10;
$sugar_config['history_max_viewed'] = 50;
$sugar_config['max_dashlets_homepage'] = '15';

// Import/Export Configuration (from v7 analysis)
$sugar_config['import_max_execution_time'] = 3600;
$sugar_config['import_max_records_per_file'] = 100;
$sugar_config['export_delimiter'] = ',';
$sugar_config['export_excel_compatible'] = false;

// Email Configuration
$sugar_config['email_default_client'] = 'sugar';
$sugar_config['email_default_editor'] = 'html';
$sugar_config['email_default_charset'] = 'UTF-8';
$sugar_config['email_allow_send_as_user'] = true;
$sugar_config['email_default_delete_attachments'] = true;

// Localization (from v7 analysis)
$sugar_config['default_language'] = 'en_us';
$sugar_config['default_locale_name_format'] = 's f l';
$sugar_config['default_currency_iso4217'] = 'USD';
$sugar_config['default_currency_name'] = 'US Dollar';
$sugar_config['default_currency_symbol'] = '$';
$sugar_config['default_currency_significant_digits'] = '2';
$sugar_config['default_date_format'] = 'm/d/Y';
$sugar_config['default_time_format'] = 'H:i';
$sugar_config['default_decimal_seperator'] = '.';
$sugar_config['default_number_grouping_seperator'] = ',';

// Theme Configuration (matching v7 default)
$sugar_config['default_theme'] = 'suite8';
$sugar_config['default_module'] = 'Home';

// Security Configuration
$sugar_config['verify_client_ip'] = true;
$sugar_config['use_real_names'] = true;
$sugar_config['disable_export'] = false;
$sugar_config['disable_convert_lead'] = false;
$sugar_config['admin_export_only'] = false;

// Developer Mode (disabled for production)
$sugar_config['developerMode'] = false;
$sugar_config['stack_trace_errors'] = false;
$sugar_config['dump_slow_queries'] = false;
$sugar_config['slow_query_time_msec'] = '100';

// Legacy Support for Migration
$sugar_config['legacy_enabled'] = true;
$sugar_config['legacy_url_routing'] = true;

// Module Configuration (prepared for partner modules)
$sugar_config['moduleInstaller']['packageScan'] = false;

// Calendar Configuration (from v7 analysis)
$sugar_config['calendar']['default_view'] = 'week';
$sugar_config['calendar']['show_calls_by_default'] = true;
$sugar_config['calendar']['show_tasks_by_default'] = true;
$sugar_config['calendar']['show_completed_by_default'] = true;
$sugar_config['calendar']['editview_width'] = 990;
$sugar_config['calendar']['editview_height'] = 485;
$sugar_config['calendar']['day_timestep'] = 15;
$sugar_config['calendar']['week_timestep'] = 30;
$sugar_config['calendar']['items_draggable'] = true;
$sugar_config['calendar']['items_resizable'] = true;
$sugar_config['calendar']['enable_repeat'] = true;
$sugar_config['calendar']['max_repeat_count'] = 1000;

// Resource Management (from v7 analysis)
$sugar_config['resource_management']['special_query_limit'] = 50000;
$sugar_config['resource_management']['special_query_modules'] = [
    'Reports', 'Export', 'Import', 'Administration', 'Sync'
];
$sugar_config['resource_management']['default_limit'] = 20000;

// Search Configuration
$sugar_config['search']['controller'] = 'UnifiedSearch';
$sugar_config['search']['defaultEngine'] = 'BasicSearchEngine';
$sugar_config['search']['pagination']['min'] = 10;
$sugar_config['search']['pagination']['max'] = 50;
$sugar_config['search']['pagination']['step'] = 10;

// Elasticsearch Configuration
$sugar_config['search']['ElasticSearch']['enabled'] = true;
$sugar_config['search']['ElasticSearch']['host'] = 'elasticsearch';
$sugar_config['search']['ElasticSearch']['port'] = 9200;

// Jobs and Cron Configuration (from v7 analysis)
$sugar_config['cron']['max_cron_jobs'] = 10;
$sugar_config['cron']['max_cron_runtime'] = 30;
$sugar_config['cron']['min_cron_interval'] = 30;
$sugar_config['cron']['allowed_cron_users'] = ['daemon'];

$sugar_config['jobs']['min_retry_interval'] = 30;
$sugar_config['jobs']['max_retries'] = 5;
$sugar_config['jobs']['timeout'] = 86400;

// AOS Configuration (for migration compatibility)
$sugar_config['aos']['version'] = '8.0.0';
$sugar_config['aos']['contracts']['renewalReminderPeriod'] = '14';
$sugar_config['aos']['lineItems']['totalTax'] = false;
$sugar_config['aos']['lineItems']['enableGroups'] = false;
$sugar_config['aos']['invoices']['initialNumber'] = '1';
$sugar_config['aos']['quotes']['initialNumber'] = '1';

// Security Suite Configuration (for migration)
$sugar_config['securitysuite_additive'] = true;
$sugar_config['securitysuite_filter_user_list'] = false;
$sugar_config['securitysuite_inherit_assigned'] = true;
$sugar_config['securitysuite_inherit_creator'] = true;
$sugar_config['securitysuite_inherit_parent'] = true;
$sugar_config['securitysuite_popup_select'] = false;
$sugar_config['securitysuite_strict_rights'] = false;
$sugar_config['securitysuite_user_popup'] = true;
$sugar_config['securitysuite_user_role_precedence'] = true;

// Custom Configuration for Migration
$sugar_config['migration'] = [
    'v7_source_enabled' => true,
    'partner_modules_enabled' => true,
    'custom_fields_mapping' => true,
    'relationship_migration' => true,
    'preserve_custom_code' => false, // Will need manual migration
];

// Installer Configuration
$sugar_config['installer_locked'] = true;
$sugar_config['sugarbeet'] = false;
