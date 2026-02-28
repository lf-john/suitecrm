<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/Controller/SugarController.php');

class LF_PRConfigController extends SugarController
{
    /**
     * Config action - displays the admin configuration view
     */
    public function action_config()
    {
        $this->view = 'config';
    }
}
