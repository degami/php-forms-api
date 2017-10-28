<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

/**
 * the option element class
 */
class option extends element{

  /**
   * option label
   * @var string
   */
  protected $label;

  /**
   * option key
   * @var string
   */
  protected $key;

  /**
   * class constructor
   * @param string $key     key
   * @param string $label   label
   * @param array  $options build options
   */
  function __construct($key, $label, $options = array()) {
    $this->key = trim($key);
    $this->label = $label;

    foreach ($options as $key => $value) {
      $key = trim($key);
      if( property_exists(get_class($this), $key) )
        $this->$key = $value;
    }
  }

  /**
   * render the option
   * @param  select $form_field select field
   * @return string        the option html
   */
  public function render(select $form_field){
    $this->no_translation = $form_field->no_translation;
    $selected = '';
    $field_value = $form_field->get_value();
    if(is_array($field_value) || $form_field->is_multiple() == TRUE){
      if( !is_array($field_value) ) $field_value = array($field_value);
      $selected = in_array($this->key, array_values($field_value), TRUE) ? ' selected="selected"' : '';
    }else{
      $selected = ($this->key === $field_value) ? ' selected="selected"' : '';
    }
    $attributes = $this->get_attributes(array('value','selected'));
    $output = "<option value=\"{$this->key}\"{$selected}{$attributes}>".$this->get_text($this->label)."</option>\n";
    return $output;
  }

  /**
   * get the element key
   * @return mixed the element key
   */
  public function get_key(){
    return $this->key;
  }

  /**
   * set the element key
   * @param  mixed $label element key
   */
  public function set_key($key){
    $this->key = $key;

    return $this;
  }

  /**
   * get the element label
   * @return mixed the element label
   */
  public function get_label(){
    return $this->label;
  }

  /**
   * set the element label
   * @param  mixed $label element label
   */
  public function set_label($label){
    $this->label = $label;

    return $this;
  }



}
