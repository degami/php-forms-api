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
 * the color input field class
 */
class color extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options, $name);
    if( !empty($this->default_value) && !$this->is_RGB($this->default_value)){
      $this->value = $this->default_value = '#000000';
    }
  }

  private function is_RGB($str){
    return preg_match("/^#?([a-f\d]{3}([a-f\d]{3})?)$/i", $str) ;
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
    $output = "<input type=\"color\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"".htmlspecialchars($this->value)."\"{$attributes} />\n";
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
