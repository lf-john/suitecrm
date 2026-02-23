<?php
/* Smarty version 4.5.3, created on 2026-02-23 11:48:55
  from '/var/www/html/public/legacy/include/SugarFields/Fields/Base/ListView.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_699c3ea7a215a6_00607032',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c3d247f8fae5a151bb6a18f80993a68185b47de1' => 
    array (
      0 => '/var/www/html/public/legacy/include/SugarFields/Fields/Base/ListView.tpl',
      1 => 1769132138,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699c3ea7a215a6_00607032 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/var/www/html/public/legacy/include/Smarty/plugins/function.sugar_fetch.php','function'=>'smarty_function_sugar_fetch',),));
?>

<?php echo smarty_function_sugar_fetch(array('object'=>$_smarty_tpl->tpl_vars['parentFieldArray']->value,'key'=>$_smarty_tpl->tpl_vars['col']->value),$_smarty_tpl);?>

<?php }
}
