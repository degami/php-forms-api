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
 * the button field class
 */
class button extends clickable {

  /**
   * element label
   * @var string
   */
  protected $label;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL){
    parent::__construct($options,$name);
    if(empty($this->label)) $this->label = $this->value;
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    $tag = new tag_element([
      'tag' => 'button',
      'id' => $id,
      'name' => $this->name,
      'value' => $this->value,
      'text' => $this->get_text($this->label),
      'attributes' => $this->attributes,
      'has_close' => TRUE,
    ]);
    return $tag;    
  }

}
