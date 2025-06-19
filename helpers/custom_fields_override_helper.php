<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Override the default render_custom_fields function to handle repeater fields
 * You need to call this function instead of render_custom_fields() in your view files
 * where you want repeater functionality
 */
if (!function_exists('render_custom_fields_with_repeater')) {
    function render_custom_fields_with_repeater($field_to, $rel_id = false, $where = [], $items_pr_row = 1, $items_wrapper = '')
    {
        $CI = &get_instance();
        $CI->load->model('custom_fields_model');
        $CI->load->model('repeater_fields_model');
        
        $where['fieldto'] = $field_to;
        
        if (!isset($where['active'])) {
            $where['active'] = 1;
        }
        
        $custom_fields = $CI->custom_fields_model->get($where);
        
        if (count($custom_fields) == 0) {
            return '';
        }

        $custom_fields_html = '';
        $i = 0;
        
        foreach ($custom_fields as $field) {
            $value = '';
            
            if ($rel_id !== false) {
                $value = get_custom_field_value($rel_id, $field['id'], $field_to);
            }

            // Check if this is a repeater field
            if (isset($field['is_repeater']) && $field['is_repeater'] == 1) {
                // Get repeater values
                $repeater_values = array();
                if ($rel_id !== false) {
                    $repeater_values = $CI->repeater_fields_model->get_repeater_values($field['id'], $rel_id, $field_to);
                }
                
                if (empty($repeater_values)) {
                    $repeater_values = array('');
                }
                
                $custom_fields_html .= render_repeater_field_complete($field, $repeater_values, $field_to, $items_pr_row);
            } else {
                // Render normal field using Perfex's function
                $custom_fields_html .= render_custom_field($field, $value, $items_pr_row, $items_wrapper);
            }
            
            $i++;
        }

        return $custom_fields_html;
    }
}

/**
 * Render complete repeater field with form group wrapper
 */
