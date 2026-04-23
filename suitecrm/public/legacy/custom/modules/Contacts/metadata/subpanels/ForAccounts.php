<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Override: adds Title column after Name

$subpanel_layout = array(
    'top_buttons' => array(
        array('widget_class' => 'SubPanelTopCreateButton'),
        array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Contacts'),
    ),

    'where' => '',

    'list_fields' => array(
        'first_name' => array(
            'name'  => 'first_name',
            'usage' => 'query_only',
        ),
        'last_name' => array(
            'name'  => 'last_name',
            'usage' => 'query_only',
        ),
        'salutation' => array(
            'name'  => 'salutation',
            'usage' => 'query_only',
        ),
        'name' => array(
            'name'         => 'name',
            'vname'        => 'LBL_LIST_NAME',
            'widget_class' => 'SubPanelDetailViewLink',
            'module'       => 'Contacts',
            'width'        => '25%',
        ),
        'title' => array(
            'name'  => 'title',
            'vname' => 'LBL_TITLE',
            'width' => '18%',
        ),
        'primary_address_city' => array(
            'name'  => 'primary_address_city',
            'vname' => 'LBL_LIST_CITY',
            'width' => '15%',
        ),
        'primary_address_state' => array(
            'name'  => 'primary_address_state',
            'vname' => 'LBL_LIST_STATE',
            'width' => '10%',
        ),
        'email1' => array(
            'name'         => 'email1',
            'vname'        => 'LBL_LIST_EMAIL',
            'widget_class' => 'SubPanelEmailLink',
            'width'        => '22%',
            'sortable'     => false,
        ),
        'phone_work' => array(
            'name'  => 'phone_work',
            'vname' => 'LBL_LIST_PHONE',
            'width' => '15%',
        ),
        'edit_button' => array(
            'vname'        => 'LBL_EDIT_BUTTON',
            'widget_class' => 'SubPanelEditButton',
            'module'       => 'Contacts',
            'width'        => '5%',
        ),
        'remove_button' => array(
            'vname'        => 'LBL_REMOVE',
            'widget_class' => 'SubPanelRemoveButton',
            'width'        => '5%',
        ),
    ),
);
