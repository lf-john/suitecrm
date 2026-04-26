<?php
/**
 * AJAX Password Change Endpoint
 * POST with JSON body: { old_password, new_password, confirm_password }
 * Returns JSON: { success: bool, message: string }
 */
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

chdir(dirname(__FILE__) . '/../../..');
require_once 'include/entryPoint.php';

header('Content-Type: application/json');

// Must be logged in
if (empty($current_user->id)) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
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

// Load the current user bean
$focus = BeanFactory::getBean('Users', $current_user->id);
if (!$focus || empty($focus->id)) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Attempt password change (validates old password internally)
if ($focus->change_password($oldPassword, $newPassword)) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
}
