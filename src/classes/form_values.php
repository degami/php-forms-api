<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi;
use \Iterator;
use \IteratorAggregate;
use \ArrayIterator;
use \ArrayAccess;

/**
 * a class to hold form fields submitted values
 */
class form_values implements IteratorAggregate, ArrayAccess{
  private $values = array();

  public function __get($key){
    return isset($this->values[$key]) ? $this->values[$key] : NULL;
  }

  public function __set($key, $value){
    $this->values[$key] = $value;
    return $this;
  }

  public function __construct($values) {
    foreach( $values as $k => $v ){
      if( is_numeric($k) ) $k = '_value'.$k;
      $this->{$k} = (is_array($v)) ? new form_values($v) : $v;
    }
  }

  public function getIterator() {
    return new ArrayIterator($this);
  }

  public function keys(){
    return array_keys($this->values);
  }

  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->values[] = $value;
    } else {
      $this->values[$offset] = $value;
    }
  }

  public function offsetExists($offset) {
    return isset($this->values[$offset]);
  }

  public function offsetUnset($offset) {
    unset($this->values[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->values[$offset]) ? $this->values[$offset] : null;
  }

  public function toArray(){
    $out = array();
    foreach ($this->values as $key => $value) {
      $out[$key] = ( $value instanceof form_values ) ? $value->toArray() : $value;
    }
    return $out;
  }
}
