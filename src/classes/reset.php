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
 * the reset button field class
 */
class reset extends action {

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
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Reset';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"reset\" id=\"{$id}\" name=\"{$this->name}\" value=\"".$this->get_text($this->value)."\"{$attributes} />\n";
    return $output;
  }

}
