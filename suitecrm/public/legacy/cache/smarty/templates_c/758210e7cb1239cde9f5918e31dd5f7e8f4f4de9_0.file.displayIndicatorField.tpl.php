<?php
/* Smarty version 4.5.3, created on 2026-02-22 06:52:45
  from '/var/www/html/public/legacy/modules/Emails/templates/displayIndicatorField.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_699aa7bd113605_32451160',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '758210e7cb1239cde9f5918e31dd5f7e8f4f4de9' => 
    array (
      0 => '/var/www/html/public/legacy/modules/Emails/templates/displayIndicatorField.tpl',
      1 => 1769132138,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699aa7bd113605_32451160 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="email-indicator">
    <?php if (!empty($_smarty_tpl->tpl_vars['bean']->value)) {?>
        <?php if (!empty($_smarty_tpl->tpl_vars['bean']->value['status']) && $_smarty_tpl->tpl_vars['bean']->value['status'] == 'unread') {?>
            <div class="email-new"></div>
        <?php }?>
        <?php if (!empty($_smarty_tpl->tpl_vars['bean']->value['is_imported']) && !empty($_smarty_tpl->tpl_vars['bean']->value['inbound_email_record']) && $_smarty_tpl->tpl_vars['bean']->value['is_imported'] == true && $_smarty_tpl->tpl_vars['bean']->value['inbound_email_record'] == $_REQUEST['inbound_email_record']) {?>
            <div class="email-imported"><span class="glyphicon glyphicon-ok"></span></div>
        <?php }?>
        <?php if (!empty($_smarty_tpl->tpl_vars['bean']->value['flagged']) && $_smarty_tpl->tpl_vars['bean']->value['flagged'] == 1) {?>
            <span class="email-flagged">!</span>
        <?php }?>
    <?php }?>
</div>
<?php }
}
