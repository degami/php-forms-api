<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                     BASE                        ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Base;

use Degami\PHPFormsApi\Traits\tools;
use Degami\PHPFormsApi\Abstracts\Base\element;
use Degami\PHPFormsApi\Accessories\ordered_functions;
use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Abstracts\Base\fields_container;
use \Exception;

/**
 * base element class
 * every form element classes inherits from this class
 * @abstract
 */
abstract class base_element{

  use tools;

  /**
   * element attributes array
   * @var array
   */
  protected $attributes = [];

  /**
   * set html attributes
   * @param string $name  attribute name
   * @param string $value attribute value
   * @return element
   */
  public function set_attribute($name,$value){
    $this->attributes[$name] = $value;

    return $this;
  }

  /**
   * set html attributes
   * @param array $attributes attributes array
   * @return element
   */
  public function set_attributes_array($attributes){
    $this->attributes = $attributes;

    return $this;
  }

  /**
   * get attribute value if present. FALSE on failure
   * @param  string $name attribute name
   * @return string       attribute description
   */
  public function get_attribute($name){
    return isset($this->attributes[$name]) ? $this->attributes[$name] : FALSE;
  }

  /**
   * returns the element html attributes string
   * @param  array  $reserved_arr array of attributes name that will be skipped if present in the attributes array
   * @return string               the html attributes string
   */
  public function get_attributes( $reserved_arr = ['type','name','id','value'] ){
    return $this->get_attributes_string($this->attributes, $reserved_arr);
  }

  /**
   * returns the html attributes string
   * @param  array $attributes_arr  attributes array
   * @param  array  $reserved_arr   array of attributes name that will be skipped if present in the attributes array
   * @return string                 the html attributes string
   */
  public function get_attributes_string( $attributes_arr, $reserved_arr = ['type','name','id','value'] ){
    $attributes = '';
    foreach ($reserved_arr as $key => $reserved) {
      if(isset($attributes_arr[$reserved])) unset($attributes_arr[$reserved]);
    }
    foreach ($attributes_arr as $key => $value) {
      if(!is_string($value) && !is_numeric($value)) continue;
      $value = form::process_plain($value);
      if(trim($value) != ''){
        $value=trim($value);
        $attributes .= " {$key}=\"{$value}\"";
      }
    }
    $attributes = trim($attributes);
    return empty($attributes) ? '' : ' ' . $attributes;
  }

  /**
   * get attributes array
   * @return array attributes array
   */
  public function get_attributes_array(){
    return $this->attributes;
  }

  /**
   * to array
   * @return array array representation for the element properties
   */
  public function toArray(){
    $values = get_object_vars($this);
    foreach($values as $key => $val){
      $values[$key] = static::_toArray($key, $val);
    }
    return $values;
  }

  /**
   * _toArray private method
   * @param  mixed  $key  key
   * @param  mixed  $elem element
   * @return array        element as an array
   */
  private static function _toArray($key, $elem, $path = '/'){
    if($key === 'parent'){
      return "-- link to parent --";
    }

    if( is_object($elem) && ($elem instanceof element ||  $elem instanceof ordered_functions) ){
      $elem = $elem->toArray();
    }else if(is_array($elem)){
      foreach($elem as $k => $val){
        $elem[$k] = static::_toArray($k, $val, $path.$key.'/');
      }
    }
    return $elem;
  }

  /**
   * Set/Get attribute wrapper
   *
   * @param   string $method
   * @param   array $args
   * @return  mixed
   */
  public function __call($method, $args){
      switch ( strtolower(substr($method, 0, 4)) ) {
          case 'get_' :
            $name = trim(strtolower(substr($method, 4)));
            if( property_exists(get_class($this), $name) ){
              return $this->{$name};
            }
          case 'set_' :
            $name = trim(strtolower(substr($method, 4)));
            $value = is_array($args) ? reset($args) : NULL;
            if( property_exists(get_class($this), $name) ){
              $this->{$name} = $value;
              return $this;
            }
      }
      throw new Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
  }

}
