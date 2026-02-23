<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/Controller/SugarController.php';

class LF_WeeklyReportController extends SugarController
{
    public function action_index()
    {
        $this->view = 'reporting';
    }

    public function action_reporting()
    {
        $this->view = 'reporting';
    }

    public function action_dashboard()
    {
        $this->view = 'dashboard';
    }

    public function action_save_json()
    {
        $this->view = 'save_json';
    }
}
