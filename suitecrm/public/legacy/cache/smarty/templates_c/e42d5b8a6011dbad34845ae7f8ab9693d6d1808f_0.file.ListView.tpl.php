<?php
/* Smarty version 4.5.3, created on 2026-02-23 11:48:55
  from '/var/www/html/public/legacy/include/SugarFields/Fields/Link/ListView.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_699c3ea7a2a299_21476468',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e42d5b8a6011dbad34845ae7f8ab9693d6d1808f' => 
    array (
      0 => '/var/www/html/public/legacy/include/SugarFields/Fields/Link/ListView.tpl',
      1 => 1769132138,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699c3ea7a2a299_21476468 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/var/www/html/public/legacy/include/Smarty/plugins/function.sugar_fetch.php','function'=>'smarty_function_sugar_fetch',),1=>array('file'=>'/var/www/html/public/legacy/include/Smarty/plugins/function.sugar_replace_vars.php','function'=>'smarty_function_sugar_replace_vars',),2=>array('file'=>'/var/www/html/public/legacy/include/Smarty/plugins/modifier.to_url.php','function'=>'smarty_modifier_to_url',),));
$_smarty_tpl->smarty->ext->_capture->open($_smarty_tpl, 'getLink', 'link', null);
echo smarty_function_sugar_fetch(array('object'=>$_smarty_tpl->tpl_vars['parentFieldArray']->value,'key'=>$_smarty_tpl->tpl_vars['col']->value),$_smarty_tpl);
$_smarty_tpl->smarty->ext->_capture->close($_smarty_tpl);
if ($_smarty_tpl->tpl_vars['vardef']->value['gen'] && $_smarty_tpl->tpl_vars['vardef']->value['default'] && $_smarty_tpl->tpl_vars['link']->value) {?>
    <?php $_smarty_tpl->smarty->ext->_capture->open($_smarty_tpl, 'getDefault', 'default', null);
if (is_string($_smarty_tpl->tpl_vars['vardef']->value['default'])) {
echo $_smarty_tpl->tpl_vars['vardef']->value['default'];
} else {
echo $_smarty_tpl->tpl_vars['link']->value;
}
$_smarty_tpl->smarty->ext->_capture->close($_smarty_tpl);?>
    <?php echo smarty_function_sugar_replace_vars(array('subject'=>$_smarty_tpl->tpl_vars['default']->value,'use_curly'=>true,'assign'=>'link','fields'=>$_smarty_tpl->tpl_vars['parentFieldArray']->value),$_smarty_tpl);?>

<?php }?>

<a href="<?php echo smarty_modifier_to_url($_smarty_tpl->tpl_vars['link']->value);?>
" <?php if ((isset($_smarty_tpl->tpl_vars['displayParams']->value['link_target']))) {?>target='<?php echo $_smarty_tpl->tpl_vars['displayParams']->value['link_target'];?>
'<?php } elseif ((isset($_smarty_tpl->tpl_vars['vardef']->value['link_target']))) {?>target='<?php echo $_smarty_tpl->tpl_vars['vardef']->value['link_target'];?>
'<?php }?>><?php echo $_smarty_tpl->tpl_vars['link']->value;?>
</a>
<?php }
}
