<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

/**
 * the "clickable" field element (a button or a submit )
 * @abstract
 */
abstract class clickable extends action{

  /**
   * "this element was clicked" flag
   * @var boolean
   */
  protected $clicked = FALSE;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
    $this->clicked = FALSE;
  }

  /**
   * check if this button was clicked
   * @return boolean if this element was clicked
   */
  public function get_clicked(){
    return $this->clicked;
  }

  /**
   * process hook
   * @param  mixed $value value to set
   */
  public function process($value){
    parent::process($value);
    $this->clicked = TRUE;
  }

  /**
   * reset this element
   */
  public function reset(){
    $this->clicked = FALSE;
    parent::reset();
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}
