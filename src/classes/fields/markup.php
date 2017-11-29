<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Base\field;

/**
 * the markup field class.
 * this is not a value
 */
class markup extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element value
   */
  public function render_field(form $form) {
    $output = $this->value;
    return $output;
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
   * @return boolean this is not a value
   */
  public function is_a_value(){
    return FALSE;
  }
}
