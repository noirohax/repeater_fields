<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Render repeater field HTML
 */
if (!function_exists('render_repeater_field')) {
    function render_repeater_field($field, $value = '', $field_name = '')
    {
        $CI = &get_instance();
        
        // Load the repeater model if not already loaded
        if (!isset($CI->repeater_fields_model)) {
            $CI->load->model('repeater_fields_model');
        }
        
        // Check if this field is marked as repeatable
        if (!isset($field['is_repeater']) || $field['is_repeater'] != 1) {
            return false; // Not a repeater field
        }
        
        // Parse existing values
        $values = array();
        if (!empty($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $values = $decoded;
            } else {
                $values = array($value);
            }
        }
        
        if (empty($values)) {
            $values = array(''); // At least one empty field
        }
        
        $field_id = $field['id'];
        $name_attr = !empty($field_name) ? $field_name : 'custom_fields[' . $field['name'] . ']';
        
        $output = '<div class="repeater-field-container" data-field-id="' . $field_id . '">';
        
        foreach ($values as $index => $val) {
            $output .= render_repeater_field_group($field, $val, $index, $name_attr, $index == 0);
        }
        
        // Add template for new fields
        $output .= '<div class="repeater-field-template" data-field-id="' . $field_id . '" style="display: none;">';
        $output .= render_repeater_field_group($field, '', '__INDEX__', $name_attr, false);
        $output .= '</div>';
        
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Render individual repeater field group
 */
if (!function_exists('render_repeater_field_group')) {
    function render_repeater_field_group($field, $value, $index, $name_attr, $is_first = false)
    {
        $output = '<div class="repeater-field-group" data-index="' . $index . '">';
        $output .= '<div class="row">';
        $output .= '<div class="col-md-10">';
        
        // Generate field input based on type
        $field_input = render_custom_field_by_type($field, $value, $name_attr . '[' . $index . ']');
        $output .= $field_input;
        
        $output .= '</div>';
        $output .= '<div class="col-md-2">';
        $output .= '<div class="repeater-controls">';
        
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
}

/**
 * Render custom field input by type
 */
if (!function_exists('render_custom_field_by_type')) {
    function render_custom_field_by_type($field, $value = '', $name = '')
    {
        $type = $field['type'];
        $field_name = !empty($name) ? $name : 'custom_fields[' . $field['name'] . ']';
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
                return '<input type="date" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            case 'colorpicker':
                return '<input type="color" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            default:
                return '<input type="text" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
        }
    }
}