if (!function_exists('render_repeater_field_complete')) {
    function render_repeater_field_complete($field, $values, $field_to, $items_pr_row = 1)
    {
        if (empty($values)) {
            $values = array('');
        }
        
        $field_id = $field['id'];
        $name_attr = 'custom_fields[' . $field['slug'] . ']';
        
        // Determine column class based on items per row
        $col_class = 'col-md-12';
        if ($items_pr_row == 2) {
            $col_class = 'col-md-6';
        } elseif ($items_pr_row == 3) {
            $col_class = 'col-md-4';
        } elseif ($items_pr_row == 4) {
            $col_class = 'col-md-3';
        }
        
        $output = '<div class="' . $col_class . '">';
        $output .= '<div class="form-group" data-fieldid="' . $field['id'] . '" data-fieldto="' . $field_to . '">';
        $output .= '<label for="' . $field['slug'] . '" class="control-label">';
        $output .= $field['name'];
        if ($field['required'] == 1) {
            $output .= ' <small class="req text-danger">*</small>';
        }
        $output .= '</label>';
        
        $output .= '<div class="repeater-field-container" data-field-id="' . $field_id . '">';
        
        foreach ($values as $index => $val) {
            $output .= render_repeater_field_single($field, $val, $index, $name_attr, $index == 0);
        }
        
        // Add template for new fields
        $output .= '<div class="repeater-field-template" data-field-id="' . $field_id . '" style="display: none !important;">';
        $output .= render_repeater_field_single($field, '', '__INDEX__', $name_attr, false);
        $output .= '</div>';
        
        $output .= '</div>';
        
        // Add/Remove buttons
        $output .= '<div class="repeater-field-actions" style="margin-top: 10px;">';
        $output .= '<button type="button" class="btn btn-success btn-sm add-repeater-field" data-field-id="' . $field_id . '">';
        $output .= '<i class="fa fa-plus"></i> Add Item</button>';
        $output .= '</div>';
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Render single repeater field item
 */
if (!function_exists('render_repeater_field_single')) {
    function render_repeater_field_single($field, $value, $index, $name_attr, $is_first = false)
    {
        $output = '<div class="repeater-field-item" data-index="' . $index . '" style="margin-bottom: 10px;">';
        $output .= '<div class="input-group">';
        
        // Generate field input based on field type
        $field_input = generate_repeater_field_input($field, $value, $index, $name_attr);
        $output .= $field_input;
        
        // Add remove button (not for first item or template)
        if (!$is_first && $index !== '__INDEX__') {
            $output .= '<div class="input-group-btn">';
            $output .= '<button type="button" class="btn btn-danger btn-sm remove-repeater-field" title="Remove">';
            $output .= '<i class="fa fa-minus"></i></button>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Generate field input based on field type
 */
if (!function_exists('generate_repeater_field_input')) {
    function generate_repeater_field_input($field, $value, $index, $name_attr)
    {
        $field_name = $name_attr . '[' . $index . ']';
        $field_id = $field['slug'] . '_' . $index;
        $required = $field['required'] == 1 ? 'required' : '';
        $placeholder = !empty($field['description']) ? $field['description'] : '';
        
        $output = '';
        
        switch ($field['type']) {
            case 'input':
            case 'text':
                $output = '<input type="text" class="form-control" name="' . $field_name . '" id="' . $field_id . '" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
                
            case 'number':
                $output = '<input type="number" class="form-control" name="' . $field_name . '" id="' . $field_id . '" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
                
            case 'email':
                $output = '<input type="email" class="form-control" name="' . $field_name . '" id="' . $field_id . '" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
                
            case 'textarea':
                $output = '<textarea class="form-control" name="' . $field_name . '" id="' . $field_id . '" rows="3" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>' . htmlspecialchars($value) . '</textarea>';
                break;
                
            case 'select':
                $output = '<select class="form-control selectpicker" name="' . $field_name . '" id="' . $field_id . '" ' . $required . '>';
                $output .= '<option value="">Select...</option>';
                
                // Parse options from field options
                if (!empty($field['options'])) {
                    $options = explode(',', $field['options']);
                    foreach ($options as $option) {
                        $option = trim($option);
                        $selected = ($value == $option) ? 'selected' : '';
                        $output .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                    }
                }
                $output .= '</select>';
                break;
                
            case 'multiselect':
                $output = '<select class="form-control selectpicker" name="' . $field_name . '[]" id="' . $field_id . '" multiple ' . $required . '>';
                
                // Parse options from field options
                if (!empty($field['options'])) {
                    $selected_values = is_array($value) ? $value : explode(',', $value);
                    $options = explode(',', $field['options']);
                    foreach ($options as $option) {
                        $option = trim($option);
                        $selected = in_array($option, $selected_values) ? 'selected' : '';
                        $output .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                    }
                }
                $output .= '</select>';
                break;
                
            case 'date':
                $output = '<input type="date" class="form-control" name="' . $field_name . '" id="' . $field_id . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                break;
                
            case 'datetime':
                $output = '<input type="datetime-local" class="form-control" name="' . $field_name . '" id="' . $field_id . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                break;
                
            case 'colorpicker':
                $output = '<input type="color" class="form-control" name="' . $field_name . '" id="' . $field_id . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                break;
                
            case 'file':
                $output = '<input type="file" class="form-control" name="' . $field_name . '" id="' . $field_id . '">';
                if (!empty($value)) {
                    $output .= '<div class="current-file" style="margin-top: 5px;">';
                    $output .= '<small class="text-muted">Current: ' . htmlspecialchars($value) . '</small>';
                    $output .= '</div>';
                }
                break;
                
            default:
                $output = '<input type="text" class="form-control" name="' . $field_name . '" id="' . $field_id . '" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
        }
        
        return $output;
    }
}

/**
 * Render repeater field for display (non-editable)
 */
if (!function_exists('render_repeater_field_display')) {
    function render_repeater_field_display($field, $values)
    {
        if (empty($values)) {
            return '';
        }
        
        $output = '<div class="repeater-field-display">';
        $output .= '<strong>' . htmlspecialchars($field['name']) . ':</strong>';
        $output .= '<ul class="list-unstyled" style="margin-top: 5px;">';
        
        foreach ($values as $value) {
            if (!empty($value)) {
                $output .= '<li>• ' . htmlspecialchars($value) . '</li>';
            }
        }
        
        $output .= '</ul>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Initialize repeater field JavaScript
 */
if (!function_exists('init_repeater_fields_js')) {
    function init_repeater_fields_js()
    {
        return '
        <script>
        $(document).ready(function() {
            // Add repeater field
            $(document).on("click", ".add-repeater-field", function(e) {
                e.preventDefault();
                var fieldId = $(this).data("field-id");
                var container = $(".repeater-field-container[data-field-id=\"" + fieldId + "\"]");
                var template = container.find(".repeater-field-template").html();
                var itemCount = container.find(".repeater-field-item").not(".repeater-field-template .repeater-field-item").length;
                
                // Replace __INDEX__ with actual index
                template = template.replace(/__INDEX__/g, itemCount);
                
                // Insert before template
                container.find(".repeater-field-template").before(template);
                
                // Initialize selectpicker if exists
                container.find(".selectpicker").selectpicker("refresh");
            });
            
            // Remove repeater field
            $(document).on("click", ".remove-repeater-field", function(e) {
                e.preventDefault();
                $(this).closest(".repeater-field-item").remove();
            });
        });
        </script>';
    }
}

/**
 * Get repeater field values for processing
 */
if (!function_exists('process_repeater_field_values')) {
    function process_repeater_field_values($field_slug, $post_data)
    {
        if (!isset($post_data['custom_fields'][$field_slug])) {
            return array();
        }
        
        $values = $post_data['custom_fields'][$field_slug];
        
        // Remove empty values
        $values = array_filter($values, function($value) {
            return !empty(trim($value));
        });
        
        return array_values($values); // Re-index array
    }
}