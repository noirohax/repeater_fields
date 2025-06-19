<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Repeater_fields extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('repeater_fields_model');
    }

    /**
     * AJAX endpoint to save repeater data
     */
    public function save_repeater_data()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $relid = $this->input->post('relid');
        $fieldto = $this->input->post('fieldto');
        
        if (!$relid || !$fieldto) {
            echo json_encode(array('success' => false, 'message' => 'Missing required parameters'));
            return;
        }
        
        // Get all repeater fields
        $repeater_fields = $this->repeater_fields_model->get_repeater_fields();
        
        foreach ($repeater_fields as $field) {
            $field_key = 'repeater_field_' . $field['id'];
            if ($this->input->post($field_key)) {
                $values = json_decode($this->input->post($field_key), true);
                if (is_array($values)) {
                    $this->repeater_fields_model->save_repeater_values(
                        $field['id'],
                        $relid,
                        $fieldto,
                        $values
                    );
                }
            }
        }

        echo json_encode(array('success' => true));
    }

    /**
     * AJAX endpoint to get repeater values
     */
    public function get_repeater_values()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fieldid = $this->input->get('fieldid');
        $relid = $this->input->get('relid');
        $fieldto = $this->input->get('fieldto');

        if (!$fieldid || !$relid || !$fieldto) {
            echo json_encode(array('values' => array()));
            return;
        }

        $values = $this->repeater_fields_model->get_repeater_values($fieldid, $relid, $fieldto);
        echo json_encode(array('values' => $values));
    }
}