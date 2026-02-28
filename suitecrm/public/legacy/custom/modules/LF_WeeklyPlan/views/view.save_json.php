<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/View/SugarView.php');

#[\AllowDynamicProperties]
class LF_WeeklyPlanViewSave_json extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_title'] = false;
        $this->options['show_subpanels'] = false;
    }

    public function process()
    {
        // Override process() to skip all header/footer rendering
        $this->display();
    }

    public function display()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        header('Content-Type: application/json');

        // CSRF token validation
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !isset($_SESSION['lf_csrf_token']) || $csrfToken !== $_SESSION['lf_csrf_token']) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        // JSON parsing with error handling
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
            exit;
        }
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        $planId = $input['plan_id'] ?? null;
        if (!$planId) {
            echo json_encode(['success' => false, 'message' => 'Missing plan ID']);
            exit;
        }

        // Verify plan ownership before modification
        $ownerCheck = $db->query(sprintf(
            "SELECT assigned_user_id FROM lf_weekly_plan WHERE id = %s AND deleted = 0",
            $db->quoted($planId)
        ));
        $ownerRow = $db->fetchByAssoc($ownerCheck);
        if (!$ownerRow || $ownerRow['assigned_user_id'] !== $current_user->id) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $now = gmdate('Y-m-d H:i:s');
        $dbErrors = [];

        if (isset($input['status']) && $input['status'] === 'submitted') {
            // Store frozen totals at submit time
            $frozenClosing = isset($input['frozen_closing']) ? (float)$input['frozen_closing'] : 0;
            $frozenProgression = isset($input['frozen_progression']) ? (float)$input['frozen_progression'] : 0;
            $frozenNewPipeline = isset($input['frozen_new_pipeline']) ? (float)$input['frozen_new_pipeline'] : 0;

            $query = sprintf(
                "UPDATE lf_weekly_plan SET status = 'submitted', submitted_date = %s, date_modified = %s, modified_user_id = %s, frozen_closing = %s, frozen_progression = %s, frozen_new_pipeline = %s WHERE id = %s AND deleted = 0",
                $db->quoted($now),
                $db->quoted($now),
                $db->quoted($current_user->id),
                $frozenClosing,
                $frozenProgression,
                $frozenNewPipeline,
                $db->quoted($planId)
            );
            $result = $db->query($query);
            if ($result === false) {
                $dbErrors[] = 'Failed to update plan status';
            }
        } elseif (isset($input['status']) && $input['status'] === 'in_progress') {
            $query = sprintf(
                "UPDATE lf_weekly_plan SET status = 'in_progress', date_modified = %s, modified_user_id = %s WHERE id = %s AND deleted = 0",
                $db->quoted($now),
                $db->quoted($current_user->id),
                $db->quoted($planId)
            );
            $result = $db->query($query);
            if ($result === false) {
                $dbErrors[] = 'Failed to update plan status';
            }
        }

        // Save Op Items
        if (isset($input['op_items']) && is_array($input['op_items'])) {
            foreach ($input['op_items'] as $item) {
                $oppId = $item['opportunity_id'];
                $itemType = $item['item_type'];
                $projStage = $item['projected_stage'];
                $plannedDay = $item['planned_day'];
                $planDesc = $item['plan_description'];
                $isAtRisk = !empty($item['is_at_risk']) ? 1 : 0;

                // Look up current stage and profit from opportunity for snapshot
                $oppQuery = sprintf(
                    "SELECT sales_stage, opportunity_profit FROM opportunities WHERE id = %s AND deleted = 0",
                    $db->quoted($oppId)
                );
                $oppRes = $db->query($oppQuery);
                $oppRow = $db->fetchByAssoc($oppRes);
                $originalStage = $oppRow ? $oppRow['sales_stage'] : '';
                $originalProfit = $oppRow ? (float)$oppRow['opportunity_profit'] : 0;

                $checkQuery = sprintf(
                    "SELECT id, original_stage FROM lf_plan_op_items WHERE lf_weekly_plan_id = %s AND opportunity_id = %s AND deleted = 0",
                    $db->quoted($planId),
                    $db->quoted($oppId)
                );
                $res = $db->query($checkQuery);
                $row = $db->fetchByAssoc($res);

                if ($row) {
                    // Only update original_stage/original_profit if not already set (preserve first snapshot)
                    $origStageSql = '';
                    if (empty($row['original_stage'])) {
                        $origStageSql = sprintf(", original_stage = %s, original_profit = %s",
                            $db->quoted($originalStage),
                            $originalProfit
                        );
                    }
                    $updateQuery = sprintf(
                        "UPDATE lf_plan_op_items SET item_type = %s, projected_stage = %s, planned_day = %s, plan_description = %s, is_at_risk = %d, date_modified = %s, modified_user_id = %s%s WHERE id = %s",
                        $db->quoted($itemType),
                        $db->quoted($projStage),
                        $db->quoted($plannedDay),
                        $db->quoted($planDesc),
                        $isAtRisk,
                        $db->quoted(gmdate('Y-m-d H:i:s')),
                        $db->quoted($current_user->id),
                        $origStageSql,
                        $db->quoted($row['id'])
                    );
                    $db->query($updateQuery);
                } else {
                    $newId = create_guid();
                    $insertQuery = sprintf(
                        "INSERT INTO lf_plan_op_items (id, name, date_entered, date_modified, modified_user_id, created_by, deleted, lf_weekly_plan_id, opportunity_id, item_type, projected_stage, original_stage, original_profit, planned_day, plan_description, is_at_risk) VALUES (%s, %s, %s, %s, %s, %s, 0, %s, %s, %s, %s, %s, %s, %s, %s, %d)",
                        $db->quoted($newId),
                        $db->quoted("Plan Item for $oppId"),
                        $db->quoted(gmdate('Y-m-d H:i:s')),
                        $db->quoted(gmdate('Y-m-d H:i:s')),
                        $db->quoted($current_user->id),
                        $db->quoted($current_user->id),
                        $db->quoted($planId),
                        $db->quoted($oppId),
                        $db->quoted($itemType),
                        $db->quoted($projStage),
                        $db->quoted($originalStage),
                        $originalProfit,
                        $db->quoted($plannedDay),
                        $db->quoted($planDesc),
                        $isAtRisk
                    );
                    $db->query($insertQuery);
                }
            }
        }

        // Save Prospect Items (with expected_revenue and expected_profit)
        $sentProspectIds = [];
        if (isset($input['prospect_items']) && is_array($input['prospect_items'])) {
            foreach ($input['prospect_items'] as $item) {
                $sourceType = $item['source_type'];
                $plannedDay = $item['planned_day'];
                $expectedRevenue = (float)($item['expected_revenue'] ?? $item['expected_value'] ?? 0);
                $expectedProfit = (float)($item['expected_profit'] ?? 0);
                $expectedValue = $expectedRevenue; // backward compat
                $planDesc = $item['plan_description'];

                if (!empty($item['id'])) {
                    $updateQuery = sprintf(
                        "UPDATE lf_plan_prospect_items SET source_type = %s, planned_day = %s, expected_value = %s, expected_revenue = %s, expected_profit = %s, plan_description = %s, date_modified = %s, modified_user_id = %s WHERE id = %s AND deleted = 0",
                        $db->quoted($sourceType),
                        $db->quoted($plannedDay),
                        $expectedValue,
                        $expectedRevenue,
                        $expectedProfit,
                        $db->quoted($planDesc),
                        $db->quoted(gmdate('Y-m-d H:i:s')),
                        $db->quoted($current_user->id),
                        $db->quoted($item['id'])
                    );
                    $db->query($updateQuery);
                    $sentProspectIds[] = $item['id'];
                } else {
                    $newId = create_guid();
                    $insertQuery = sprintf(
                        "INSERT INTO lf_plan_prospect_items (id, name, date_entered, date_modified, modified_user_id, created_by, deleted, lf_weekly_plan_id, source_type, planned_day, expected_value, expected_revenue, expected_profit, plan_description) VALUES (%s, %s, %s, %s, %s, %s, 0, %s, %s, %s, %s, %s, %s, %s)",
                        $db->quoted($newId),
                        $db->quoted("Prospect Item"),
                        $db->quoted(gmdate('Y-m-d H:i:s')),
                        $db->quoted(gmdate('Y-m-d H:i:s')),
                        $db->quoted($current_user->id),
                        $db->quoted($current_user->id),
                        $db->quoted($planId),
                        $db->quoted($sourceType),
                        $db->quoted($plannedDay),
                        $expectedValue,
                        $expectedRevenue,
                        $expectedProfit,
                        $db->quoted($planDesc)
                    );
                    $db->query($insertQuery);
                    $sentProspectIds[] = $newId;
                }
            }
        }
        // Soft-delete prospect items not in payload
        if (!empty($sentProspectIds)) {
            $idList = implode(',', array_map(fn($id) => $db->quoted($id), $sentProspectIds));
            $db->query(sprintf(
                "UPDATE lf_plan_prospect_items SET deleted = 1 WHERE lf_weekly_plan_id = %s AND id NOT IN (%s) AND deleted = 0",
                $db->quoted($planId),
                $idList
            ));
        } else {
            $db->query(sprintf(
                "UPDATE lf_plan_prospect_items SET deleted = 1 WHERE lf_weekly_plan_id = %s AND deleted = 0",
                $db->quoted($planId)
            ));
        }

        if (!empty($dbErrors)) {
            echo json_encode(['success' => false, 'message' => 'Database errors occurred', 'errors' => $dbErrors]);
        } else {
            echo json_encode(['success' => true, 'message' => 'Plan saved successfully']);
        }
        exit;
    }
}
