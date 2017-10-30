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
 * the radios group field class
 */
class radios extends field_multivalues {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '<div class="options">';
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    foreach ($this->options as $key => $value) {
      $attributes = $this->get_attributes();
      if(is_array($value) && isset($value['attributes'])){
        $attributes = $this->get_attributes_string($value['attributes'],['type','name','id','value']);
      }
      if(is_array($value)){
        $value = $value['value'];
      }

      $checked = ($this->value == $key) ? ' checked="checked"' : '';
      $output .= "<label class=\"label-radio\" for=\"{$id}-{$key}\"><input type=\"radio\" id=\"{$id}-{$key}\" name=\"{$this->name}\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
    }
    $output .= '</div>';
    return $output;
  }
}
