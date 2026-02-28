<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/View/SugarView.php');

/**
 * LF_WeeklyPlan List View - redirects to planning action
 * This module doesn't have a traditional list view.
 */
#[\AllowDynamicProperties]
class LF_WeeklyPlanViewList extends SugarView
{
    public function display()
    {
        // Redirect to the planning page
        header('Location: index.php?module=LF_WeeklyPlan&action=planning');
        exit;
    }
}
