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
* the email input field class
*/
class email extends field {
  /**
  * class constructor
  * @param array  $options build options
  * @param string $name    field name
  */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options, $name);

    // ensure is email validator is present
    $this->get_validate()->add_element('email');
  }

  /**
  * render_field hook
  * @param  form $form form object
  * @return string        the element html
  */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    $tag = new tag_element([
      'tag' => 'input',
      'type' => 'email',
      'id' => $id,
      'name' => $this->name,
      'value' => $this->value,
      'attributes' => $this->attributes + ['size' => $this->size],
    ]);
    return $tag;
  }

  /**
  * is_a_value hook
  * @return boolean this is a value
  */
  public function is_a_value(){
    return TRUE;
  }
}
