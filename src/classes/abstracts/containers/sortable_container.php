<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Containers;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Base\fields_container;

/**
 * an abstract sortable field container
 * @abstract
 */
abstract class sortable_container extends fields_container_multiple{

  /**
   * sort handle position (left/right)
   * @var string
   */
  protected $handle_position = 'left';

  /**
   * deltas array ( used for sorting )
   * @var array
   */
  protected $deltas = [];

  /**
   * get handle position (left/right)
   * @return string handle position
   */
  public function get_handle_position(){
    return $this->handle_position;
  }

  /**
   * return form elements values into this element
   * @return array form values
   */
  public function values() {
    $output = [];

    $fields_with_delta = $this->get_fields_with_delta();
    usort($fields_with_delta, [__CLASS__,'orderby_delta']);

    foreach ($fields_with_delta as $name => $info) {
      $field = $info['field'];
      if($field->is_a_value() == TRUE){
        $output[$name] = $field->values();
        if(is_array($output[$name]) && empty($output[$name])){
          unset($output[$name]);
        }
      }
    }
    return $output;
  }

  /**
   * process (set) the fields value
   * @param  mixed $values value to set
   */
  public function process($values) {
    foreach ($this->get_fields() as $name => $field) {
      $partitionindex = $this->get_partitionindex($field->get_name());

      if( $field instanceof fields_container ) $this->get_field($name)->process($values);
      else if(isset($values[$name])){
        $this->get_field($name)->process($values[$name]);
      }

      $this->deltas[$name]=isset($values[$this->get_html_id().'-delta-'.$partitionindex]) ? $values[$this->get_html_id().'-delta-'.$partitionindex] : 0;
    }
  }

  /**
   * get an array of fields with the relative delta (ordering) information
   * @return array fields with delta
   */
  private function get_fields_with_delta(){
    $out = [];
    foreach($this->get_fields() as $key => $field){
      $out[$key]=['field'=> $field,'delta'=>$this->deltas[$key]];
    }
    return $out;
  }

  /**
   * order elements by delta property
   * @param  array $a first element
   * @param  array $b second element
   * @return integer  order
   */
  private static function orderby_delta($a,$b){
    if($a['delta']==$b['delta']) return 0;
    return ($a['delta']>$b['delta']) ? 1:-1;
  }
}
