<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/View/SugarView.php');
require_once('custom/modules/LF_PRConfig/LF_PRConfig.php');
require_once('custom/include/LF_PlanningReporting/LF_SubHeader.php');

#[\AllowDynamicProperties]
class LF_PRConfigViewConfig extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = true;
        $this->options['show_footer'] = true;
    }

    public function display()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        // H1: Admin access control
        if (!$current_user->is_admin) {
            sugar_die('Access denied: Admin role required');
        }

        // Generate CSRF token if not set
        if (empty($_SESSION['csrf_form_token'])) {
            $_SESSION['csrf_form_token'] = bin2hex(random_bytes(32));
        }

        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // H3: CSRF token validation
            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_form_token']) {
                sugar_die('Invalid CSRF token');
            }
            $success = true;
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'config_') === 0) {
                    // Format: config_{category}__{configName}
                    $parts = explode('__', substr($key, 7));
                    if (count($parts) === 2) {
                        $category = $parts[0];
                        $configName = $parts[1];

                        $finalValue = $value;
                        if ($configName === 'source_types') {
                            $lines = explode("\n", str_replace("\r", "", $value));
                            $finalValue = json_encode(array_values(array_filter(array_map('trim', $lines))));
                        } elseif ($configName === 'activity_types') {
                            $finalValue = json_encode($value);
                        }

                        $sql = "UPDATE lf_pr_config 
                                SET value = '" . $db->quote($finalValue) . "', 
                                    date_modified = '" . gmdate('Y-m-d H:i:s') . "' 
                                WHERE category = '" . $db->quote($category) . "' 
                                  AND config_name = '" . $db->quote($configName) . "' 
                                  AND deleted = 0";
                        
                        if (!$db->query($sql)) {
                            $success = false;
                        }
                    }
                }
            }

            // Regenerate CSRF token after processing POST
            $_SESSION['csrf_form_token'] = bin2hex(random_bytes(32));

            if ($success) {
                $message = 'Configuration saved successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error saving configuration.';
                $messageType = 'error';
            }
        }

        // Load config values
        $quotas_annual = LF_PRConfig::getConfig('quotas', 'default_annual_quota');
        $quotas_multiplier = LF_PRConfig::getConfig('quotas', 'pipeline_coverage_multiplier');
        $quotas_fiscal_month = LF_PRConfig::getConfig('quotas', 'fiscal_year_start_month');

        $targets_new = LF_PRConfig::getConfig('targets', 'default_new_pipeline_target');
        $targets_progression = LF_PRConfig::getConfig('targets', 'default_progression_target');
        $targets_closed = LF_PRConfig::getConfig('targets', 'default_closed_target');

        $weeks_start = LF_PRConfig::getConfig('weeks', 'week_start_day');
        $weeks_to_show = LF_PRConfig::getConfig('weeks', 'weeks_to_show');
        $snapshot_time = LF_PRConfig::getConfig('weeks', 'snapshot_time') ?: '09:00';
        $plan_day = LF_PRConfig::getConfig('weeks', 'plan_day') ?: '1';
        $plan_time = LF_PRConfig::getConfig('weeks', 'plan_time') ?: '10:00';

        $display_green = LF_PRConfig::getConfig('display', 'achievement_tier_green');
        $display_yellow = LF_PRConfig::getConfig('display', 'achievement_tier_yellow');
        $display_orange = LF_PRConfig::getConfig('display', 'achievement_tier_orange');

        $stages = LF_PRConfig::getConfigJson('stages', 'stage_probabilities');
        $source_types = LF_PRConfig::getConfigJson('prospecting', 'source_types');
        
        $risk_stale_days = LF_PRConfig::getConfig('risk', 'stale_deal_days');
        $activity_types = LF_PRConfig::getConfigJson('risk', 'activity_types') ?: [];

        // Include CSS
        echo '<link rel="stylesheet" href="custom/themes/lf_dashboard.css">';
        echo '<script src="custom/modules/LF_PRConfig/js/config.js"></script>';

        // Render sub-header CSS and JS
        LF_SubHeader::renderCSS();
        LF_SubHeader::renderJS();

        // Render SuiteCRM-style sub-header (no user selector for config page)
        LF_SubHeader::render('Configuration', []);

        // Content wrapper
        echo '<div class="lf-content-wrapper">';

        if ($message) {
            echo '<div class="message ' . $messageType . '">' . htmlspecialchars($message) . '</div>';
        }

        echo '<form method="post" action="index.php?module=LF_PRConfig&action=config">';
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_form_token'] ?? '') . '">';

        // Two-column grid layout
        echo '<div class="lf-two-column-grid">';

        // === LEFT COLUMN ===
        echo '<div class="lf-config-column">';

        // 1. Quota Settings
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Quota Settings</h2></div>';
        echo '<div class="lf-card-content">';
        echo '<div class="field-container">';
        echo '<label>Annual Quota:</label>';
        echo '<input type="number" name="config_quotas__default_annual_quota" value="' . htmlspecialchars($quotas_annual) . '" required>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Coverage Multiplier:</label>';
        echo '<input type="number" step="0.1" name="config_quotas__pipeline_coverage_multiplier" value="' . htmlspecialchars($quotas_multiplier) . '" required>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Fiscal Year Start Month:</label>';
        echo '<select name="config_quotas__fiscal_year_start_month">';
        for ($i = 1; $i <= 12; $i++) {
            $selected = ($quotas_fiscal_month == $i) ? 'selected' : '';
            echo '<option value="' . $i . '" ' . $selected . '>' . date('F', mktime(0, 0, 0, $i, 1)) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div></div>';

        // 2. Weekly Targets
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Weekly Targets</h2></div>';
        echo '<div class="lf-card-content">';
        echo '<div class="field-container">';
        echo '<label>New Pipeline Target:</label>';
        echo '<input type="number" name="config_targets__default_new_pipeline_target" value="' . htmlspecialchars($targets_new) . '" required>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Progression Target:</label>';
        echo '<input type="number" name="config_targets__default_progression_target" value="' . htmlspecialchars($targets_progression) . '" required>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Closed Target:</label>';
        echo '<input type="number" name="config_targets__default_closed_target" value="' . htmlspecialchars($targets_closed) . '" required>';
        echo '</div>';
        echo '</div></div>';

        // 3. Week Configuration
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Week Configuration</h2></div>';
        echo '<div class="lf-card-content">';
        echo '<div class="field-container">';
        echo '<label>Start Day:</label>';
        echo '<select name="config_weeks__week_start_day">';
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        foreach ($days as $num => $dayName) {
            $selected = ((int)$weeks_start === $num) ? 'selected' : '';
            echo '<option value="' . $num . '" ' . $selected . '>' . $dayName . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Weeks to Show:</label>';
        echo '<input type="number" name="config_weeks__weeks_to_show" value="' . htmlspecialchars($weeks_to_show) . '" min="1" max="52" required>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Snapshot Time (Mountain):</label>';
        echo '<input type="time" name="config_weeks__snapshot_time" value="' . htmlspecialchars($snapshot_time) . '" required>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Plan Due Day:</label>';
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        echo '<select name="config_weeks__plan_day">';
        foreach ($days as $num => $dayName) {
            $selected = ((int)$plan_day === $num) ? 'selected' : '';
            echo '<option value="' . $num . '" ' . $selected . '>' . $dayName . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Plan Due Time (Mountain):</label>';
        echo '<input type="time" name="config_weeks__plan_time" value="' . htmlspecialchars($plan_time) . '" required>';
        echo '</div>';
        echo '</div></div>';

        // 4. Display Settings
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Display Settings (Achievement Tiers)</h2></div>';
        echo '<div class="lf-card-content">';
        echo '<div class="field-container">';
        echo '<label>Green Threshold:</label>';
        echo '<input type="number" name="config_display__achievement_tier_green" value="' . htmlspecialchars($display_green) . '" required> %';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Yellow Threshold:</label>';
        echo '<input type="number" name="config_display__achievement_tier_yellow" value="' . htmlspecialchars($display_yellow) . '" required> %';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Orange Threshold:</label>';
        echo '<input type="number" name="config_display__achievement_tier_orange" value="' . htmlspecialchars($display_orange) . '" required> %';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Red Threshold:</label>';
        echo '<span style="color: #666;">Below Orange</span>';
        echo '</div>';
        echo '</div></div>';

        echo '</div>'; // end left column

        // === RIGHT COLUMN ===
        echo '<div class="lf-config-column">';

        // 5. Stage Configuration (Read-Only)
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Stage Configuration</h2></div>';
        echo '<div class="lf-card-content">';
        echo '<p style="color: #666; font-size: 13px; margin-bottom: 12px;">Current pipeline stages and probabilities (Read-Only):</p>';
        echo '<table class="lf-table">';
        echo '<thead><tr><th>Stage</th><th>Probability</th></tr></thead>';
        echo '<tbody>';
        if (is_array($stages)) {
            foreach ($stages as $stage => $prob) {
                echo '<tr><td>' . htmlspecialchars($stage) . '</td><td>' . htmlspecialchars($prob) . '%</td></tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div></div>';

        // 6. Prospecting Source Types
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Prospecting Source Types</h2></div>';
        echo '<div class="lf-card-content">';
        echo '<div class="field-container">';
        echo '<label>Source Types (one per line):</label><br>';
        $source_text = is_array($source_types) ? implode("\n", $source_types) : '';
        echo '<textarea name="config_prospecting__source_types" rows="6" style="width: 100%;">' . htmlspecialchars($source_text) . '</textarea>';
        echo '</div>';
        echo '</div></div>';

        // 7. Deal Risk Settings
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Deal Risk Settings</h2></div>';
        echo '<div class="lf-card-content">';
        echo '<div class="field-container">';
        echo '<label>Stale Deal Days:</label>';
        echo '<input type="number" name="config_risk__stale_deal_days" value="' . htmlspecialchars($risk_stale_days) . '" required>';
        echo '</div>';
        echo '<div class="field-container">';
        echo '<label>Activity Types to Consider:</label><br>';
        $acts = ['Calls', 'Meetings', 'Tasks', 'Notes', 'Emails'];
        echo '<div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px;">';
        foreach ($acts as $act) {
            $checked = in_array($act, $activity_types) ? 'checked' : '';
            echo '<label style="display: flex; align-items: center; gap: 4px; cursor: pointer;">';
            echo '<input type="checkbox" name="config_risk__activity_types[]" value="' . $act . '" ' . $checked . '> ' . $act;
            echo '</label>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div></div>';

        echo '</div>'; // end right column

        echo '</div>'; // end two-column grid

        // Save button
        echo '<div style="margin-top: 24px; text-align: center;">';
        echo '<button type="submit" class="lf-btn lf-btn-primary" style="padding: 12px 32px; font-size: 16px;">Save Configuration</button>';
        echo '</div>';

        echo '</form>';
        echo '</div>'; // end content wrapper
    }
}