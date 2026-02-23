<?php
/* Smarty version 4.5.3, created on 2026-02-23 11:48:55
  from '/var/www/html/public/legacy/themes/suite8/include/ListView/ListViewSelectObjects.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_699c3ea79ed480_79133069',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2d5f179503cb95ebda983165d622ecf56799f3a5' => 
    array (
      0 => '/var/www/html/public/legacy/themes/suite8/include/ListView/ListViewSelectObjects.tpl',
      1 => 1769132138,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_699c3ea79ed480_79133069 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="selectedRecords label hidden"><?php echo $_smarty_tpl->tpl_vars['APP']->value['LBL_LISTVIEW_SELECTED_OBJECTS'];?>
</div><div class="selectedRecords value hidden"><?php echo $_smarty_tpl->tpl_vars['TOTAL_ITEMS_SELECTED']->value;?>
</div>
<input type='hidden' id='selectCountTop' name='selectCount[]' value='<?php echo $_smarty_tpl->tpl_vars['TOTAL_ITEMS_SELECTED']->value;?>
' />

<?php echo '<script'; ?>
>

    $(document).ready(function () {
        setInterval(function () {
            sListView.toggleSelected();
        }, 100);
    });

<?php echo '</script'; ?>
><?php }
}
