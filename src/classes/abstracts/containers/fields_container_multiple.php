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
use Degami\PHPFormsApi\Abstracts\Base\fields_container;
use Degami\PHPFormsApi\Abstracts\Base\field;
use Degami\PHPFormsApi\Fields\datetime;
use Degami\PHPFormsApi\Fields\geolocation;
use Degami\PHPFormsApi\Traits\containers;
use \Exception;

/**
 * a field container subdivided in groups
 * @abstract
 */
abstract class fields_container_multiple extends fields_container{

  use containers;

  /**
   * element subelements
   * @var array
   */
  protected $partitions = [];

  /**
   * get element partitions
   * @return array partitions
   */
  public function &get_partitions(){
    return $this->partitions;
  }

  /**
   * get number of defined partitions
   * @return integer partitions number
   */
  public function num_partitions(){
    return count($this->partitions);
  }

  /**
   * add a new partition
   * @param string $title partition title
   */
  public function add_partition($title){
    $this->partitions[] = ['title'=>$title,'fieldnames'=>[]];

    return $this;
  }

  /**
   * add field to element
   * @param string  $name     field name
   * @param mixed   $field    field to add, can be an array or a field subclass
   * @param integer $partitions_index index of partition to add field to
   */
  public function add_field($name, $field, $partitions_index = 0) {

    $field = $this->get_field_obj($name, $field);
    $field->set_parent($this);

    $this->fields[$name] = $field;
    $this->insert_field_order[$partitions_index][] = $name;
    if(!isset($this->partitions[$partitions_index])){
      $this->partitions[$partitions_index] = ['title'=>'','fieldnames'=>[]];
    }
    $this->partitions[$partitions_index]['fieldnames'][] = $name;

    if( !method_exists($field, 'on_add_return') ) {
      if(  $this->is_field_container($field) )
        return $field;
      return $this;
    }
    if($field->on_add_return() == 'this') return $field;
    return $this;
  }

  /**
   * remove field from form
   * @param  string $field field name
   * @param  integer $partitions_index field partition
   */
  public function remove_field($name, $partitions_index = 0){
    unset($this->fields[$name]);
    if(($key = array_search($name, $this->insert_field_order[$partitions_index])) !== false) {
      unset($this->insert_field_order[$partitions_index][$key]);
    }
    if(($key = array_search($name, $this->partitions[$partitions_index]['fieldnames'])) !== false) {
      unset($this->partitions[$partitions_index]['fieldnames'][$key]);
    }
    return $this;
  }

  /**
   * get partition fields array
   * @param  integer $partitions_index partition index
   * @return array             partition fields array
   */
  public function &get_partition_fields($partitions_index){
    $out = [];
    $fieldsnames = $this->partitions[$partitions_index]['fieldnames'];
    foreach($fieldsnames as $name){
      $out[$name] = $this->get_field($name);
    }
    return $out;
  }

  /**
   * set partition fields array
   * @param  array $fields array of new fields to set for partition
   * @param  integer $partitions_index partition index
   */
  public function set_partition_fields( $fields, $partition_index = 0 ){
    $fieldsnames = $this->partitions[$partition_index]['fieldnames'];
    foreach($fieldsnames as $name){
      $this->remove_field($name, $partitions_index);
    }
    unset($this->partitions[$partition_index]['fieldnames']);
    $this->partitions[$partition_index]['fieldnames'] = [];
    foreach($fields as $name => $field){
      if( $field instanceof field ) $name = $field->get_name();
      $field = $this->get_field_obj($name, $field);
      $this->add_field( $field->get_name(), $field, $partition_index );
    }
    return $this;
  }

  /**
   * check if partition has errors
   * @param  integer $partitions_index partition index
   * @param  form $form form object
   * @return boolean           partition has errors
   */
  public function partition_has_errors($partitions_index, form $form){
    if( !$form->is_processed() ) return FALSE;
    $out = FALSE;
    foreach ($this->get_partition_fields($partitions_index) as $name => $field) {
      if( $out == TRUE ) continue;
      $out |= !$field->valid();
    }
    return $out;
  }

  /**
   * get partition index containint specified field name
   * @param  string $field_name field name
   * @return integer            partition index, -1 on failure
   */
  public function get_partitionindex($field_name){
    foreach($this->partitions as $partitions_index => $partition){
      if(in_array($field_name, $partition['fieldnames'])) return $partitions_index;
    }
    return -1;
  }

}