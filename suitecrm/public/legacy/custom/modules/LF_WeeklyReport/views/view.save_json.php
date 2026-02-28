<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';
require_once 'custom/modules/LF_PlanProspectItem/LF_PlanProspectItem.php';

#[\AllowDynamicProperties]
class LF_WeeklyReportViewSave_json extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
    }

    public function display()
    {
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
                default:
                    echo json_encode(['success' => false, 'message' => 'Unknown action']);
                    break;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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

    private function handleSaveResultDescription($data)
    {
        if (empty($data['snapshot_id'])) {
            throw new Exception('Missing snapshot ID');
        }

        $snapshot = BeanFactory::getBean('LF_ReportSnapshot', $data['snapshot_id']);
        if (!$snapshot || empty($snapshot->id)) {
            throw new Exception('Snapshot not found');
        }

        $snapshot->result_description = $data['result_description'] ?? '';
        $snapshot->save();

        echo json_encode(['success' => true]);
    }
}
