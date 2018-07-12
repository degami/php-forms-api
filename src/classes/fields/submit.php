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
use Degami\PHPFormsApi\Abstracts\Fields\clickable;

/**
 * the submit input type field class
 */
class submit extends clickable {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Submit';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    $tag = new tag_element([
      'tag' => 'input',
      'type' => 'submit',
      'id' => $id,
      'name' => $this->name,
      'value' => $this->get_text($this->value),
      'attributes' => $this->attributes,
    ]);
    return $tag;       
  }

}
