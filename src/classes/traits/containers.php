<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                     TRAITS                      ####
   ######################################################### */

namespace Degami\PHPFormsApi\Traits;

use Degami\PHPFormsApi\Base\field;
use Degami\PHPFormsApi\Base\fields_container;
use Degami\PHPFormsApi\Fields\datetime;
use Degami\PHPFormsApi\Fields\geolocation;
use \Exception;

/**
 * containers specific functions
 */
trait containers {

  /**
   * keeps fields insert order
   * @var array
   */
  protected $insert_field_order = [];

  /**
   * element fields
   * @var array
   */
  protected $fields = [];

  /**
   * get the fields array by reference
   * @return array        the array of field elements
   */
  public function &get_fields(){
    return $this->fields;
  }

  /**
   * get parent namespace
   * @return string  parent namespace
   */
  private function parentNameSpace(){
    $namespaceParts = explode('\\', __NAMESPACE__);
    return implode("\\",array_slice($namespaceParts,0,-1));
  }

  /**
   * returns a field object instance
   * @param string $name field name
   * @param mixed  $field field to add, can be an array or a field subclass
   * @return field instance
   */
  public function get_field_obj($name, $field){
    if (is_array($field)) {
      $field_type = $this->parentNameSpace() . "\\Fields\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      $container_type = $this->parentNameSpace() . "\\Containers\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      $base_type = $this->parentNameSpace() . "\\Base\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      $root_type = $this->parentNameSpace() . "\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      if(!class_exists($field_type) && !class_exists($container_type) && !class_exists($root_type) && !class_exists($base_type)){
        throw new Exception("Error adding field. Class \"$field_type\", \"$base_type\", \"$container_type\", \"$root_type\" not found", 1);
      }

      if( class_exists($field_type) ){
        $field = new $field_type($field, $name);
      } else if( class_exists($container_type) ) {
        $field = new $container_type($field, $name);
      } else if( class_exists($base_type) ) {
        $field = new $base_type($field, $name);
      } else {
        $field = new $root_type($field, $name);
      }
    }else if($field instanceof field){
      $field->set_name($name);
    }else{
      throw new Exception("Error adding field. Array or field subclass expected, ".gettype($field)." given", 1);
    }

    return $field;
  }

  /**
   * check if field is a field container
   * @param field $field field instance
   * @return boolean true if field is a field container
   */
  public function is_field_container( field $field){
      return $field instanceof fields_container && !( $field instanceof datetime || $field instanceof geolocation );
  }

}
