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
use Degami\PHPFormsApi\Abstracts\Base\field;

/**
 * the hidden input field class
 */
class hidden extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    $this->container_tag = '';
    $this->container_class = '';
    parent::__construct($options,$name);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();
    return "<input type=\"hidden\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}
