<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/View/SugarView.php');
require_once('custom/modules/LF_PRConfig/LF_PRConfig.php');
require_once('custom/include/LF_PlanningReporting/LF_SubHeader.php');

#[\AllowDynamicProperties]
class LF_RepTargetsViewManage extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = true;
        $this->options['show_footer'] = true;
    }

    public function display()
    {
        global $current_user, $mod_strings;
        $db = DBManagerFactory::getInstance();

        // H2: Admin access control
        if (!$current_user->is_admin) {
            sugar_die('Access denied: Admin role required');
        }

        // Initialize CSRF token if not set (independent of Config page)
        if (empty($_SESSION['csrf_form_token'])) {
            $_SESSION['csrf_form_token'] = bin2hex(random_bytes(32));
        }

        $this->handlePost();

        // Load default values from config
        $defaultAnnualQuota = LF_PRConfig::getConfig('quotas', 'default_annual_quota');
        $defaultNewPipeline = LF_PRConfig::getConfig('targets', 'default_new_pipeline_target');
        $defaultProgression = LF_PRConfig::getConfig('targets', 'default_progression_target');
        $defaultClosed = LF_PRConfig::getConfig('targets', 'default_closed_target');

        // Query existing reps - split by active/inactive
        $query = "SELECT rt.*, u.first_name, u.last_name
                  FROM lf_rep_targets rt
                  JOIN users u ON rt.assigned_user_id = u.id
                  WHERE rt.deleted = 0 AND u.deleted = 0
                  ORDER BY rt.is_active DESC, u.last_name, u.first_name, rt.fiscal_year DESC";
        $result = $db->query($query);

        $activeReps = [];
        $inactiveReps = [];
        while ($row = $db->fetchByAssoc($result)) {
            if ($row['is_active']) {
                $activeReps[] = $row;
            } else {
                $inactiveReps[] = $row;
            }
        }

        // Query users NOT in lf_rep_targets for the current fiscal year
        $userQuery = "SELECT id, first_name, last_name
                      FROM users
                      WHERE deleted = 0 AND status = 'Active'
                      AND id NOT IN (SELECT assigned_user_id FROM lf_rep_targets WHERE deleted = 0 AND fiscal_year = " . (int)date('Y') . ")
                      ORDER BY last_name, first_name";
        $userResult = $db->query($userQuery);

        // Include CSS
        echo '<link rel="stylesheet" href="custom/themes/lf_dashboard.css">';

        // Render sub-header CSS and JS
        LF_SubHeader::renderCSS();
        LF_SubHeader::renderJS();

        // Render SuiteCRM-style sub-header (no user selector for rep targets)
        LF_SubHeader::render('Rep Targets', []);

        // Content Wrapper
        echo '<div class="lf-content-wrapper">';
        echo '<div class="lf-rep-targets-wrapper" style="padding: 0;">';

        // Add Rep Form Section
        echo '<div class="lf-form-section">';
        echo '<h3>Add New Rep</h3>';
        echo '<form method="post" action="index.php?module=LF_RepTargets&action=manage">';
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_form_token'] ?? '') . '">';
        echo '<input type="hidden" name="action_type" value="add_rep">';
        echo '<div class="lf-field-row">';
        echo '<span class="lf-field-label">User:</span>';
        echo '<select name="user_id" required class="lf-select" style="min-width: 250px;">';
        echo '<option value="">-- Select User --</option>';
        while ($user = $db->fetchByAssoc($userResult)) {
            echo '<option value="' . htmlspecialchars($user['id']) . '">' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</option>';
        }
        echo '</select>';
        echo '<span class="lf-field-label" style="min-width: 100px;">Fiscal Year:</span>';
        echo '<input type="number" name="fiscal_year" value="' . date('Y') . '" required class="lf-input" style="width: 100px;">';
        echo '<button type="submit" class="lf-btn lf-btn-primary">Add Rep</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';

        // Two Column Grid for Active/Inactive Reps
        echo '<div class="lf-two-column-grid">';

        // Active Reps Column
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Active Reps (' . count($activeReps) . ')</h2></div>';
        echo '<div class="lf-card-content">';
        if (empty($activeReps)) {
            echo '<p style="color: #666; text-align: center; padding: 20px;">No active reps configured.</p>';
        } else {
            $this->renderRepTable($activeReps, $defaultAnnualQuota, $defaultNewPipeline, $defaultProgression, $defaultClosed, true);
        }
        echo '</div>';
        echo '</div>';

        // Inactive Reps Column
        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header"><h2>Inactive Reps (' . count($inactiveReps) . ')</h2></div>';
        echo '<div class="lf-card-content">';
        if (empty($inactiveReps)) {
            echo '<p style="color: #666; text-align: center; padding: 20px;">No inactive reps.</p>';
        } else {
            $this->renderRepTable($inactiveReps, $defaultAnnualQuota, $defaultNewPipeline, $defaultProgression, $defaultClosed, false);
        }
        echo '</div>';
        echo '</div>';

        echo '</div>'; // end two-column grid

        // Default Values Info
        echo '<div class="lf-info-box" style="margin-top: 24px;">';
        echo '<div class="lf-info-box-title">Default Values (from Configuration)</div>';
        echo '<div class="lf-info-box-content">';
        echo 'Annual Quota: $' . number_format((float)$defaultAnnualQuota, 0) . ' | ';
        echo 'Weekly New Pipeline: $' . number_format((float)$defaultNewPipeline, 0) . ' | ';
        echo 'Weekly Progression: $' . number_format((float)$defaultProgression, 0) . ' | ';
        echo 'Weekly Closed: $' . number_format((float)$defaultClosed, 0);
        echo '</div>';
        echo '</div>';

        echo '</div>'; // end rep-targets-wrapper
        echo '</div>'; // end content wrapper
    }

    private function renderRepTable($reps, $defaultAnnualQuota, $defaultNewPipeline, $defaultProgression, $defaultClosed, $isActive)
    {
        $hiddenForms = [];

        echo '<table class="lf-table">';
        echo '<thead><tr>';
        echo '<th>Rep Name</th>';
        echo '<th>Year</th>';
        echo '<th>Annual Quota</th>';
        echo '<th>Wkly New</th>';
        echo '<th>Wkly Prog</th>';
        echo '<th>Wkly Close</th>';
        echo '<th>Actions</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($reps as $row) {
            $repName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
            $id = htmlspecialchars($row['id']);
            $csrfToken = htmlspecialchars($_SESSION['csrf_form_token'] ?? '');

            $annualQuota = $row['annual_quota'];
            $weeklyNewPipeline = $row['weekly_new_pipeline'];
            $weeklyProgression = $row['weekly_progression'];
            $weeklyClosed = $row['weekly_closed'];

            echo '<tr>';
            echo '<td><strong>' . $repName . '</strong></td>';
            echo '<td>' . htmlspecialchars($row['fiscal_year']) . '</td>';

            // Annual Quota
            $placeholder = number_format((float)$defaultAnnualQuota, 0);
            echo '<td><input type="number" form="form_' . $id . '" name="annual_quota" value="' . ($annualQuota !== null && $annualQuota !== '' ? htmlspecialchars($annualQuota) : '') . '" placeholder="' . $placeholder . '" style="width: 90px;"></td>';

            // Weekly New Pipeline
            $placeholder = number_format((float)$defaultNewPipeline, 0);
            echo '<td><input type="number" form="form_' . $id . '" name="weekly_new_pipeline" value="' . ($weeklyNewPipeline !== null && $weeklyNewPipeline !== '' ? htmlspecialchars($weeklyNewPipeline) : '') . '" placeholder="' . $placeholder . '" style="width: 70px;"></td>';

            // Weekly Progression
            $placeholder = number_format((float)$defaultProgression, 0);
            echo '<td><input type="number" form="form_' . $id . '" name="weekly_progression" value="' . ($weeklyProgression !== null && $weeklyProgression !== '' ? htmlspecialchars($weeklyProgression) : '') . '" placeholder="' . $placeholder . '" style="width: 70px;"></td>';

            // Weekly Closed
            $placeholder = number_format((float)$defaultClosed, 0);
            echo '<td><input type="number" form="form_' . $id . '" name="weekly_closed" value="' . ($weeklyClosed !== null && $weeklyClosed !== '' ? htmlspecialchars($weeklyClosed) : '') . '" placeholder="' . $placeholder . '" style="width: 70px;"></td>';

            // Actions
            echo '<td style="white-space: nowrap;">';
            echo '<button type="submit" form="form_' . $id . '" class="lf-btn lf-btn-primary" style="padding: 4px 10px; font-size: 12px;">Save</button> ';
            if ($isActive) {
                echo '<button type="submit" form="toggle_' . $id . '" class="lf-btn lf-btn-secondary" style="padding: 4px 10px; font-size: 12px;">Deactivate</button>';
            } else {
                echo '<button type="submit" form="toggle_' . $id . '" class="lf-btn lf-btn-success" style="padding: 4px 10px; font-size: 12px;">Activate</button>';
            }
            echo '</td>';
            echo '</tr>';

            $hiddenForms[] = '<form id="form_' . $id . '" method="post" action="index.php?module=LF_RepTargets&action=manage">'
                . '<input type="hidden" name="csrf_token" value="' . $csrfToken . '">'
                . '<input type="hidden" name="action_type" value="update_targets">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '</form>';

            $hiddenForms[] = '<form id="toggle_' . $id . '" method="post" action="index.php?module=LF_RepTargets&action=manage">'
                . '<input type="hidden" name="csrf_token" value="' . $csrfToken . '">'
                . '<input type="hidden" name="action_type" value="toggle_active">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '<input type="hidden" name="is_active" value="' . ($isActive ? '0' : '1') . '">'
                . '</form>';
        }

        echo '</tbody></table>';

        // Output hidden forms
        echo '<div style="display:none;">';
        foreach ($hiddenForms as $form) {
            echo $form;
        }
        echo '</div>';
    }

    private function handlePost()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['action_type'])) {
            return;
        }

        // H4: CSRF token validation
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_form_token']) {
            sugar_die('Invalid CSRF token');
        }

        $actionType = $_POST['action_type'];

        if ($actionType === 'add_rep') {
            $userId = $_POST['user_id'] ?? '';
            $fiscalYear = (int)($_POST['fiscal_year'] ?? 0);

            if (!empty($userId) && !empty($fiscalYear)) {
                $id = create_guid();
                $now = gmdate('Y-m-d H:i:s');
                $query = sprintf(
                    "INSERT INTO lf_rep_targets (id, name, date_entered, date_modified, modified_user_id, created_by, deleted, assigned_user_id, fiscal_year, is_active)
                     VALUES (%s, %s, %s, %s, %s, %s, 0, %s, %d, 1)",
                    $db->quoted($id),
                    $db->quoted('Rep Target ' . $fiscalYear),
                    $db->quoted($now),
                    $db->quoted($now),
                    $db->quoted($current_user->id),
                    $db->quoted($current_user->id),
                    $db->quoted($userId),
                    $fiscalYear
                );
                $db->query($query);
            }
        } elseif ($actionType === 'update_targets') {
            $id = $_POST['id'] ?? '';
            $annualQuota = ($_POST['annual_quota'] ?? '') === '' ? "NULL" : (float)$_POST['annual_quota'];
            $weeklyNewPipeline = ($_POST['weekly_new_pipeline'] ?? '') === '' ? "NULL" : (float)$_POST['weekly_new_pipeline'];
            $weeklyProgression = ($_POST['weekly_progression'] ?? '') === '' ? "NULL" : (float)$_POST['weekly_progression'];
            $weeklyClosed = ($_POST['weekly_closed'] ?? '') === '' ? "NULL" : (float)$_POST['weekly_closed'];

            if (!empty($id)) {
                $now = gmdate('Y-m-d H:i:s');
                $query = sprintf(
                    "UPDATE lf_rep_targets SET
                        annual_quota = %s,
                        weekly_new_pipeline = %s,
                        weekly_progression = %s,
                        weekly_closed = %s,
                        date_modified = %s,
                        modified_user_id = %s
                     WHERE id = %s",
                    $annualQuota === "NULL" ? "NULL" : $annualQuota,
                    $weeklyNewPipeline === "NULL" ? "NULL" : $weeklyNewPipeline,
                    $weeklyProgression === "NULL" ? "NULL" : $weeklyProgression,
                    $weeklyClosed === "NULL" ? "NULL" : $weeklyClosed,
                    $db->quoted($now),
                    $db->quoted($current_user->id),
                    $db->quoted($id)
                );
                $db->query($query);
            }
        } elseif ($actionType === 'toggle_active') {
            $id = $_POST['id'] ?? '';
            $isActive = (int)($_POST['is_active'] ?? 0);

            if (!empty($id)) {
                $now = gmdate('Y-m-d H:i:s');
                $query = sprintf(
                    "UPDATE lf_rep_targets SET
                        is_active = %d,
                        date_modified = %s,
                        modified_user_id = %s
                     WHERE id = %s",
                    $isActive,
                    $db->quoted($now),
                    $db->quoted($current_user->id),
                    $db->quoted($id)
                );
                $db->query($query);
            }
        }
    }
}
