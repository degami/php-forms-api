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
 * the date field class
 */
class date extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    $this->default_value = date('Y-m-d');
    parent::__construct($options, $name);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    if( is_array($this->value) ) $this->value = '';
    $output = "<input type=\"date\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"".htmlspecialchars($this->value)."\"{$attributes} />\n";
    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}
