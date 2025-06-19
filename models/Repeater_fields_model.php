<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Repeater_fields_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Save repeater field values
     */
    public function save_repeater_values($fieldid, $relid, $fieldto, $values)
    {
        // First, delete existing values for this field
        $this->db->where('fieldid', $fieldid);
        $this->db->where('relid', $relid);
        $this->db->where('fieldto', $fieldto);
        $this->db->delete(db_prefix() . 'customfields_repeater_values');

        // Insert new values
        if (is_array($values)) {
            foreach ($values as $index => $value) {
                if (!empty($value)) {
                    $this->db->insert(db_prefix() . 'customfields_repeater_values', array(
                        'fieldid' => $fieldid,
                        'relid' => $relid,
                        'fieldto' => $fieldto,
                        'value' => $value,
                        'field_index' => $index
                    ));
                }
            }
        }
        
        return true;
    }

    /**
     * Get repeater field values
     */
    public function get_repeater_values($fieldid, $relid, $fieldto)
    {
        $this->db->where('fieldid', $fieldid);
        $this->db->where('relid', $relid);
        $this->db->where('fieldto', $fieldto);
        $this->db->order_by('field_index', 'ASC');
        
        $result = $this->db->get(db_prefix() . 'customfields_repeater_values')->result_array();
        
        $values = array();
        foreach ($result as $row) {
            $values[] = $row['value'];
        }
        
        return $values;
    }

    /**
     * Get all repeater fields
     */
    public function get_repeater_fields()
    {
        $this->db->where('is_repeater', 1);
        return $this->db->get(db_prefix() . 'customfields')->result_array();
    }

    /**
     * Check if field is repeater
     */
    public function is_repeater_field($fieldid)
    {
        $this->db->where('id', $fieldid);
        $this->db->where('is_repeater', 1);
        $result = $this->db->get(db_prefix() . 'customfields')->row();
        
        return $result ? true : false;
    }
}
