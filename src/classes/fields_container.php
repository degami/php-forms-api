<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi;
use \Exception;

/**
 * a field that contains other fields class
 * @abstract
 */
abstract class fields_container extends field {

  /**
   * keeps fields insert order
   * @var array
   */
  protected $insert_field_order = array();

  /**
   * element fields
   * @var array
   */
  protected $fields = array();

  /**
   * get the fields array by reference
   * @return array        the array of field elements
   */
  public function &get_fields(){
    return $this->fields;
  }

  /**
   * get the form fields by type
   * @param  array $field_types field types
   * @return array              fields in the element
   */
  public function get_fields_by_type($field_types){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof fields_container){
        $out = array_merge($out, $field->get_fields_by_type($field_types));
      }else{
        if($field instanceof field && in_array($field->get_type(), $field_types)) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  /**
   * get the step fields by type and name
   * @param  array $field_types field types
   * @param  string $name       field name
   * @return array              fields in the element matching the search criteria
   */
  public function get_fields_by_type_and_name($field_types,$name){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof fields_container){
        $out = array_merge($out, $field->get_fields_by_type_and_name($field_types,$name));
      }else{
        if($field instanceof field && in_array($field->get_type(), $field_types) && $field->get_name() == $name) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  /**
   * get field by name
   * @param  string  $field_name field name
   * @return element subclass field object
   */
  public function get_field($field_name){
    return isset($this->fields[$field_name]) ? $this->fields[$field_name] : NULL;
  }

  /**
   * add field to form
   * @param string  $name  field name
   * @param mixed   $field field to add, can be an array or a field subclass
   */
  public function add_field($name, $field) {
    if (!is_object($field)) {
      $field_type = "Degami\\PHPFormsApi\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      if(!class_exists($field_type)){
        throw new Exception("Error adding field. Class $field_type not found", 1);
      }
      $field = new $field_type($field, $name);
    }else{
      $field->set_name($name);
    }

    $field->set_parent($this);

    $this->fields[$name] = $field;
    $this->insert_field_order[] = $name;

    if( !method_exists($field, 'on_add_return') ) {
      if(  $field instanceof fields_container && !( $field instanceof datetime || $field instanceof geolocation ) )
        return $field;
      return $this;
    }
    if($field->on_add_return() == 'this') return $field;
    return $this;
  }

  /**
   * remove field from form
   * @param  string $field field name
   */
  public function remove_field($name){
    unset($this->fields[$name]);
    if(($key = array_search($name, $this->insert_field_order)) !== false) {
      unset($this->insert_field_order[$key]);
    }
    return $this;
  }

  /**
   * return form elements values into this element
   * @return array form values
   */
  public function values() {
    $output = array();
    foreach ($this->get_fields() as $name => $field) {
      if($field->is_a_value() == TRUE){
        $output[$name] = $field->values();
        if(is_array($output[$name]) && empty($output[$name])){
          unset($output[$name]);
        }
      }
    }
    return $output;
  }

  /**
   * preprocess hook
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    foreach ($this->get_fields() as $field) {
      $field->preprocess($process_type);
    }
  }

  /**
   * process (set) the fields value
   * @param  mixed $values value to set
   */
  public function process($values) {
    foreach ($this->get_fields() as $name => $field) {
      if( $field instanceof fields_container ) {
        $this->get_field($name)->process($values);
      } else if ( preg_match_all('/(.*?)(\[(.*?)\])+/i',$name, $matches, PREG_SET_ORDER) ) {
        if(isset($values[ $matches[0][1] ])){
          $value = $values[ $matches[0][1] ];
          foreach($matches as $match){
            if(isset($value[ $match[3] ])){
              $value = $value[ $match[3] ];
            }
          }
        }
        $field->process($value);
      }else if(isset($values[$name])){
        $this->get_field($name)->process($values[$name]);
      } else if( $field instanceof checkbox ){
        // no value on request[name] && field is a checkbox - process anyway with an empty value
        $this->get_field($name)->process(NULL);
      } else if( $field instanceof select ){
        if($field->is_multiple()) $this->get_field($name)->process(array());
        else $this->get_field($name)->process(NULL);
      } else if( $field instanceof field_multivalues ){
        // no value on request[name] && field is a multivalue (eg. checkboxes ?) - process anyway with an empty value
        $this->get_field($name)->process(array());
      }
    }
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    foreach ($this->get_fields() as $name => $field) {
      if( is_object($field) && method_exists ( $field , 'pre_render' ) ){
        $field->pre_render($form);
      }
    }
    parent::pre_render($form);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    $valid = TRUE;
    foreach ($this->get_fields() as $field) {
      if (!$field->valid()) {
        // not returnig FALSE to let all the fields to be validated
        $valid = FALSE;
      }
    }
    return $valid;
  }

  /**
   * renders form errors
   * @return string errors as an html <li> list
   */
  public function show_errors() {
    $output = "";
    foreach ($this->get_fields() as $field) {
      $output .= $field->show_errors();
    }
    return $output;
  }

  /**
   * resets the fields
   */
  public function reset() {
    foreach ($this->get_fields() as $field) {
      $field->reset();
    }
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }

  /**
   * alter_request hook
   * @param array $request request array
   */
  public function alter_request(&$request){
    foreach($this->get_fields() as $field){
      $field->alter_request($request);
    }
  }

  /**
   * after_validate hook
   * @param  form $form form object
   */
  public function after_validate(form $form){
    foreach($this->get_fields() as $field){
      $field->after_validate($form);
    }
  }

  /**
   * on_add_return overload
   * @return string 'this'
   */
  protected function on_add_return(){
    return 'this';
  }
}
