<?php
/**
 * LF Planning & Reporting - Install Script
 *
 * Creates all 7 module tables and inserts default configuration values.
 * This script is idempotent: safe to run multiple times without data loss.
 *
 * Usage: php custom/modules/LF_PRConfig/install.php
 */

// SuiteCRM bootstrap - define sugarEntry to pass entry point checks
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

// Change to SuiteCRM legacy directory
chdir('/var/www/html/public/legacy');

// Bootstrap SuiteCRM
require_once 'include/entryPoint.php';

// Access the global database object
global $db;

$hasError = false;
$now = gmdate('Y-m-d H:i:s');

// ============================================================
// Table 1: lf_weekly_plan
// ============================================================
$sql = "CREATE TABLE IF NOT EXISTS lf_weekly_plan (
    id char(36) NOT NULL,
    name varchar(255),
    date_entered datetime,
    date_modified datetime,
    modified_user_id char(36),
    created_by char(36),
    deleted tinyint(1) DEFAULT 0,
    assigned_user_id char(36) NOT NULL,
    week_start_date date NOT NULL,
    status varchar(100) DEFAULT 'in_progress',
    submitted_date datetime,
    reviewed_by char(36),
    reviewed_date datetime,
    notes text,
    PRIMARY KEY (id),
    UNIQUE INDEX idx_assigned_user_week_start (assigned_user_id, week_start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$result = $db->query($sql, false);
if ($result) {
    echo "[OK] Table lf_weekly_plan created successfully.\n";
} else {
    echo "[ERROR] Failed to create table lf_weekly_plan.\n";
    $hasError = true;
}

// ============================================================
// Table 2: lf_plan_op_items
// ============================================================
$sql = "CREATE TABLE IF NOT EXISTS lf_plan_op_items (
    id char(36) NOT NULL,
    name varchar(255),
    date_entered datetime,
    date_modified datetime,
    modified_user_id char(36),
    created_by char(36),
    deleted tinyint(1) DEFAULT 0,
    lf_weekly_plan_id char(36) NOT NULL,
    opportunity_id char(36) NOT NULL,
    item_type varchar(100),
    projected_stage varchar(100),
    planned_day varchar(100),
    plan_description text,
    is_at_risk tinyint(1) DEFAULT 0,
    PRIMARY KEY (id),
    INDEX idx_lf_weekly_plan_id (lf_weekly_plan_id),
    INDEX idx_opportunity_id (opportunity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$result = $db->query($sql, false);
if ($result) {
    echo "[OK] Table lf_plan_op_items created successfully.\n";
} else {
    echo "[ERROR] Failed to create table lf_plan_op_items.\n";
    $hasError = true;
}

// ============================================================
// Table 3: lf_plan_prospect_items
// ============================================================
$sql = "CREATE TABLE IF NOT EXISTS lf_plan_prospect_items (
    id char(36) NOT NULL,
    name varchar(255),
    date_entered datetime,
    date_modified datetime,
    modified_user_id char(36),
    created_by char(36),
    deleted tinyint(1) DEFAULT 0,
    lf_weekly_plan_id char(36) NOT NULL,
    source_type varchar(100),
    planned_day varchar(100),
    expected_value decimal(26,6),
    plan_description text,
    status varchar(100) DEFAULT 'planned',
    converted_opportunity_id char(36),
    prospecting_notes text,
    PRIMARY KEY (id),
    INDEX idx_lf_weekly_plan_id (lf_weekly_plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$result = $db->query($sql, false);
if ($result) {
    echo "[OK] Table lf_plan_prospect_items created successfully.\n";
} else {
    echo "[ERROR] Failed to create table lf_plan_prospect_items.\n";
    $hasError = true;
}

// ============================================================
// Table 4: lf_weekly_report
// ============================================================
$sql = "CREATE TABLE IF NOT EXISTS lf_weekly_report (
    id char(36) NOT NULL,
    name varchar(255),
    date_entered datetime,
    date_modified datetime,
    modified_user_id char(36),
    created_by char(36),
    deleted tinyint(1) DEFAULT 0,
    lf_weekly_plan_id char(36),
    assigned_user_id char(36) NOT NULL,
    week_start_date date NOT NULL,
    status varchar(100) DEFAULT 'in_progress',
    submitted_date datetime,
    reviewed_by char(36),
    reviewed_date datetime,
    notes text,
    PRIMARY KEY (id),
    UNIQUE INDEX idx_assigned_user_week_start (assigned_user_id, week_start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$result = $db->query($sql, false);
if ($result) {
    echo "[OK] Table lf_weekly_report created successfully.\n";
} else {
    echo "[ERROR] Failed to create table lf_weekly_report.\n";
    $hasError = true;
}

// ============================================================
// Table 5: lf_report_snapshots
// ============================================================
$sql = "CREATE TABLE IF NOT EXISTS lf_report_snapshots (
    id char(36) NOT NULL,
    name varchar(255),
    date_entered datetime,
    date_modified datetime,
    modified_user_id char(36),
    created_by char(36),
    deleted tinyint(1) DEFAULT 0,
    lf_weekly_report_id char(36) NOT NULL,
    opportunity_id char(36) NOT NULL,
    account_name varchar(255),
    opportunity_name varchar(255),
    amount_at_snapshot decimal(26,6),
    stage_at_week_start varchar(100),
    stage_at_week_end varchar(100),
    probability_at_start int,
    probability_at_end int,
    movement varchar(100),
    was_planned tinyint(1) DEFAULT 0,
    plan_category varchar(50),
    result_description text,
    PRIMARY KEY (id),
    INDEX idx_lf_weekly_report_id (lf_weekly_report_id),
    INDEX idx_opportunity_id (opportunity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$result = $db->query($sql, false);
if ($result) {
    echo "[OK] Table lf_report_snapshots created successfully.\n";
} else {
    echo "[ERROR] Failed to create table lf_report_snapshots.\n";
    $hasError = true;
}

// ============================================================
// Table 6: lf_pr_config
// ============================================================
$sql = "CREATE TABLE IF NOT EXISTS lf_pr_config (
    id char(36) NOT NULL,
    name varchar(255),
    date_entered datetime,
    date_modified datetime,
    modified_user_id char(36),
    created_by char(36),
    deleted tinyint(1) DEFAULT 0,
    category varchar(50) NOT NULL,
    config_name varchar(100) NOT NULL,
    `value` text,
    description varchar(255),
    PRIMARY KEY (id),
    UNIQUE INDEX idx_category_config_name (category, config_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$result = $db->query($sql, false);
if ($result) {
    echo "[OK] Table lf_pr_config created successfully.\n";
} else {
    echo "[ERROR] Failed to create table lf_pr_config.\n";
    $hasError = true;
}

// ============================================================
// Table 7: lf_rep_targets
// ============================================================
$sql = "CREATE TABLE IF NOT EXISTS lf_rep_targets (
    id char(36) NOT NULL,
    name varchar(255),
    date_entered datetime,
    date_modified datetime,
    modified_user_id char(36),
    created_by char(36),
    deleted tinyint(1) DEFAULT 0,
    assigned_user_id char(36) NOT NULL,
    fiscal_year int NOT NULL,
    annual_quota decimal(26,6),
    weekly_new_pipeline decimal(26,6),
    weekly_progression decimal(26,6),
    weekly_closed decimal(26,6),
    is_active tinyint(1) DEFAULT 1,
    PRIMARY KEY (id),
    INDEX idx_assigned_user_fiscal_year (assigned_user_id, fiscal_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$result = $db->query($sql, false);
if ($result) {
    echo "[OK] Table lf_rep_targets created successfully.\n";
} else {
    echo "[ERROR] Failed to create table lf_rep_targets.\n";
    $hasError = true;
}

// ============================================================
// Default Config Entries (19 entries)
// Uses INSERT IGNORE for idempotency - will not overwrite existing values
// ============================================================
echo "\nInserting default configuration values...\n";

$defaultConfigEntries = array(
    array('quotas', 'default_annual_quota', '500000', 'Annual quota per rep ($)'),
    array('quotas', 'pipeline_coverage_multiplier', '4', 'Pipeline coverage ratio'),
    array('quotas', 'fiscal_year_start_month', '1', 'January'),
    array('targets', 'default_new_pipeline_target', '100000', 'Weekly new pipeline target ($)'),
    array('targets', 'default_progression_target', '100000', 'Weekly progression target ($)'),
    array('targets', 'default_closed_target', '20000', 'Weekly closed target ($)'),
    array('display', 'achievement_tier_green', '76', 'Green threshold %'),
    array('display', 'achievement_tier_yellow', '51', 'Yellow threshold %'),
    array('display', 'achievement_tier_orange', '26', 'Orange threshold %'),
    array('weeks', 'week_start_day', '5', '5=Friday'),
    array('weeks', 'weeks_to_show', '12', 'Weeks in selector'),
    array('stages', 'stage_order', '["2-Analysis (1%)","3-Confirmation (10%)","5-Specifications (30%)","6-Solution (60%)","7-Closing (90%)","closed_won"]', 'Stage order'),
    array('stages', 'stage_probabilities', '{"2-Analysis (1%)":1,"3-Confirmation (10%)":10,"5-Specifications (30%)":30,"6-Solution (60%)":60,"7-Closing (90%)":90,"closed_won":100}', 'Stage to probability mapping'),
    array('stages', 'pipeline_stages', '["3-Confirmation (10%)","5-Specifications (30%)","6-Solution (60%)","7-Closing (90%)"]', 'Stages that count as pipeline'),
    array('stages', 'analysis_stage', '2-Analysis (1%)', 'Stage excluded from pipeline'),
    array('prospecting', 'source_types', '["Cold Call","Referral","Event","Partner","Inbound","Customer Visit","Other"]', 'Source options'),
    array('prospecting', 'default_conversion_stage', '3-Confirmation (10%)', 'Default stage when converting'),
    array('risk', 'stale_deal_days', '14', 'Days before flagging'),
    array('risk', 'activity_types', '["Calls","Meetings","Tasks","Notes","Emails"]', 'Activity types'),
);

foreach ($defaultConfigEntries as $entry) {
    $id = create_guid();
    $category = $entry[0];
    $configName = $entry[1];
    $value = $entry[2];
    $description = $entry[3];

    $sql = "INSERT IGNORE INTO lf_pr_config (id, name, date_entered, date_modified, deleted, category, config_name, `value`, description) VALUES ("
        . $db->quoted($id) . ", "
        . $db->quoted($configName) . ", "
        . $db->quoted($now) . ", "
        . $db->quoted($now) . ", "
        . "0, "
        . $db->quoted($category) . ", "
        . $db->quoted($configName) . ", "
        . $db->quoted($value) . ", "
        . $db->quoted($description)
        . ")";

    $result = $db->query($sql, false);
    if ($result) {
        echo "[OK] Config entry '$category.$configName' inserted successfully.\n";
    } else {
        echo "[ERROR] Failed to insert config entry '$category.$configName'.\n";
        $hasError = true;
    }
}

// ============================================================
// Final status
// ============================================================
echo "\n";
if ($hasError) {
    echo "[ERROR] Installation completed with errors.\n";
    exit(1);
} else {
    echo "[OK] Installation completed successfully.\n";
    exit(0);
}
