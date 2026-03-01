<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'modules/Users/controller.php';

class CustomUsersController extends UsersController
{
    protected function action_change_password_json()
    {
        $this->view = 'change_password_json';
    }
}
