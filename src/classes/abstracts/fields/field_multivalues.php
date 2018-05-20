<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Abstracts\Base\field;
use Degami\PHPFormsApi\Fields\option;
use Degami\PHPFormsApi\Fields\optgroup;
use \Traversable;

/**
 * the multivalues field class (a select, a radios or a checkboxes group)
 * @abstract
 */
abstract class field_multivalues extends field {

  /**
   * options array
   * @var array
   */
  protected $options = [];

  /**
   * get elements options array by reference
   * @return array element options
   */
  public function &get_options(){
    return $this->options;
  }

  public static function is_foreacheable($var){
    return (is_array($var) || ($var instanceof Traversable)); 
  }

  /**
   * check if key is present into haystack
   * @param  mixed  $needle   element to find
   * @param  array  $haystack where to find it
   * @return boolean           TRUE if element is found
   */
  public static function has_key($needle, $haystack) {
    foreach ($haystack as $key => $value) {
      if($value instanceof option){
        if($value->get_key() == $needle) return TRUE;
      }else if($value instanceof optgroup){
        if($value->options_has_key($needle) == TRUE) return TRUE;
      }else if ($needle == $key) {
        return TRUE;
      } else if( field_multivalues::is_foreacheable($value) && field_multivalues::has_key($needle, $value) == TRUE ) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * check if key is present into element options
   * @param  mixed $needle element to find
   * @return bookean         TRUE if element is found
   */
  public function options_has_key($needle){
    return field_multivalues::has_key($needle,$this->options);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid(){
    $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;

    if(!is_array($this->value) && !empty($this->value)){
      $check = $this->options_has_key($this->value);
      $this->add_error(str_replace("%t",$titlestr, $this->get_text("%t: Invalid choice")).serialize( [ $this->value, $this->options_has_key($this->value) ] ),__FUNCTION__);

      if(!$check) return FALSE;
    }else if(field_multivalues::is_foreacheable($this->value)){
      $check = TRUE;
      foreach ($this->value as $key => $value) {
        $check &= $this->options_has_key($value);
      }
      if(!$check) {
        $this->add_error(str_replace("%t",$titlestr, $this->get_text("%t: Invalid choice")),__FUNCTION__);

        if($this->stop_on_first_error)
          return FALSE;
      }
    }
    return parent::valid();
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}
