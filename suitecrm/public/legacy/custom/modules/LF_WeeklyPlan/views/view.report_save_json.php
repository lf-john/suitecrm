<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';
require_once 'custom/modules/LF_PlanProspectItem/LF_PlanProspectItem.php';

#[\AllowDynamicProperties]
class LF_WeeklyPlanViewReport_save_json extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
    }

    public function display()
    {
        global $current_user;
        header('Content-Type: application/json');

        // CSRF token validation
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !isset($_SESSION['lf_csrf_token']) || $csrfToken !== $_SESSION['lf_csrf_token']) {
            file_put_contents(sugar_cached('lf_save_debug.log'), date('Y-m-d H:i:s') . " REPORT_CSRF_FAILED\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $logFile = sugar_cached('lf_save_debug.log');
        file_put_contents($logFile, date("Y-m-d H:i:s") . " REPORT_SAVE: user=" . $current_user->id . " action=" . ($data["action"] ?? "none") . " raw_body=" . substr($input, 0, 500) . "\n", FILE_APPEND);
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (empty($data) || empty($data['action'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        try {
            switch ($data['action']) {
                case 'convert':
                    $this->handleConvert($data);
                    break;
                case 'no_opportunity':
                    $this->handleNoOpportunity($data);
                    break;
                case 'submit':
                    $this->handleSubmission($data);
                    break;
                case 'save_result_description':
                    $this->handleSaveResultDescription($data);
                    break;
                case 'save_prospect_notes':
                    $this->handleSaveProspectNotes($data);
                    break;
                case 'save_all':
                    $this->handleSaveAll($data);
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Unknown action']);
                    break;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Verify that the current user owns the weekly plan associated with a plan item.
     */
    private function verifyItemOwnership($bean, $planIdField = 'lf_weekly_plan_id')
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        $planId = $bean->$planIdField ?? '';
        if (empty($planId)) {
            throw new Exception('Access denied');
        }

        $ownerCheck = $db->query(sprintf(
            "SELECT assigned_user_id FROM lf_weekly_plan WHERE id = %s AND deleted = 0",
            $db->quoted($planId)
        ));
        $ownerRow = $db->fetchByAssoc($ownerCheck);
        if (!$ownerRow || $ownerRow['assigned_user_id'] !== $current_user->id) {
            throw new Exception('Access denied');
        }
    }

    private function handleConvert($data)
    {
        if (empty($data['id'])) {
            throw new Exception('Missing prospect item ID');
        }
        if (empty($data['account_name'])) {
            throw new Exception('Missing account name');
        }
        if (empty($data['opportunity_name'])) {
            throw new Exception('Missing opportunity name');
        }
        if (!isset($data['amount']) || !is_numeric($data['amount'])) {
            throw new Exception('Invalid amount');
        }

        $prospectItem = BeanFactory::getBean('LF_PlanProspectItem', $data['id']);
        if (!$prospectItem || empty($prospectItem->id)) {
            throw new Exception('Prospect item not found');
        }

        $this->verifyItemOwnership($prospectItem);

        $opportunity = $prospectItem->convertToOpportunity(
            $data['account_name'],
            $data['opportunity_name'],
            (float)$data['amount']
        );

        echo json_encode([
            'success' => true,
            'opportunity_id' => $opportunity->id
        ]);
    }

    private function handleNoOpportunity($data)
    {
        if (empty($data['id'])) {
            throw new Exception('Missing prospect item ID');
        }

        $prospectItem = BeanFactory::getBean('LF_PlanProspectItem', $data['id']);
        if (!$prospectItem || empty($prospectItem->id)) {
            throw new Exception('Prospect item not found');
        }

        $this->verifyItemOwnership($prospectItem);

        $prospectItem->status = 'no_opportunity';
        if (isset($data['notes'])) {
            $prospectItem->prospecting_notes = $data['notes'];
        }
        $prospectItem->save();

        echo json_encode(['success' => true]);
    }

    private function handleSubmission($data)
    {
        global $current_user;
        require_once 'custom/include/LF_PlanningReporting/WeekHelper.php';
        require_once 'custom/modules/LF_WeeklyReport/LF_WeeklyReport.php';

        $weekStart = WeekHelper::getCurrentWeekStart();
        $report = LF_WeeklyReport::getOrCreateForWeek($current_user->id, $weekStart);

        if (!$report || empty($report->id)) {
            throw new Exception('Report not found');
        }

        $report->status = 'submitted';
        $report->submitted_date = $data['submitted_date'] ?? gmdate('Y-m-d H:i:s');
        $report->save();

        echo json_encode(['success' => true]);
    }

    /**
     * Verify that the current user owns the weekly report associated with a snapshot.
     */
    private function verifySnapshotOwnership($snapshot)
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        $reportId = $snapshot->lf_weekly_report_id ?? '';
        if (empty($reportId)) {
            throw new Exception('Access denied');
        }

        $ownerCheck = $db->query(sprintf(
            "SELECT assigned_user_id FROM lf_weekly_report WHERE id = %s AND deleted = 0",
            $db->quoted($reportId)
        ));
        $ownerRow = $db->fetchByAssoc($ownerCheck);
        if (!$ownerRow || $ownerRow['assigned_user_id'] !== $current_user->id) {
            throw new Exception('Access denied');
        }
    }

    private function handleSaveResultDescription($data)
    {
        $logFile = sugar_cached('lf_save_debug.log');
        if (empty($data['snapshot_id'])) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " REPORT_RESULT_DESC: missing snapshot_id\n", FILE_APPEND);
            throw new Exception('Missing snapshot ID');
        }

        file_put_contents($logFile, date('Y-m-d H:i:s') . " REPORT_RESULT_DESC: snapshot_id=" . $data['snapshot_id'] . " desc=[" . substr($data['result_description'] ?? '', 0, 80) . "]\n", FILE_APPEND);

        $snapshot = BeanFactory::getBean('LF_ReportSnapshot', $data['snapshot_id']);
        if (!$snapshot || empty($snapshot->id)) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " REPORT_RESULT_DESC: snapshot NOT FOUND for id=" . $data['snapshot_id'] . "\n", FILE_APPEND);
            throw new Exception('Snapshot not found');
        }

        $this->verifySnapshotOwnership($snapshot);

        $snapshot->result_description = $data['result_description'] ?? '';
        $snapshot->save();

        // Verify the save by re-reading from DB
        $db = DBManagerFactory::getInstance();
        $verifyRes = $db->query(sprintf("SELECT result_description FROM lf_report_snapshots WHERE id = %s", $db->quoted($data['snapshot_id'])));
        $verifyRow = $db->fetchByAssoc($verifyRes);
        $savedDesc = $verifyRow ? $verifyRow['result_description'] : 'QUERY_FAILED';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " REPORT_RESULT_DESC_VERIFY: db_value=[" . substr($savedDesc ?? 'NULL', 0, 80) . "]\n", FILE_APPEND);

        echo json_encode(['success' => true]);
    }

    private function handleSaveAll($data)
    {
        $saved = 0;

        // Save all result descriptions
        if (!empty($data['descriptions']) && is_array($data['descriptions'])) {
            foreach ($data['descriptions'] as $item) {
                if (empty($item['snapshot_id'])) continue;
                $snapshot = BeanFactory::getBean('LF_ReportSnapshot', $item['snapshot_id']);
                if ($snapshot && !empty($snapshot->id)) {
                    $this->verifySnapshotOwnership($snapshot);
                    $snapshot->result_description = $item['result_description'] ?? '';
                    $snapshot->save();
                    $saved++;
                }
            }
        }

        // Save all prospect notes
        if (!empty($data['prospect_notes']) && is_array($data['prospect_notes'])) {
            foreach ($data['prospect_notes'] as $item) {
                if (empty($item['id'])) continue;
                $prospectItem = BeanFactory::getBean('LF_PlanProspectItem', $item['id']);
                if ($prospectItem && !empty($prospectItem->id)) {
                    $this->verifyItemOwnership($prospectItem);
                    $prospectItem->prospecting_notes = $item['notes'] ?? '';
                    $prospectItem->save();
                    $saved++;
                }
            }
        }

        echo json_encode(['success' => true, 'saved' => $saved]);
    }

    private function handleSaveProspectNotes($data)
    {
        if (empty($data['id'])) {
            throw new Exception('Missing prospect item ID');
        }

        $prospectItem = BeanFactory::getBean('LF_PlanProspectItem', $data['id']);
        if (!$prospectItem || empty($prospectItem->id)) {
            throw new Exception('Prospect item not found');
        }

        $this->verifyItemOwnership($prospectItem);

        $prospectItem->prospecting_notes = $data['notes'] ?? '';
        $prospectItem->save();

        echo json_encode(['success' => true]);
    }
}
