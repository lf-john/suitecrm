<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');

class UsersViewChange_password_json extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_title'] = false;
        $this->options['show_subpanels'] = false;
    }

    public function process()
    {
        // Skip all parent processing — just call display directly
        $this->display();
    }

    public function display()
    {
        global $current_user;
        
        // Clear any existing output
        while (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/json');
        
        if (empty($current_user->id)) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
            exit;
        }
        
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        if (!$input) {
            $input = $_POST;
        }
        
        $oldPassword = $input['old_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';
        
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            exit;
        }
        
        $focus = BeanFactory::getBean('Users', $current_user->id);
        if (!$focus || empty($focus->id)) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        if ($focus->change_password($oldPassword, $newPassword)) {
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        }
        exit;
    }
}
