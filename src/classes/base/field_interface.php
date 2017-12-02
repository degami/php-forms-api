<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                  FIELD INTERFACE                ####
   ######################################################### */

namespace Degami\PHPFormsApi\Base;
use Degami\PHPFormsApi\form;

interface field_interface {

  /**
   * this function tells to the form if this element is a value that needs to be included into parent values() function call result
   * @return boolean include_me
   */
  public function is_a_value(); // tells if component value is passed on the parent values() function call

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form);

  /**
   * the function that actually renders the html field
   * @param  form $form form object
   * @return string        the field html
   */
  public function render_field(form $form); // renders html

  /**
   * process hook
   * @param  mixed $value value to set
   */
  public function process($value);

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid();

  /**
   * return form elements values into this element
   * @return array form values
   */
  public function values();
}
