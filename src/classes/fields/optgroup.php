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
use Degami\PHPFormsApi\Abstracts\Base\element;
use Degami\PHPFormsApi\Abstracts\Base\field;

/**
 * the optgroup element class
 */
class optgroup extends element{

  /**
   * options array
   * @var array
   */
  protected $options;

  /**
   * element label
   * @var string
   */
  protected $label;

  /**
   * class constructor
   * @param string $label   label
   * @param array  $options options array
   */
  function __construct($label, $options) {
    $this->label = $label;

    if(isset($options['options'])){
      foreach ($options['options'] as $key => $value) {
        if($value instanceof option) {
          $this->add_option($value);
          $value->set_parent($this);
        } else {
          $this->add_option( new option($key , $value) );
        }
      }
      unset($options['options']);
    }

    foreach ($options as $key => $value) {
      $key = trim($key);
      if( property_exists(get_class($this), $key) )
        $this->{$key} = $value;
    }
  }

  /**
   * check if key is present into element options array
   * @param  mixed $needle element to find
   * @return boolean         TRUE if element is present
   */
  public function options_has_key($needle){
    return field_multivalues::has_key($needle,$this->options);
  }

  /**
   * add option
   * @param option $option option to add
   */
  public function add_option(option $option){
    $option->set_parent($this);
    $this->options[] = $option;
  }

  /**
   * render the optgroup
   * @param  select $form_field select field
   * @return string        the optgroup html
   */
  public function render(select $form_field){
    $this->no_translation = $form_field->no_translation;
    $attributes = $this->get_attributes(['label']);
    $output = "<optgroup label=\"".$this->get_text($this->label)."\"{$attributes}>\n";
    foreach ($this->options as $option) {
      $output .= $option->render($form_field);
    }
    $output .= "</optgroup>\n";
    return $output;
  }
}
