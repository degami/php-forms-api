<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                  FIELD INTERFACE                ####
   ######################################################### */

namespace Degami\PHPFormsApi\Interfaces;

/**
 * tag interface
 */
interface tag_interface {

   /**
   * add child to tag
   * @param mixed   $child tag to add, can be a tag object or a string
   */
  public function add_child($child);

  /**
   * render tag html
   * @return string tag html
   */
  public function render_tag();

}
