<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/Controller/SugarController.php');

class LF_WeeklyPlanController extends SugarController
{
    /**
     * Default action - redirect to dashboard
     */
    public function action_index()
    {
        header('Location: index.php?module=LF_WeeklyPlan&action=dashboard');
        exit;
    }

    /**
     * List action - redirect to dashboard (no standard list view)
     */
    public function action_listview()
    {
        header('Location: index.php?module=LF_WeeklyPlan&action=dashboard');
        exit;
    }

    /**
     * Planning action - displays the weekly planning view
     */
    public function action_planning()
    {
        $this->view = 'planning';
    }

    /**
     * Dashboard action - displays the weekly planning dashboard
     */
    public function action_dashboard()
    {
        $this->view = 'dashboard';
    }

    /**
     * Plan action - alias for dashboard (user-friendly URL)
     */
    public function action_plan()
    {
        $this->view = 'dashboard';
    }

    /**
     * Save JSON action - handles AJAX save requests
     */
    public function action_save_json()
    {
        $this->view = 'save_json';
    }

    /**
     * Reporting action - displays the weekly reporting dashboard
     */
    public function action_reporting()
    {
        $this->view = 'reporting';
    }

    /**
     * Report action - alias for reporting (user-friendly URL)
     */
    public function action_report()
    {
        $this->view = 'reporting';
    }
}
