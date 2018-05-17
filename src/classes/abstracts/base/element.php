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
use Degami\PHPFormsApi\Accessories\ordered_functions;
use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Abstracts\Base\fields_container;
use \Exception;

/**
 * base element class
 * every form element classes inherits from this class
 * @abstract
 */
abstract class element{

  use tools;

  /**
   * element name
   * @var string
   */
  protected $name = NULL;

  /**
   * element parent
   * @var element subclass
   */
  protected $parent = NULL;

  /**
   * element weight
   * @var integer
   */
  protected $weight = 0;

  /**
   * element container tag
   * @var string
   */
  protected $container_tag = FORMS_DEFAULT_FIELD_CONTAINER_TAG;

  /**
   * element container html class
   * @var string
   */
  protected $container_class = FORMS_DEFAULT_FIELD_CONTAINER_CLASS;

  /**
   * element label class
   * @var string
   */
  protected $label_class = FORMS_DEFAULT_FIELD_LABEL_CLASS;

  /**
   * element container inherits classes
   * @var boolean
   */
  protected $container_inherits_classes = FALSE;

  /**
   * element errors array
   * @var array
   */
  protected $notifications = [ 'error' => [], 'highlight'=>[] ];

  /**
   * element attributes array
   * @var array
   */
  protected $attributes = [];

  /**
   * element js array
   * @var array
   */
  protected $js = [];

  /**
   * element css array
   * @var array
   */
  protected $css = [];

  /**
   * element prefix
   * @var string
   */
  protected $prefix = '';

  /**
   * element suffix
   * @var string
   */
  protected $suffix = '';

  /**
   * element build options
   * @var null
   */
  protected $build_options = NULL;

  /**
   * element no translation flag. if true form::translate_string won't be applied
   * @var FALSE
   */
  protected $no_translation = FALSE;

  /**
   * returns initially build options
   * @return array build_options
   */
  public function get_build_options(){
    return $this->build_options;
  }

  /**
   * set name
   * @param string $name element name
   * @return element
   */
  public function set_name($name){
    $this->name = $name;

    return $this;
  }

  /**
   * get name
   * @return string element name
   */
  public function get_name(){
    return $this->name;
  }

  /**
   * set parent
   * @param element $parent element parent
   * @return element
   */
  public function set_parent(element $parent){
    $this->parent = $parent;

    return $this;
  }

  /**
   * get parent
   * @return element element parent
   */
  public function get_parent(){
    return $this->parent;
  }

  /**
   * get weight
   * @return int element weight
   */
  public function get_weight() {
    return $this->weight;
  }

  /**
   * add error
   * @param string $error_string           error string
   * @param string $validate_function_name validation function name
   * @return element
   */
  public function add_error($error_string,$validate_function_name){
    $this->notifications['error'][$validate_function_name] = $error_string;
    return $this;
  }

  /**
   * get defined errors
   * @return array errors
   */
  public function get_errors(){
    return $this->notifications['error'];
  }

  /**
   * check if element has errors
   * @return boolean there are errors
   */
  public function has_errors(){
    return count($this->get_errors()) > 0;
  }

  /**
   * set element errors
   * @param array $errors           errors array
   */
  public function set_errors($errors){
    $this->notifications['error'] = $errors;

    return $this;
  }

  /**
   * add highlight
   * @param string $highlight_string           highlight string
   * @param string $validate_function_name validation function name
   * @return element
   */
  public function add_highlight($highlight_string){
    $this->notifications['highlight'][] = $highlight_string;

    return $this;
  }

  /**
   * get defined highlights
   * @return array errors
   */
  public function get_highlights(){
    return $this->notifications['highlight'];
  }

  /**
   * check if element has highlights
   * @return boolean there are highlights
   */
  public function has_highlights(){
    return count($this->get_highlights()) > 0;
  }

  /**
   * set element highlights
   * @param array $highlights           highlights array
   * @return element
   */
  public function set_highlights($highlights){
    $this->notifications['highlight'] = $highlights;

    return $this;
  }


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

