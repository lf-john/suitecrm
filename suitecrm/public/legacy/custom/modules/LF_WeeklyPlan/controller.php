<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/Controller/SugarController.php');

class LF_WeeklyPlanController extends SugarController
{
    public function action_index()
    {
        header('Location: index.php?module=LF_WeeklyPlan&action=dashboard');
        exit;
    }

    public function action_listview()
    {
        header('Location: index.php?module=LF_WeeklyPlan&action=dashboard');
        exit;
    }

    public function action_planning()
    {
        $this->view = 'planning';
    }

    public function action_dashboard()
    {
        $this->view = 'dashboard';
    }

    public function action_plan()
    {
        $this->view = 'dashboard';
    }

    public function action_save_json()
    {
        $this->view = 'save_json';
    }

    public function action_reporting()
    {
        $this->view = 'reporting';
    }

    public function action_report()
    {
        $this->view = 'reporting';
    }

    public function action_rep_report()
    {
        $this->view = 'rep_report';
    }

    // CamelCase alias: Angular route sends action=RepReport → strtolower → repreport
    public function action_repreport()
    {
        $this->view = 'rep_report';
    }

    public function action_report_save_json()
    {
        $this->view = 'report_save_json';
    }
}
