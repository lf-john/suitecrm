<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/Controller/SugarController.php');

class LF_RepTargetsController extends SugarController
{
    /**
     * Manage action - displays the rep targets management view
     */
    public function action_manage()
    {
        $this->view = 'manage';
    }
}
