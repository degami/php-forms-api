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
 * the value field class
 * this field is not rendered as part of the form, but the value is passed on form submission
 */
class value extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    $this->container_tag = '';
    $this->container_class = '';
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        an empty string
   */
  public function render_field(form $form) {
    return '';
  }

  /**
   * validate function
   * @return boolean this field is always valid
   */
  public function valid() {
    return TRUE;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}
