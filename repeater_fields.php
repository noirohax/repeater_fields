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

// Hook into custom fields rendering
hooks()->add_filter('render_custom_field_input', 'repeater_fields_render_custom_field', 10, 4);

// Hook into custom fields saving
hooks()->add_action('before_custom_fields_save', 'repeater_fields_save_values');

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
    
    // Load on admin pages that might have custom fields
    if (strpos($viewuri, '/admin/') !== false) {
        echo '<link href="' . module_dir_url(REPEATER_FIELDS_MODULE_NAME, 'assets/css/repeater_fields.css') . '?v=' . time() . '" rel="stylesheet" type="text/css" />';
    }
}

function repeater_fields_add_footer_components()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    
    // Load JS on admin pages that might have custom fields
    if (strpos($viewuri, '/admin/') !== false) {
        echo '<script src="' . module_dir_url(REPEATER_FIELDS_MODULE_NAME, 'assets/js/repeater_fields.js') . '?v=' . time() . '"></script>';
    }
}

/**
 * Hook into custom field rendering
 */
function repeater_fields_render_custom_field($field_html, $field, $value, $field_to)
{
    $CI = &get_instance();
    
    // Load the model
    if (!isset($CI->repeater_fields_model)) {
        $CI->load->model('repeater_fields_model');
    }
    
    // Check if this field is marked as repeatable
    if (!isset($field['is_repeater']) || $field['is_repeater'] != 1) {
        return $field_html; // Return original HTML if not a repeater field
    }
    
    // Load helper
    $CI->load->helper('repeater_fields');
    
    // Get repeater values if editing existing record
    $repeater_values = array();
    if (!empty($value) && $field_to && isset($_GET['id'])) {
        $relid = (int)$_GET['id'];
        $repeater_values = $CI->repeater_fields_model->get_repeater_values($field['id'], $relid, $field_to);
    }
    
    if (empty($repeater_values)) {
        $repeater_values = array(''); // At least one empty field
    }
    
    return render_repeater_field_html($field, $repeater_values);
}

/**
 * Save repeater field values
 */
function repeater_fields_save_values($data)
{
    $CI = &get_instance();
    
    if (!isset($CI->repeater_fields_model)) {
        $CI->load->model('repeater_fields_model');
    }
    
    // Get all repeater fields for this field_to
    $repeater_fields = $CI->repeater_fields_model->get_repeater_fields_by_fieldto($data['fieldto']);
    
    foreach ($repeater_fields as $field) {
        $field_key = 'repeater_field_' . $field['id'];
        if ($CI->input->post($field_key)) {
            $values = json_decode($CI->input->post($field_key), true);
            if (is_array($values)) {
                $CI->repeater_fields_model->save_repeater_values(
                    $field['id'],
                    $data['relid'],
                    $data['fieldto'],
                    $values
                );
            }
        }
    }
}

/**
 * Render repeater field HTML
 */
function render_repeater_field_html($field, $values = array())
{
    if (empty($values)) {
        $values = array('');
    }
    
    $field_id = $field['id'];
    $name_attr = 'custom_fields[' . $field['slug'] . ']';
    
    $output = '<div class="repeater-field-container" data-field-id="' . $field_id . '">';
    
    foreach ($values as $index => $val) {
        $output .= render_single_repeater_field($field, $val, $index, $name_attr, $index == 0);
    }
    
    // Add template for new fields
    $output .= '<div class="repeater-field-template" data-field-id="' . $field_id . '" style="display: none;">';
    $output .= render_single_repeater_field($field, '', '__INDEX__', $name_attr, false);
    $output .= '</div>';
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Render single repeater field
 */
function render_single_repeater_field($field, $value, $index, $name_attr, $is_first = false)
{
    $output = '<div class="repeater-field-group" data-index="' . $index . '">';
    $output .= '<div class="row">';
    $output .= '<div class="col-md-10">';
    
    // Generate field input based on type
    $field_input = render_custom_field_input_by_type($field, $value, $name_attr . '[' . $index . ']');
    $output .= $field_input;
    
    $output .= '</div>';
    $output .= '<div class="col-md-2">';
    $output .= '<div class="repeater-controls" style="margin-top: 25px;">';
    
    if ($is_first) {
        $output .= '<button type="button" class="btn btn-success btn-sm add-repeater-field" title="Add another field">';
        $output .= '<i class="fa fa-plus"></i>';
        $output .= '</button>';
    } else {
        $output .= '<button type="button" class="btn btn-danger btn-sm remove-repeater-field" title="Remove this field">';
        $output .= '<i class="fa fa-minus"></i>';
        $output .= '</button>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

/**
 * Render custom field input by type for repeater
 */
function render_custom_field_input_by_type($field, $value = '', $name = '')
{
    $type = $field['type'];
    $field_name = !empty($name) ? $name : 'custom_fields[' . $field['slug'] . ']';
    $required = isset($field['required']) && $field['required'] == 1 ? 'required' : '';
    
    switch ($type) {
        case 'input':
            return '<input type="text" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
            
        case 'number':
            return '<input type="number" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
            
        case 'textarea':
            return '<textarea class="form-control" name="' . $field_name . '" rows="4" ' . $required . '>' . htmlspecialchars($value) . '</textarea>';
            
        case 'select':
            $options = '<option value="">Select...</option>';
            if (!empty($field['options'])) {
                $field_options = explode(',', $field['options']);
                foreach ($field_options as $option) {
                    $option = trim($option);
                    $selected = ($value == $option) ? 'selected' : '';
                    $options .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                }
            }
            return '<select class="form-control" name="' . $field_name . '" ' . $required . '>' . $options . '</select>';
            
        case 'date_picker':
            return '<input type="date" class="form-control datepicker" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
            
        case 'colorpicker':
            return '<input type="color" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
            
        default:
            return '<input type="text" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
    }
}