<?php
/**
 * Daily Plans Dashlet
 * Displays today's planned activities from LF_WeeklyPlan module
 *
 * @package LF_WeeklyPlan
 */

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/Dashlets/Dashlet.php');
require_once('custom/include/LF_PlanningReporting/WeekHelper.php');

class DailyPlansDashlet extends Dashlet
{
    public $isConfigurable = true;
    public $hasScript = false;

    protected $dayMap = array(
        0 => 'sunday',
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday'
    );

    /**
     * Constructor
     */
    public function __construct($id, $def = null)
    {
        global $current_user, $app_strings;

        parent::__construct($id);

        $this->isConfigurable = true;
        $this->hasScript = false;

        if (empty($def['title'])) {
            $this->title = "Today's Plans";
        } else {
            $this->title = $def['title'];
        }
    }

    /**
     * Display the dashlet
     */
    public function display()
    {
        global $current_user, $db, $app_list_strings;

        $html = '<div class="daily-plans-dashlet" style="padding: 10px;">';

        // Get current day of week
        $dayOfWeek = date('w'); // 0 = Sunday, 6 = Saturday
        $todayKey = $this->dayMap[$dayOfWeek];
        $todayName = ucfirst($todayKey);
        $todayDate = date('l, F j, Y');

        $html .= '<div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #125EAD;">';
        $html .= '<h4 style="margin: 0; color: #125EAD; font-size: 16px;">' . htmlspecialchars($todayDate) . '</h4>';
        $html .= '</div>';

        // Get current week's start date using configured week start day
        $weekStart = WeekHelper::getCurrentWeekStart();

        // Find the current user's weekly plan for this week
        $planQuery = "SELECT id FROM lf_weekly_plan
                      WHERE assigned_user_id = " . $db->quoted($current_user->id) . "
                      AND week_start_date = " . $db->quoted($weekStart) . "
                      AND deleted = 0
                      LIMIT 1";

        $planResult = $db->query($planQuery);
        $planRow = $db->fetchByAssoc($planResult);

        if (!$planRow) {
            $html .= '<div style="text-align: center; padding: 20px; color: #666;">';
            $html .= '<p style="margin: 0;">No weekly plan found for this week.</p>';
            $html .= '<a href="index.php?module=LF_WeeklyPlan&action=EditView" class="btn btn-primary" style="margin-top: 10px; display: inline-block; padding: 8px 16px; background: #125EAD; color: white; text-decoration: none; border-radius: 4px;">Create Weekly Plan</a>';
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        }

        $weeklyPlanId = $planRow['id'];

        // Get Opportunity items for today
        $opItemsQuery = "SELECT poi.id, poi.name, poi.plan_description, poi.item_type, poi.projected_stage,
                                o.name as opportunity_name, o.id as opportunity_id, o.sales_stage, o.amount
                         FROM lf_plan_op_items poi
                         LEFT JOIN opportunities o ON poi.opportunity_id = o.id AND o.deleted = 0
                         WHERE poi.lf_weekly_plan_id = " . $db->quoted($weeklyPlanId) . "
                         AND poi.planned_day = " . $db->quoted($todayKey) . "
                         AND poi.deleted = 0
                         ORDER BY poi.date_entered ASC";

        $opResult = $db->query($opItemsQuery);
        $opItems = array();
        while ($row = $db->fetchByAssoc($opResult)) {
            $opItems[] = $row;
        }

        // Get Prospect items for today
        $prospectQuery = "SELECT ppi.id, ppi.name, ppi.plan_description, ppi.source_type,
                                 ppi.expected_value, ppi.status, ppi.prospecting_notes
                          FROM lf_plan_prospect_items ppi
                          WHERE ppi.lf_weekly_plan_id = " . $db->quoted($weeklyPlanId) . "
                          AND ppi.planned_day = " . $db->quoted($todayKey) . "
                          AND ppi.deleted = 0
                          ORDER BY ppi.date_entered ASC";

        $prospectResult = $db->query($prospectQuery);
        $prospectItems = array();
        while ($row = $db->fetchByAssoc($prospectResult)) {
            $prospectItems[] = $row;
        }

        $totalItems = count($opItems) + count($prospectItems);

        if ($totalItems === 0) {
            $html .= '<div style="text-align: center; padding: 20px; color: #666;">';
            $html .= '<p style="margin: 0;">No activities planned for today.</p>';
            $html .= '<a href="index.php?module=LF_WeeklyPlan&action=DetailView&record=' . $weeklyPlanId . '" style="color: #125EAD; text-decoration: none;">View Weekly Plan</a>';
            $html .= '</div>';
        } else {
            // Display summary
            $html .= '<div style="margin-bottom: 15px; display: flex; gap: 15px;">';
            $html .= '<div style="flex: 1; background: #e8f4fd; padding: 10px; border-radius: 6px; text-align: center;">';
            $html .= '<div style="font-size: 24px; font-weight: bold; color: #125EAD;">' . count($opItems) . '</div>';
            $html .= '<div style="font-size: 12px; color: #666;">Opportunities</div>';
            $html .= '</div>';
            $html .= '<div style="flex: 1; background: #e8f9e8; padding: 10px; border-radius: 6px; text-align: center;">';
            $html .= '<div style="font-size: 24px; font-weight: bold; color: #4BB74E;">' . count($prospectItems) . '</div>';
            $html .= '<div style="font-size: 12px; color: #666;">Prospecting</div>';
            $html .= '</div>';
            $html .= '</div>';

            // Display Opportunity Items
            if (!empty($opItems)) {
                $html .= '<div style="margin-bottom: 15px;">';
                $html .= '<h5 style="margin: 0 0 10px 0; color: #125EAD; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Opportunity Activities</h5>';

                foreach ($opItems as $item) {
                    $html .= '<div style="background: #f8f9fa; border-left: 3px solid #125EAD; padding: 10px; margin-bottom: 8px; border-radius: 0 4px 4px 0;">';

                    if (!empty($item['opportunity_name'])) {
                        $html .= '<div style="font-weight: 600; color: #333;">';
                        $html .= '<a href="index.php?module=Opportunities&action=DetailView&record=' . htmlspecialchars($item['opportunity_id']) . '" style="color: #125EAD; text-decoration: none;">';
                        $html .= htmlspecialchars($item['opportunity_name']);
                        $html .= '</a>';
                        if (!empty($item['amount'])) {
                            $html .= ' <span style="color: #4BB74E;">($' . number_format($item['amount'], 0) . ')</span>';
                        }
                        $html .= '</div>';
                    }

                    if (!empty($item['plan_description'])) {
                        $html .= '<div style="font-size: 13px; color: #555; margin-top: 5px;">' . htmlspecialchars($item['plan_description']) . '</div>';
                    }

                    if (!empty($item['projected_stage'])) {
                        $html .= '<div style="font-size: 12px; color: #888; margin-top: 5px;">';
                        $html .= '<span style="background: #e8f4fd; padding: 2px 8px; border-radius: 10px;">Target: ' . htmlspecialchars($item['projected_stage']) . '</span>';
                        $html .= '</div>';
                    }

                    $html .= '</div>';
                }

                $html .= '</div>';
            }

            // Display Prospect Items
            if (!empty($prospectItems)) {
                $html .= '<div style="margin-bottom: 15px;">';
                $html .= '<h5 style="margin: 0 0 10px 0; color: #4BB74E; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Prospecting Activities</h5>';

                foreach ($prospectItems as $item) {
                    $html .= '<div style="background: #f8f9fa; border-left: 3px solid #4BB74E; padding: 10px; margin-bottom: 8px; border-radius: 0 4px 4px 0;">';

                    $html .= '<div style="font-weight: 600; color: #333;">' . htmlspecialchars($item['name']) . '</div>';

                    if (!empty($item['source_type'])) {
                        $html .= '<div style="font-size: 12px; color: #666; margin-top: 3px;">Source: ' . htmlspecialchars($item['source_type']) . '</div>';
                    }

                    if (!empty($item['plan_description'])) {
                        $html .= '<div style="font-size: 13px; color: #555; margin-top: 5px;">' . htmlspecialchars($item['plan_description']) . '</div>';
                    }

                    if (!empty($item['expected_value']) && $item['expected_value'] > 0) {
                        $html .= '<div style="font-size: 12px; color: #888; margin-top: 5px;">';
                        $html .= '<span style="background: #e8f9e8; padding: 2px 8px; border-radius: 10px;">Expected: $' . number_format($item['expected_value'], 0) . '</span>';
                        $html .= '</div>';
                    }

                    $html .= '</div>';
                }

                $html .= '</div>';
            }
        }

        // Footer link to full plan
        $html .= '<div style="text-align: center; padding-top: 10px; border-top: 1px solid #eee;">';
        $html .= '<a href="index.php?module=LF_WeeklyPlan&action=DetailView&record=' . $weeklyPlanId . '" style="color: #125EAD; text-decoration: none; font-size: 13px;">View Full Weekly Plan →</a>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Display configuration options
     */
    public function displayOptions()
    {
        $html = '<table width="100%" cellpadding="5" cellspacing="0" border="0">';
        $html .= '<tr>';
        $html .= '<td width="30%"><label>Title:</label></td>';
        $html .= '<td><input type="text" name="title" id="title" value="' . htmlspecialchars($this->title) . '" size="40"></td>';
        $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Save configuration options
     */
    public function saveOptions($req)
    {
        $options = array();

        if (!empty($req['title'])) {
            $options['title'] = $req['title'];
        }

        return $options;
    }
}
