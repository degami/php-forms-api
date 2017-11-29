<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Accessories;
use \Iterator;

/**
 * class for maintaining ordered list of functions
 */
class ordered_functions implements Iterator{

  /**
   * current position
   * @var integer
   */
  private $position = 0;

  /**
   * iterable elements
   * @var array
   */
  private $array = [];

  /**
   * sort function name
   * @var null
   */
  private $sort_callback = NULL;

  /**
   * [class constructor
   * @param array  $array         initially contained elements
   * @param string $type          type of elements
   * @param string $sort_callback sort callback name
   */
  public function __construct(array $array, $type, $sort_callback = NULL) {
    $this->position = 0;
    $this->array = $array;
    $this->type = $type;
    $this->sort_callback = $sort_callback;
    $this->sort();
  }

  /**
   * sort elements
   */
  function sort(){
    // $this->array = array_filter( array_map('trim', $this->array) );
    // $this->array = array_unique( array_map('strtolower', $this->array) );

    $tmparr = [];
    foreach ($this->array as &$value) {
      if(is_string($value)){
        $value = strtolower(trim($value));
      }else if(is_array($value) && isset($value[$this->type])){
        $value[$this->type] = strtolower(trim($value[$this->type]));
      }
    }

    $this->array = array_unique($this->array,SORT_REGULAR);

    if(!empty($this->sort_callback) && is_callable($this->sort_callback)){
      usort($this->array, $this->sort_callback);
    }
  }

  /**
   * rewind pointer position
   */
  function rewind() {
    $this->position = 0;
    $this->sort();
  }

  /**
   * get current element
   * @return mixed current element
   */
  function current() {
    return $this->array[$this->position];
  }

  /**
   * get current position
   * @return integer position
   */
  function key() {
    return $this->position;
  }

  /**
   * increment current position
   */
  function next() {
    ++$this->position;
  }

  /**
   * check if current position is valud
   * @return boolean current position is valid
   */
  function valid() {
    return isset($this->array[$this->position]);
  }

  /**
   * check if element is present
   * @param  mixed  $value value to search
   * @return boolean       TRUE if $value was found
   */
  public function has_value($value){
    // return in_array($value, $this->array);
    return in_array($value, $this->values());
  }

  /**
   * check if key is in the array keys
   * @param  integer  $key key to search
   * @return boolean       TRUE if key was found
   */
  public function has_key($key){
    return in_array($key, array_keys($this->array));
  }

  /**
   * return element values
   * @return array element values
   */
  public function values(){
    // return array_values($this->array);
    $out = [];
    foreach ($this->array as $key => $value) {
      if(is_array($value) && isset($value[$this->type])){
        $out[] = $value[$this->type];
      }else{
        $out[] = $value;
      }
    }
    return $out;
  }

  /**
   * return element keys
   * @return array element keys
   */
  public function keys(){
    return array_keys($this->array);
  }

  /**
   * adds a new element to array elements
   * @param mixed $value element to add
   */
  public function add_element($value){
    $this->array[] = $value;
    $this->sort();
  }

  /**
   * removes an element from array elements
   * @param  mixed $value element to remove
   */
  public function remove_element($value){
    $this->array = array_diff($this->array, [$value]);
    $this->sort();
  }

  /**
   * element to array
   * @return array element to array
   */
  public function toArray(){
    return $this->array;
  }
}
