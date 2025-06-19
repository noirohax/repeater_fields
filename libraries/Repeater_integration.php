<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Repeater_integration
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('repeater_fields_model');
    }

    /**
     * Hook into form processing to handle repeater fields
     */
    public function process_form_submission()
    {
        // Get form data
        $post_data = $this->CI->input->post();
        
        // Check if we have repeater field data
        foreach ($post_data as $key => $value) {
            if (strpos($key, 'repeater_field_') === 0) {
                $field_id = str_replace('repeater_field_', '', $key);
                
                // Decode JSON values
                $values = json_decode($value, true);
                
                if (is_array($values)) {
                    // Get field info to determine where to save
                    $field = $this->CI->db->get_where(db_prefix() . 'customfields', array('id' => $field_id))->row_array();
                    
                    if ($field) {
                        // Get rel_id from URL or form data
                        $rel_id = $this->get_relation_id($field['fieldto']);
                        
                        if ($rel_id) {
                            $this->CI->repeater_fields_model->save_repeater_values(
                                $field_id, 
                                $rel_id, 
                                $field['fieldto'], 
                                $values
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Get relation ID based on context
     */
    private function get_relation_id($field_to)
    {
        // Try to get from URL first
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            return (int)$_GET['id'];
        }
        
        // Try to get from POST data
        $post_data = $this->CI->input->post();
        
        // Common ID field names based on field_to
        $id_fields = array(
            'contacts' => 'userid',
            'customers' => 'userid', 
            'leads' => 'leadid',
            'staff' => 'staffid',
            'projects' => 'id',
            'tasks' => 'id',
            'invoices' => 'id',
            'estimates' => 'id',
            'proposals' => 'id',
            'contracts' => 'id',
            'tickets' => 'ticketid'
        );
        
        if (isset($id_fields[$field_to]) && isset($post_data[$id_fields[$field_to]])) {
            return (int)$post_data[$id_fields[$field_to]];
        }
        
        // Fallback - try common ID field names
        $common_ids = array('id', 'userid', 'clientid', 'customer_id');
        foreach ($common_ids as $id_field) {
            if (isset($post_data[$id_field]) && is_numeric($post_data[$id_field])) {
                return (int)$post_data[$id_field];
            }
        }
        
        return false;
    }

    /**
     * Override custom fields rendering for repeater fields
     */
    public function render_custom_fields($field_to, $rel_id = false, $where = [], $items_pr_row = 1)
    {
        $this->CI->load->model('custom_fields_model');
        
        // Get custom fields
        $where['fieldto'] = $field_to;
        $custom_fields = $this->CI->custom_fields_model->get($where);
        
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
                    $repeater_values = $this->CI->repeater_fields_model->get_repeater_values($field['id'], $rel_id, $field_to);
                }
                
                if (empty($repeater_values)) {
                    $repeater_values = array('');
                }
                
                $custom_fields_html .= $this->render_repeater_field($field, $repeater_values, $field_to);
            } else {
                // Render normal field using Perfex's function
                $custom_fields_html .= render_custom_field($field, $value, $items_pr_row, $items_wrapper);
            }
            
            $i++;
        }

        return $custom_fields_html;
    }

    /**
     * Render repeater field HTML
     */
    private function render_repeater_field($field, $values, $field_to)
    {
        if (empty($values)) {
            $values = array('');
        }
        
        $field_id = $field['id'];
        $name_attr = 'custom_fields[' . $field['slug'] . ']';
        
        $output = '<div class="form-group" data-fieldid="' . $field['id'] . '" data-fieldto="' . $field_to . '">';
        $output .= '<label for="' . $field['slug'] . '" class="control-label">';
        $output .= $field['name'];
        if ($field['required'] == 1) {
            $output .= ' <small class="req text-danger">*</small>';
        }
        $output .= '</label>';
        
        $output .= '<div class="repeater-field-container" data-field-id="' . $field_id . '">';
        
        foreach ($values as $index => $val) {
            $output .= $this->render_single_repeater_field($field, $val, $index, $name_attr, $index == 0);
        }
        
        // Add template for new fields
        $output .= '<div class="repeater-field-template" data-field-id="' . $field_id . '" style="display: none !important;">';
        $output .= $this->render_single_repeater_field($field, '', '__INDEX__', $name_attr, false);
        $output .= '</div>';
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render single repeater field group
     */
    private function render_single_repeater_field($field, $value, $index, $name_attr, $is_first = false)
    {
        $output = '<div class="repeater-field-group" data-index="' . $index . '">';
        $output .= '<div class="row">';
        $output .= '<div class="col-md-10">';
        
        // Generate field input
        $field_input = $this->render_field_input($field, $value, $name_attr . '[' . $index . ']');
        $output .= $field_input;
        
        $output .= '</div>';
        $output .= '<div class="col-md-2">';
        $output .= '<div class="repeater-controls" style="margin-top: 0px;">';
        
        if ($is_first) {
            $output .= '<button type="button" class="btn btn-success btn-xs add-repeater-field" title="Add another field">';
            $output .= '<i class="fa fa-plus"></i>';
            $output .= '</button>';
        } else {
            $output .= '<button type="button" class="btn btn-danger btn-xs remove-repeater-field" title="Remove this field">';
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
     * Render field input based on type
     */
    private function render_field_input($field, $value = '', $name = '')
    {
        $type = $field['type'];
        $field_name = !empty($name) ? $name : 'custom_fields[' . $field['slug'] . ']';
        $required = isset($field['required']) && $field['required'] == 1 ? 'required' : '';
        $field_id = str_replace(['[', ']'], ['_', ''], $field_name);
        
        switch ($type) {
            case 'input':
                return '<input type="text" id="' . $field_id . '" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            case 'number':
                return '<input type="number" id="' . $field_id . '" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            case 'textarea':
                return '<textarea id="' . $field_id . '" class="form-control" name="' . $field_name . '" rows="4" ' . $required . '>' . htmlspecialchars($value) . '</textarea>';
                
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
                return '<select id="' . $field_id . '" class="form-control selectpicker" name="' . $field_name . '" ' . $required . '>' . $options . '</select>';
                
            case 'date_picker':
                return '<input type="text" id="' . $field_id . '" class="form-control datepicker" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            case 'datetime_picker':
                return '<input type="text" id="' . $field_id . '" class="form-control datetimepicker" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            case 'colorpicker':
                return '<input type="text" id="' . $field_id . '" class="form-control colorpicker" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            case 'link':
                return '<input type="url" id="' . $field_id . '" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                
            default:
                return '<input type="text" id="' . $field_id . '" class="form-control" name="' . $field_name . '" value="' . htmlspecialchars($value) . '" ' . $required . '>';
        }
    }
}