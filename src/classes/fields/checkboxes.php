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
use Degami\PHPFormsApi\Accessories\tag_element;
use Degami\PHPFormsApi\Abstracts\Fields\field_multivalues;

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
      $this->default_value = [ $this->default_value ];
    }

    $output = '<div class="options">';
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    foreach ($this->options as $key => $value) {
      if( $value instanceof checkbox ){
        $value->set_name("{$this->name}".(count($this->options)>1 ? "[]":""));
        $value->set_id("{$this->name}-{$key}");
        $output .= $value->render($form);
      }else{
        if(is_array($value) && isset($value['attributes'])){
          $attributes = $value['attributes'];
        } else {
          $attributes = [];
        } 
        if(is_array($value)){
          $value = $value['value'];
        }

        $output .= "<label class=\"label-checkbox\" for=\"{$id}-{$key}\">";
        $tag = new tag_element([
          'tag' => 'input',
          'type' => 'checkbox',
          'id' => "{$id}-{$key}",
          'name' => "{$this->name}".(count($this->options)>1 ? "[]" : ""),
          'value' => $key,
          'attributes' => array_merge($attributes, (is_array($this->default_value) && in_array($key, $this->default_value)) ? ['checked' => 'checked'] : []),
          'text' => $value,
        ]);
        $output .= $tag->render_tag();
        $output .= "</label>\n";
      }
    }
    $output .= '</div>';
    return $output;
  }
}
