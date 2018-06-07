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

/**
 * the range input field class
 */
class range extends number {

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

    $this->attributes['size'] = $this->size;
    if( is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min ){
      $this->attributes += [
        'size' => $this->size,
        'min' => $this->min,
        'max' => $this->max,
        'step' => $this->step
      ];
    }

    $tag = new tag_element([
      'tag' => 'input',
      'type' => 'range',
      'id' => $id,
      'name' => $this->name,
      'value' => $this->value,
      'attributes' => $this->attributes,
    ]);
    return $tag->render_tag();   
  }

}
