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
use Degami\PHPFormsApi\Containers\tag_container;

/**
 * the composed field class
 */
abstract class composed_field extends tag_container {
  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }

  /**
   * on_add_return overload
   * @return string 'parent'
   */
  public function on_add_return(){
    return 'parent';
  } 
}
