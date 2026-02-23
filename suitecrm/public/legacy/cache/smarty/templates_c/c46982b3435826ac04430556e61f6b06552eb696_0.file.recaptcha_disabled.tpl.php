<?php
/* Smarty version 4.5.3, created on 2026-02-22 07:44:22
  from '/var/www/html/public/legacy/include/utils/recaptcha_disabled.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_699ab3d60279f7_28583161',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c46982b3435826ac04430556e61f6b06552eb696' => 
    array (
      0 => '/var/www/html/public/legacy/include/utils/recaptcha_disabled.tpl',
      1 => 1769132138,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699ab3d60279f7_28583161 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

  /**
   * Login Screen Validation
   */
  function validateAndSubmit() {
      generatepwd();
    }

  /**
   * Password reset screen validation
   */
  function validateCaptchaAndSubmit() {
      document.getElementById('username_password').value = document.getElementById('new_password').value;
      document.getElementById('ChangePasswordForm').submit();
    }
<?php echo '</script'; ?>
>
<?php }
}
