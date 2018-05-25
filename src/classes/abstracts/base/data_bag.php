<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                      BASE                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Base;

use \Iterator;
use \IteratorAggregate;
use \ArrayIterator;
use \ArrayAccess;
use \Countable;

/**
 * a class to hold form fields submitted values
 */

abstract class data_bag  implements Iterator, ArrayAccess, Countable{

  /**
   * current position
   * @var integer
   */
  protected $position = -1;

  /**
   * data to be stored
   * @var array
   */
  protected $data = [];

  public function __construct($data) {
    $this->position = -1;
    $this->set($data);
  }

  public function set( $data ){
    foreach( $data as $k => $v ){
      if( is_numeric($k) ) $k = '_value'.$k;
      $this->{$k} = $v;
    }
    return $this;
  }

  public function unset( $key ){
    $this->offsetUnset($key);
    return $this;
  }

  public function isset( $key ){
    return $this->offsetExists( $key );
  }

  /**
   * rewind pointer position
   */
  public function rewind() {
    $this->position = 0;
  }

  /**
   * get data keys
   */
  private function get_keys(){
    return array_keys( $this->data );
  }

  /**
   * get current element
   * @return mixed current element
   */
  public function current() {
    $keys = $this->get_keys();
    if(!isset($keys[$this->position])) return FALSE;
    return $this->data[ $keys[$this->position] ];
  }

  /**
   * get current position
   * @return integer position
   */
  public function key() {
    $keys = $this->get_keys();
    return $keys[ $this->position ];
  }

  /**
   * increment current position
   */
  public function next() {
    ++$this->position;
  }

  /**
   * check if current position is valud
   * @return boolean current position is valid
   */
  public function valid() {
    $keys = $this->get_keys();
    if(!isset($keys[$this->position])) return FALSE;
    return isset($this->data[ $keys[$this->position] ]);
  }

  public function __get($key){
    return isset($this->data[$key]) ? $this->data[$key] : NULL;
  }

  public function __set($key, $value){
    $this->data[$key] = (is_array($value)) ? new static($value) : $value;
    return $this;
  }

  public function getIterator() {
    return new ArrayIterator($this);
  }

  public function keys(){
    return $this->get_keys();
  }

  public function offsetSet($offset, $value) {
    $this->{$offset} = $value;
  }

  public function offsetExists($offset) {
    return isset($this->data[$offset]);
  }

  public function offsetUnset($offset) {
    unset($this->data[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->data[$offset]) ? $this->data[$offset] : null;
  }

  public function toArray(){
    $out = [];
    foreach ($this->data as $key => $value) {
      $out[$key] = ( $value instanceof data_bag ) ? $value->toArray() : $value;
    }
    return $out;
  }

  public function only(array $keys){
    $out = [];
    if( empty($keys) ) return $this->toArray();
    foreach( $this->toArray() as $k => $v ){
      if( in_array($k, $keys) ){
        $out[$k] = $v;
      }
    }
    return $out;
  } 

  public function count(){
    return count($this->data);
  }
}