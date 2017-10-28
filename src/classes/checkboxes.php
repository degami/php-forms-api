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
 * the checkboxes group field class
 */
class checkboxes extends field_multivalues {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if(!is_array($this->default_value)) {
      $this->default_value = array($this->default_value);
    }

    $output = '<div class="options">';
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    foreach ($this->options as $key => $value) {
      $attributes = $this->get_attributes();
      if( $value instanceof checkbox ){
        $value->set_name("{$this->name}".(count($this->options)>1 ? "[]":""));
        $value->set_id("{$this->name}-{$key}");
        $output .= $value->render($form);
      }else{
        if(is_array($value) && isset($value['attributes'])){
          $attributes = $this->get_attributes_string($value['attributes'],array('type','name','id','value'));
        }
        if(is_array($value)){
          $value = $value['value'];
        }

        $checked = (is_array($this->default_value) && in_array($key, $this->default_value)) ? ' checked="checked"' : '';
        $output .= "<label class=\"label-checkbox\" for=\"{$id}-{$key}\"><input type=\"checkbox\" id=\"{$id}-{$key}\" name=\"{$this->name}".(count($this->options)>1 ? "[]" : "")."\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
      }
    }
    $output .= '</div>';
    return $output;
  }
}
