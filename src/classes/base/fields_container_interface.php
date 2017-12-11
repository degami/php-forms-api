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

/**
 * fields container interface
 */
interface fields_container_interface {

  /**
   * add field to form
   * @param string  $name  field name
   * @param mixed   $field field to add, can be an array or a field subclass
   */
  public function add_field($name, $field);

  /**
   * remove field from form
   * @param  string $field field name
   */
  public function remove_field($name);

  /**
   * on_add_return overload
   * @return string 'this'
   */
  public function on_add_return();

}