  public function get_element_class_name(){
    return strtolower( substr(get_class($this), strrpos(get_class($this), '\\') + 1) );
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
   * add js to element
   * @param string / array $js javascript to add
   * @return element
   */
  public function add_js($js){
    if( is_array($js) ){
      $js = array_filter(array_map( ['minify_js', $this], $js));
      $this->js = array_merge( $js, $this->js );
    } else if( is_string($js) && trim($js) != '' ) {
      $this->js[] = $this->minify_js($js);
    }

    return $this;
  }

  /**
   * minify js string
   * @param string $js javascript minify
   * @return string
   */
  public function minify_js($js){
    if( is_string($js) && trim($js) != '' ) {
      $js = trim(preg_replace("/\s+/"," ",str_replace("\n","","". $js )));
    }

    return $js;
  }

  /**
   * get the element's js array
   * @return array element's js array
   */
  public function &get_js(){
    if( $this instanceof fields_container || $this instanceof form ) {
      $js = array_filter(array_map('trim',$this->js));
      $fields = $this->get_fields();
      if( $this instanceof form ) $fields = $this->get_fields( $this->get_current_step() );
      foreach($fields as $field){
        $js = array_merge($js, $field->get_js());
      }
      return $js;
    }
    return $this->js;
  }


  /**
   * add css to element
   * @param string / array $css css to add
   * @return element
   */
  public function add_css($css){
    if( is_array($css) ){
      $css = array_filter(array_map('trim',$css));
      $this->css = array_merge( $css, $this->css );
    } else if( is_string($css) && trim($css) != '' ) {
      $this->css[] = trim($css);
    }

    return $this;
  }

  /**
   * get the element's css array
   * @return array element's css array
   */
  public function &get_css(){
    if( $this instanceof fields_container || $this instanceof form ) {
      $css = array_filter(array_map('trim',$this->css));
      foreach($this->get_fields() as $field){
        $css = array_merge($css, $field->get_css());
      }
      return $css;
    }
    return $this->css;
  }

  /**
   * get element prefix
   * @return string element prefix
   */
  public function get_prefix(){
    return $this->prefix;
  }

  /**
   * set element prefix
   * @param string $prefix element prefix
   */
  public function set_prefix($prefix){
    $this->prefix = $prefix;

    return $this;
  }

  /**
   * get element suffix
   * @return string element suffix
   */
  public function get_suffix(){
    return $this->suffix;
  }

  /**
   * set element suffix
   * @param string $suffix element suffix
   * @return element
   */
  public function set_suffix($suffix){
    $this->suffix = $suffix;

    return $this;
  }

  /**
   * get element container_tag
   * @return string element container_tag
   */
  public function get_container_tag(){
    return $this->container_tag;
  }

  /**
   * set element container_tag
   * @param string $container_tag element container_tag
   * @return element
   */
  public function set_container_tag($container_tag){
    $this->container_tag = $container_tag;

    return $this;
  }

  /**
   * get element container_class
   * @return string element container_class
   */
  public function get_container_class(){
    return $this->container_class;
  }

  /**
   * set element container_class
   * @param string $container_class element container_class
   * @return element
   */
  public function set_container_class($container_class){
    $this->container_class = $container_class;

    return $this;
  }

  /**
   * get element html prefix
   * @return string html for the element prefix
   */
  public function get_element_prefix(){
    if(!empty($this->container_tag)){

      if(preg_match("/<\/?(.*?)\s.*?(class=\"(.*?)\")?.*?>/i",$this->container_tag,$matches)){
        // if a <tag> is contained try to get tag and class
        $this->container_tag = $matches[1];
        $this->container_class = (!empty($this->container_class) ? $this->container_class : '') . (!empty($matches[3]) ? ' '.$matches[3] : '');
      }

      $class = $this->container_class;
      if( $this->container_inherits_classes && isset($this->attributes['class']) && !empty($this->attributes['class']) ){
        $class .= ' '.$this->attributes['class'].'-container';
      }else{
        if( method_exists($this, 'get_type') )
          $class .= ' '.$this->get_type().'-container';
      }
      if ($this->has_errors()) {
        $class .= ' has-errors';
      }
      $class = trim($class);
      return "<{$this->container_tag} class=\"{$class}\">";
    }
    return '';
  }

  /**
   * get element html suffix
   * @return string html for the element suffix
   */
  public function get_element_suffix(){
    if(!empty($this->container_tag)){
      return "</{$this->container_tag}>";
    }
    return '';
  }

  /**
   * to array
   * @return array array representation for the element properties
   */
  public function toArray(){
    $values = get_object_vars($this);
    foreach($values as $key => $val){
      $values[$key] = element::_toArray($key, $val);
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
        $elem[$k] = element::_toArray($k, $val, $path.$key.'/');
      }
    }
    return $elem;
  }

  protected static function search_field_by_id( $container, $fieldid ){
    /** @var field $container */
    if( $container instanceof fields_container || $container instanceof form ){
      $fields = ($container instanceof form) ? $container->get_fields( $container->get_current_step() ) : $container->get_fields();
      foreach ($fields as $key => $field) {
        /** @var field $field */
        if( $field->get_html_id() == $fieldid ) {
          return $field;
        } elseif( $field instanceof fields_container ) {
          $out = element::search_field_by_id( $field, $fieldid );
          if( $out != NULL ) return $out;
        }
      }
    }elseif( $container->get_html_id() == $fieldid ){
      // not a container
      return $container;
    }
    return NULL;
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
