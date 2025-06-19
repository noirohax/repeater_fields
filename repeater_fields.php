<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Repeater Fields
Description: Add repeater functionality to custom fields - make any field infinitely repeatable
Version: 1.0.0
Author: Judah
*/

define('REPEATER_FIELDS_MODULE_NAME', 'repeater_fields');

hooks()->add_action('admin_init', 'repeater_fields_module_init_menu_items');
hooks()->add_action('app_admin_head', 'repeater_fields_add_head_components');
hooks()->add_action('app_admin_footer', 'repeater_fields_add_footer_components');

// Custom field hooks
hooks()->add_action('custom_field_extra_options', 'repeater_fields_add_custom_field_option');
hooks()->add_action('before_custom_field_updated', 'repeater_fields_handle_custom_field_save');
hooks()->add_action('after_custom_field_added', 'repeater_fields_handle_custom_field_save');

register_activation_hook(REPEATER_FIELDS_MODULE_NAME, 'repeater_fields_module_activation_hook');

function repeater_fields_module_activation_hook()
{
    $CI = &get_instance();
    
    // Add is_repeater column to customfields table if it doesn't exist
    if (!$CI->db->field_exists('is_repeater', db_prefix() . 'customfields')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'customfields` ADD `is_repeater` tinyint(1) DEFAULT 0');
    }

    // Create table to store repeater field values
    if (!$CI->db->table_exists(db_prefix() . 'customfields_repeater_values')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'customfields_repeater_values` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `fieldid` int(11) NOT NULL,
            `relid` int(11) NOT NULL,
            `fieldto` varchar(20) NOT NULL,
            `value` text DEFAULT NULL,
            `field_index` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `fieldid` (`fieldid`),
            KEY `relid` (`relid`),
            KEY `fieldto` (`fieldto`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
}

function repeater_fields_module_init_menu_items()
{
    // Module doesn't need a separate menu item
}

// Add CSS and JS components
function repeater_fields_add_head_components()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    
    // Only load on pages that might have custom fields
    if (strpos($viewuri, '/admin/') !== false) {
        echo '<link href="' . module_dir_url(REPEATER_FIELDS_MODULE_NAME, 'assets/css/repeater_fields.css') . '?v=' . time() . '" rel="stylesheet" type="text/css" />';
    }
}

function repeater_fields_add_footer_components()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    
    // Only load on pages that might have custom fields
    if (strpos($viewuri, '/admin/') !== false) {
        echo '<script src="' . module_dir_url(REPEATER_FIELDS_MODULE_NAME, 'assets/js/repeater_fields.js') . '?v=' . time() . '"></script>';
    }
}

// Add repeater checkbox to custom field options
function repeater_fields_add_custom_field_option($field = null)
{
    $checked = '';
    if ($field && isset($field->is_repeater) && $field->is_repeater == 1) {
        $checked = 'checked';
    }
    
    echo '<div class="form-group">';
    echo '<div class="checkbox checkbox-primary">';
    echo '<input type="checkbox" name="is_repeater" id="is_repeater" value="1" ' . $checked . '>';
    echo '<label for="is_repeater">';
    echo '<strong>Make this field repeatable</strong>';
    echo '<br><small class="text-muted">Users can add multiple values for this field by clicking the + button</small>';
    echo '</label>';
    echo '</div>';
    echo '</div>';
}

// Handle saving the repeater option
function repeater_fields_handle_custom_field_save($field_id)
{
    $CI = &get_instance();
    
    $is_repeater = $CI->input->post('is_repeater') ? 1 : 0;
    
    if ($field_id) {
        $CI->db->where('id', $field_id);
        $CI->db->update(db_prefix() . 'customfields', array('is_repeater' => $is_repeater));
    }
}