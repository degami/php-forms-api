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
* the single checkbox input field class
*/
class checkbox extends field {
  protected $text_position = 'after';

  /**
  * class constructor
  * @param array  $options build options
  * @param string $name    field name
  */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options,$name);
    $this->value = NULL;
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

    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $this->label_class .= " label-" . $this->get_element_class_name();
    $this->label_class = trim($this->label_class);
    $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";

    $output = "<label for=\"{$id}\"{$label_class}>".
    (($this->text_position == 'before') ? $this->get_text($this->title) : '');

    if($this->value == $this->default_value) {
      $this->attributes['checked'] = 'checked';
    }

    $tag = new tag_element([
      'tag' => 'input',
      'type' => 'checkbox',
      'id' => $id,
      'name' => $this->name,
      'value' => $this->default_value,
      'attributes' => $this->attributes,
    ]);
    $output .= $tag->render_tag();

    $output .= (($this->text_position != 'before') ? $this->get_text($this->title) : '')."</label>\n";
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
