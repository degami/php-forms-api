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
* the number input field class
*/
class number extends field {
  /**
  * minimum value
  * @var null
  */
  protected $min = NULL;

  /**
  * maximum value
  * @var null
  */
  protected $max = NULL;

  /**
  * step value
  * @var integer
  */
  protected $step = 1;

  /**
  * class constructor
  * @param array  $options build options
  * @param string $name    field name
  */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options, $name);

  // ensure is numeric validator is present
    $this->get_validate()->add_element('numeric');
  }

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
      'type' => 'number',
      'id' => $id,
      'name' => $this->name,
      'value' => $this->value,
      'attributes' => $this->attributes,
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
