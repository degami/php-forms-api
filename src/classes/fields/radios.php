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
      if(is_array($value) && isset($value['attributes'])){
        $attributes = $value['attributes'];
      } else {
        $attributes = [];
      }

      if(is_array($value)){
        $value = $value['value'];
      }

      $output .= "<label class=\"label-radio\" for=\"{$id}-{$key}\">";
      $tag = new tag_element([
        'tag' => 'input',
        'type' => 'radio',
        'id' => "{$id}-{$key}",
        'name' => $this->name,
        'value' => $key,
        'attributes' => array_merge($attributes, ($this->value == $key) ? ['checked' => 'checked'] : []),
        'text' => $value,
      ]);
      $output .= $tag->render_tag();
      $output .= "</label>\n";
    }
    $output .= '</div>';
    return $output;
  }
}
