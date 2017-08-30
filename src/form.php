<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/*
 *  Turn on error reporting during development
 */
// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);

namespace Degami\PHPFormsApi;

use \stdClass;
use \Exception;
use \Iterator;
use \IteratorAggregate;
use \ArrayIterator;
use \ArrayAccess;

/*
 *  PHP Forms API library configuration
 */

if( !defined('FORMS_DEFAULT_FORM_CONTAINER_TAG') ){
  define('FORMS_DEFAULT_FORM_CONTAINER_TAG', 'div');
}
if( !defined('FORMS_DEFAULT_FORM_CONTAINER_CLASS') ){
  define('FORMS_DEFAULT_FORM_CONTAINER_CLASS', 'form-container');
}
if( !defined('FORMS_DEFAULT_FIELD_CONTAINER_TAG') ){
  define('FORMS_DEFAULT_FIELD_CONTAINER_TAG', 'div');
}
if( !defined('FORMS_DEFAULT_FIELD_CONTAINER_CLASS') ){
  define('FORMS_DEFAULT_FIELD_CONTAINER_CLASS', 'form-item');
}
if( !defined('FORMS_DEFAULT_FIELD_LABEL_CLASS') ){
  define('FORMS_DEFAULT_FIELD_LABEL_CLASS', '');
}
if( !defined('FORMS_VALIDATE_EMAIL_DNS') ){
  define('FORMS_VALIDATE_EMAIL_DNS', TRUE);
}
if( !defined('FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS') ){
  define('FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS', 'mailinator.com|guerrillamail.com');
}
if( !defined('FORMS_BASE_PATH') ){
  define('FORMS_BASE_PATH', '');
}
if( !defined('FORMS_XSS_ALLOWED_TAGS') ){
  define('FORMS_XSS_ALLOWED_TAGS', 'a|em|strong|cite|code|ul|ol|li|dl|dt|dd');
}
if( !defined('FORMS_SESSION_TIMEOUT') ){
  define('FORMS_SESSION_TIMEOUT',7200);
}
if( !defined('FORMS_ERRORS_ICON') ){
  define('FORMS_ERRORS_ICON','<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>');
}
if( !defined('FORMS_ERRORS_TEMPLATE') ){
  define('FORMS_ERRORS_TEMPLATE','<div class="ui-state-error ui-corner-all errorsbox">'.FORMS_ERRORS_ICON.'<ul>%s</ul></div>');
}
if( !defined('FORMS_HIGHLIGHTS_ICON') ){
  define('FORMS_HIGHLIGHTS_ICON','<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>');
}
if( !defined('FORMS_HIGHLIGHTS_TEMPLATE') ){
  define('FORMS_HIGHLIGHTS_TEMPLATE','<div class="ui-state-highlight ui-corner-all highlightsbox">'.FORMS_HIGHLIGHTS_ICON.'<ul>%s</ul></div>');
}

if( ( function_exists('session_status') && session_status() != PHP_SESSION_NONE ) || session_id() != '') {
  ini_set('session.gc_maxlifetime',FORMS_SESSION_TIMEOUT);
  session_set_cookie_params(FORMS_SESSION_TIMEOUT);
}

/**
 * base element class
 * every form element classes inherits from this class
 * @abstract
 */
abstract class element{

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
  protected $notifications = array( 'error' => array(), 'highlight'=>array() );

  /**
   * element attributes array
   * @var array
   */
  protected $attributes = array();

  /**
   * element js array
   * @var array
   */
  protected $js = array();

  /**
   * element css array
   * @var array
   */
  protected $css = array();

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
  public function get_attributes($reserved_arr = array('type','name','id','value')){
    return $this->get_attributes_string($this->attributes, $reserved_arr);
  }

  public function get_element_class_name(){
    return strtolower( str_replace("Degami\\PHPFormsApi\\",'', get_class($this)) );
  }

  /**
   * returns the html attributes string
   * @param  array $attributes_arr  attributes array
   * @param  array  $reserved_arr   array of attributes name that will be skipped if present in the attributes array
   * @return string                 the html attributes string
   */
  public function get_attributes_string( $attributes_arr, $reserved_arr = array('type','name','id','value')){
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
      $js = array_filter(array_map( array('minify_js', $this), $js));
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
  private static function _toArray($key, $elem){
    if($key == 'parent'){
      return "";
    }
    if(is_object($elem) && ($elem instanceof element ||  $elem instanceof ordered_functions) ){
      $elem = $elem->toArray();
    }else if(is_array($elem)){
      foreach($elem as $k => $val){
        $elem[$k] = element::_toArray($k, $val);
      }
    }

    return $elem;
  }

  /**
   * which element should return the add_field() function
   * @return string one of 'parent' or 'this'
   */
  protected function on_add_return(){
    return 'parent';
  }

  /**
   * returns the translated version of the input text ( when available ) depending on current element configuration
   * @param  string $text input text
   * @return string       text to return (translated or not)
   */
  protected function get_text($text){
    if( $this->no_translation == TRUE ) return $text;
    return form::translate_string($text);
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

}

/* #########################################################
   ####                      FORM                       ####
   ######################################################### */

/**
 * the form object class
 */
class form extends element{

  /**
   * form id
   * @var string
   */
  protected $form_id = 'cs_form';

  /**
   * form definition function name
   * @var string
   */
  protected $definition_function = '';


  /**
   * form token
   * @var string
   */
  protected $form_token = '';

  /**
   * form action
   * @var string
   */
  protected $action = '';

  /**
   * form method
   * @var string
   */
  protected $method = 'post';

  /**
   * "form is already processsd" flag
   * @var boolean
   */
  protected $processed = FALSE;

  /**
   * "form is already validated" flag
   * @var boolean
   */
  protected $validated = FALSE;

  /**
   * "form is already submitted" flag
   * @var boolean
   */
  protected $submitted = FALSE;

  /**
   * "form is valid" flag
   * @var null
   */
  protected $valid = NULL;

  /**
   * validate functions list
   * @var array
   */
  protected $validate = array();

  /**
   * submit functions list
   * @var array
   */
  protected $submit = array();

  /**
   * form output type (html/json)
   * @var string
   */
  protected $output_type = 'html';

  /**
   * show inline errors
   * @var boolean
   */
  protected $inline_errors = FALSE;

  /**
   * "form already pre-rendered" flag
   * @var boolean
   */
  protected $pre_rendered = FALSE;

  /**
   * "js was aleready generated" flag
   * @var boolean
   */
  protected $js_generated = FALSE;

  /**
   * keeps fields insert order
   * @var array
   */
  protected $insert_field_order = array();

  /**
   * form fields
   * @var array
   */
  protected $fields = array();

  /**
   * ajax submit url
   * @var string
   */
  protected $ajax_submit_url = '';

  /**
   * current step
   * @var integer
   */
  private $current_step = 0;

  /**
   * array of submit functions results
   * @var array
   */
  private $submit_functions_results = array();


  /**
   * "do not process form token" flag
   * @var boolean
   */
  private $no_token = FALSE;

  /**
   * class constructor
   * @param array $options build options
   */
  public function __construct($options = array()) {
    $this->build_options = $options;

    $this->container_tag = FORMS_DEFAULT_FORM_CONTAINER_TAG;
    $this->container_class = FORMS_DEFAULT_FORM_CONTAINER_CLASS;

    foreach ($options as $name => $value) {
      $name = trim($name);
      if( property_exists(get_class($this), $name) )
        $this->$name = $value;
    }

    $hassubmitter = FALSE;
    foreach($this->submit as $s){
      if (!empty($s) && is_callable($s)) {
        $hassubmitter = TRUE;
      }
    }
    if( !$hassubmitter ){
        array_push($this->submit, "{$this->form_id}_submit");
    }

    // if (empty($this->submit) || !is_callable($this->submit)) {
    //   array_push($this->submit, "{$this->form_id}_submit");
    // }

    $hasvalidator = FALSE;
    foreach($this->validate as $v){
      if (!empty($v) && is_callable($v)) {
        $hasvalidator = TRUE;
      }
    }
    if( !$hasvalidator ){
      array_push($this->validate, "{$this->form_id}_validate");
    }

    // if (empty($this->validate) || !is_callable($this->validate)) {
    //   array_push($this->validate, "{$this->form_id}_validate");
    // }

    if(!$this->validate instanceof ordered_functions){
      $this->validate = new ordered_functions($this->validate,'validator');
    }

    if(!$this->submit instanceof ordered_functions){
      $this->submit = new ordered_functions($this->submit,'submitter');
    }

    $sid = session_id();
    if (!empty($sid)) {
      $this->form_token = sha1(mt_rand(0, 1000000));
      $_SESSION['form_token'][$this->form_token] = $_SERVER['REQUEST_TIME'];
    }
  }

  /**
   * set form id
   * @param string $form_id set the form id used for getting the submit function name
   * @return form
   */
  public function set_form_id($form_id){
    $this->form_id = $form_id;
    return $this;
  }

  /**
   * get the form id
   * @return string form id
   */
  public function get_form_id(){
    return $this->form_id;
  }


  /**
   * set the form action attribute
   * @param string $action the form action url
   * @return form
   */
  public function set_action($action){
    $this->action = $action;
    return $this;
  }

  /**
   * get the form action url
   * @return string the form action
   */
  public function get_action(){
    return $this->action;
  }

  /**
   * set the form method
   * @param string $method form method
   * @return form
   */
  public function set_method($method){
    $this->method = strtolower(trim($method));
    return $this;
  }

  /**
   * get the form method
   * @return string form method
   */
  public function get_method(){
    return $this->method;
  }


  /**
   * set the ajax submit url used for form submission
   * @param string $ajax_submit_url ajax endpoint url
   * @return form
   */
  public function set_ajax_submit_url($ajax_submit_url){
    $this->ajax_submit_url = $ajax_submit_url;
    return $this;
  }

  /**
   * get the ajax form submission url
   * @return string the form ajax submission url
   */
  public function get_ajax_submit_url(){
    return $this->ajax_submit_url;
  }

  /**
   * set the form render output type
   * @param string $output_type output type ( 'html' / 'json' )
   * @return form
   */
  public function set_output_type($output_type){
    $this->output_type = $output_type;
    return $this;
  }

  /**
   * get the form render output type
   * @return string form output type
   */
  public function get_output_type(){
    return $this->output_type;
  }


  /**
   * set no_token flag
   * @param boolean $no_token no token flag
   * @return form
   */
  public function set_no_token($no_token){
    $this->no_token = $no_token;
    return $this;
  }

  /**
   * get no_token flag
   * @return boolean no token flag
   */
  public function get_no_token(){
    return $this->no_token;
  }

  /**
   * get the form token
   * @return string the form token used in form validation and submission process
   */
  public function get_form_token(){
    return $this->form_token;
  }

  /**
   * return form elements (all the steps) values
   * @return array form values
   */
  public function values() {
    // Warning: some messy logic in calling process->submit->values
    if (!$this->processed) {
      $this->process();
    }
    $output = array();
    for($step = 0; $step <= $this->get_num_steps() ; $step++){
      foreach ($this->get_fields($step) as $name => $field) {
        if($field->is_a_value() == TRUE){
          $output[$name] = $field->values();
          if(is_array($output[$name]) && empty($output[$name])){
            unset($output[$name]);
          }
        }
      }
    }

    return new form_values($output);
  }

  /**
   * get current step elemets values
   * @return array step values
   */
  private function get_current_step_values(){
    $output = array();
    foreach ($this->get_fields($this->current_step) as $name => $field) {
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
   * resets the form
   */
  public function reset() {
    foreach ($this->get_fields() as $name => $field) {
      $field->reset();
      if(strtolower($this->method) == 'post') {
        unset( $_POST[$name] );
      } else {
        unset( $_GET[$name] );
      }
      unset( $_REQUEST[$name] );
    }

    if(strtolower($this->method) == 'post') {
      unset( $_POST['form_id'] );
      unset( $_POST['form_token'] );
    } else {
      unset( $_GET['form_id'] );
      unset( $_GET['form_token'] );
    }
    unset( $_REQUEST['form_id'] );
    unset( $_REQUEST['form_token'] );

    if(isset($_SESSION[$this->form_id])){
      unset($_SESSION[$this->form_id]);
    }
    if(isset($_SESSION['form_definition'][$this->form_id])){
      unset($_SESSION['form_definition'][$this->form_id]);
    }

    $this->processed = FALSE;
    $this->validated = FALSE;
    $this->submitted = FALSE;
    $this->js_generated = FALSE;
    $this->set_errors( array() );
    $this->valid = NULL;
    $this->current_step = 0;
    $this->submit_functions_results = array();
  }

  /**
   * check if form is submitted
   * @return boolean form is submitted
   */
  public function is_submitted() {
    return $this->submitted;
  }


  /**
   * check if form is processed
   * @return boolean form is processed
   */
  public function is_processed() {
    return $this->processed;
  }


  /**
   * get the form submit results optionally by submit function name
   * @param  string $submit_function submit function name
   * @return mixed                   function(s) return value or function(s) data sent to stdout if not returning anything
   */
  public function get_submit_results( $submit_function = '' ){
    if( !$this->is_submitted() ) return FALSE;
    if( !empty($submit_function) ) {
      if( !in_array($submit_function, array_keys($this->submit_functions_results)) ) return FALSE;
      return $this->submit_functions_results[$submit_function];
    }
    return $this->submit_functions_results;
  }

  /**
   * alter request hook
   * @param array $request request array
   */
  private function alter_request(&$request){
    foreach($this->get_fields($this->current_step) as $field){
      $field->alter_request($request);
    }
  }

  /**
   * copies the request values into the right form element
   * @param  array $request request array
   * @param  integer $step    step number
   */
  private function inject_values($request, $step){
    foreach ($this->get_fields($step) as $name => $field) {
      if( $field instanceof fields_container ){
        $field->process($request);
      } else if ( preg_match_all('/(.*?)(\[(.*?)\])+/i',$name, $matches, PREG_SET_ORDER) ) {
        $value = NULL;
        if(isset($request[ $matches[0][1] ])){
          $value = $request[ $matches[0][1] ];
          foreach($matches as $match){
            if(isset($value[ $match[3] ])){
              $value = $value[ $match[3] ];
            }
          }
        }
        $field->process($value);
      } else if ( isset($request[$name]) ) {
        $field->process($request[$name]);
      } else if( $field instanceof checkbox || $field instanceof radios ){
        // no value on request[name] && field is a checkbox or radios group - process anyway with an empty value
        $field->process(NULL);
      } else if( $field instanceof select ){
        if($field->is_multiple()) $field->process(array());
        else $field->process(NULL);
      } else if( $field instanceof field_multivalues ){
        // no value on request[name] && field is a multivalue (eg. checkboxes ?) - process anyway with an empty value
        $field->process(array());
      }
    }
  }

  /**
   * save current step request array in session
   * @param  array $request request array
   */
  private function save_step_request($request){
    $files = $this->get_step_fields_by_type_and_name('file', NULL, $this->current_step);
    if( !empty($files) ){
      foreach($files as $filefield){
        $request[$filefield->get_name()] = $filefield->values();
        $request[$filefield->get_name()]['uploaded'] = $filefield->is_uploaded();
      }
    }

    $recaptchas = $this->get_step_fields_by_type_and_name('recaptcha', NULL, $this->current_step);
    if( !empty($recaptchas) ){
      foreach($recaptchas as $recaptchafield){
        $request[$recaptchafield->get_name()] = $recaptchafield->values();
        $request[$recaptchafield->get_name()]['already_validated'] = $recaptchafield->is_already_validated();
      }
    }

    $_SESSION[$this->form_id]['steps'][$this->current_step] = $request;
  }

  /**
   * starts the form processing, validating and submitting
   * @param  array  $values the request values array
   */
  public function process( $values = array() ) {
    // let others alter the form
    $defined_functions = get_defined_functions();
    foreach( $defined_functions['user'] as $function_name){
      if( preg_match("/.*?_{$this->form_id}_form_alter$/i", $function_name) ){
        $function_name($this);
      }
    }

    $request = NULL;
    if (!$this->processed) { //&& !form::is_partial()
      if( empty($values) ){
        $request = (strtolower($this->method) == 'post') ? $_POST : $_GET;
      }else{
        $request = $values;
      }

      //alter request if needed
      $this->alter_request($request);

      if (isset($request['form_id']) && $request['form_id'] == $this->form_id) {
        if(isset($request['current_step'])){
          $this->current_step = $request['current_step'];
        }
        // insert values into fields
        for($step = 0; $step < $this->current_step; $step++){
          if(isset($_SESSION[$this->form_id]['steps'][$step])){
            $this->inject_values($_SESSION[$this->form_id]['steps'][$step], $step);
          }
        }

        $this->inject_values($request, $this->current_step);

        if( !$this->is_final_step() ){
          $this->save_step_request($request);
        }

        $this->processed = TRUE;
      }
    }

    if($this->processed == TRUE){
      for($step = 0; $step <= $this->current_step; $step++){
        foreach ($this->get_fields($step) as $name => $field) {
          $field->preprocess();
        }
      }
      if( !form::is_partial() && !$this->submitted && $this->valid() && $this->is_final_step() ){
        $this->submitted = TRUE;

        if(isset($_SESSION[$this->form_id])){
          unset($_SESSION[$this->form_id]);
        }

        for($step = 0; $step < $this->get_num_steps(); $step++){
          foreach( $this->get_fields($step) as $name => $field ){
            $field->postprocess();
          }
        }
        foreach($this->submit as $submit_function){
          if( function_exists($submit_function) ) {
            $submitresult = '';
            ob_start();
            $submitresult = $submit_function($this, $request);
            if($submitresult == NULL ){
              $submitresult = ob_get_contents();
            }
            ob_end_clean();
            $this->submit_functions_results[$submit_function] = $submitresult;
          }
        }
      }
    }
  }


  /**
   * check if form is valid / NULL if form is on the first render
   * @return boolean form is valid
   */
  public function valid() {
    if ($this->validated) {
      return $this->valid;
    }
    if (!isset($_REQUEST['form_id'])) {
      return NULL;
    } else if ($_REQUEST['form_id'] == $this->form_id) {
      $sid = session_id();
      if (!empty($sid) && !$this->no_token) {
        $this->valid = FALSE;
        $this->add_error($this->get_text('Form is invalid or has expired'),__FUNCTION__);
        if (isset($_REQUEST['form_token']) && isset($_SESSION['form_token'][$_REQUEST['form_token']])) {
          if ($_SESSION['form_token'][$_REQUEST['form_token']] >= $_SERVER['REQUEST_TIME'] - FORMS_SESSION_TIMEOUT) {
            $this->valid = TRUE;
            $this->set_errors( array() );
            if( !form::is_partial() ){
              unset($_SESSION['form_token'][$_REQUEST['form_token']]);
            }
          }
        }
      }

      for($step = 0; $step <= $this->current_step; $step++){
        foreach ($this->get_fields($step) as $field) {
          if (!$field->valid()) {
            $this->valid = FALSE;
          }
        }
      }

      if($this->valid){
        foreach ($this->get_fields($this->current_step) as $field) {
          $field->after_validate($this);
        }
        $this->current_step++;
      }

      if( $this->is_final_step() ){
        foreach($this->validate as $validate_function){
          if (function_exists($validate_function)) {
            if ( ($error = $validate_function($this, (strtolower($this->method) == 'post') ? $_POST : $_GET)) !== TRUE ){
              $this->valid = FALSE;
              $this->add_error( is_string($error) ? $this->get_text($error) : $this->get_text('Error. Form is not valid'), $validate_function );
            }
          }
        }
      }

      if(!$this->valid) $this->current_step--;
      if($this->current_step < 0) $this->current_step = 0;

      $this->validated = TRUE;
      return $this->valid;
    }
    return NULL;
  }

  /**
   * add field to form
   * @param string  $name  field name
   * @param mixed   $field field to add, can be an array or a field subclass
   * @param integer $step  step to add the field to
   * @return form
   */
  public function add_field($name, $field, $step = 0) {
    if (is_array($field)) {
      $field_type = "Degami\\PHPFormsApi\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      if(!class_exists($field_type)){
        throw new Exception("Error adding field. Class \"$field_type\" not found", 1);
      }
      $field = new $field_type($field, $name);
    }else if($field instanceof field){
      $field->set_name($name);
    }else{
      throw new Exception("Error adding field. Array or field subclass expected, ".gettype($field)." given", 1);
    }

    $field->set_parent($this);

    $this->fields[$step][$name] = $field;
    $this->insert_field_order[] = $name;

    if( !method_exists($field, 'on_add_return') ) {
      if(  $field instanceof fields_container && !( $field instanceof datetime || $field instanceof geolocation ) )
        return $field;
      return $this;
    }
    if($field->on_add_return() == 'this') return $field;
    return $this;
  }

  /**
   * remove field from form
   * @param  string $field field name
   * @param  integer $step field step
   * @return form
   */
  public function remove_field($name, $step = 0){
    unset($this->fields[$step][$name]);
    if(($key = array_search($name, $this->insert_field_order)) !== false) {
        unset($this->insert_field_order[$key]);
    }
    return $this;
  }

  /**
   * get the number of form steps
   * @return int steps number
   */
  private function get_num_steps(){
    return count($this->fields);
  }

  /**
   * check if current is the final step
   * @return boolean this is the final step
   */
  private function is_final_step(){
    return ($this->current_step >= $this->get_num_steps());
  }

  /**
   * check if this request is a "partial" ( used in elements ajax requests )
   * @return boolean [description]
   */
  static function is_partial(){
    return (isset($_REQUEST['partial']) && $_REQUEST['partial'] == 'true');
  }

  /**
   * get the fields array by reference
   * @param  integer $step step number
   * @return array        the array of elements for the step specified
   */
  public function &get_fields($step = 0){
    $notfound = array();
    if(!isset($this->fields[$step])) return $notfound;
    return $this->fields[$step];
  }


  /**
   * get the step fields by type and name
   * @param  array|string  $field_types field types
   * @param  string  $name       field name
   * @param  integer $step       step number
   * @return array               the array of fields matching the search criteria
   */
  private function get_step_fields_by_type_and_name($field_types, $name = NULL, $step = 0){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();
    foreach($this->get_fields($step) as $field){
      if($field instanceof fields_container){
        if($name != NULL ){
          $out = array_merge($out, $field->get_fields_by_type_and_name($field_types,$name));
        }else{
          $out = array_merge($out,$field->get_fields_by_type($field_types));
        }
      }else{
        if($name != NULL ){
          if($field instanceof field && in_array($field->get_type(), $field_types) && $field->get_name() == $name) {
            $out[] = $field;
          }
        } else if($field instanceof field && in_array($field->get_type(), $field_types)) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  /**
   * get the form fields by type (in all the steps)
   * @param  array $field_types field types
   * @return array              fields in the form
   */
  public function get_fields_by_type($field_types){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    for($step=0;$step < $this->get_num_steps();$step++){
      $out = array_merge($out, $this->get_step_fields_by_type_and_name($field_types, NULL, $step));
    }
    return $out;
  }

  /**
   * get the step fields by type and name (in all the steps)
   * @param  array $field_types field types
   * @param  string $name       field name
   * @return array              fields in the form matching the search criteria
   */
  public function get_fields_by_type_and_name($field_types, $name){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    for($step=0;$step < $this->get_num_steps();$step++){
      $out = array_merge($out, $this->get_step_fields_by_type_and_name($field_types, $name, $step));
    }
    return $out;
  }

  /**
   * get field by name
   * @param  string  $field_name field name
   * @param  integer $step       step number where to find the field
   * @return element subclass field object
   */
  public function get_field($field_name, $step = 0){
    return isset($this->fields[$step][$field_name]) ? $this->fields[$step][$field_name] : NULL;
  }

  /**
   * get the submit element which submitted the form
   * @return action subclass the submitter
   */
  public function get_triggering_element(){
    $fields = $this->get_fields_by_type(array('submit','button','image_button'));
    foreach($fields as $field){
      if($field->get_clicked() == TRUE) return $field;
    }

    if( form::is_partial() ){
      $triggering_id = $_REQUEST['triggering_element'];
      return element::search_field_by_id($this, $triggering_id);
    }

    return NULL;
  }

  /**
   * get the form submit
   * @return ordered_functions form submit function(s)
   */
  public function get_submit(){
    return $this->submit;
  }

  /**
   * get the form validate
   * @return ordered_functions form validate function(s)
   */
  public function get_validate(){
    return $this->validate;
  }

  /**
   * get the form id
   * @return string the form id
   */
  public function get_id(){
    return $this->form_id;
  }

  /**
   * get the current step number
   * @return integer current step
   */
  public function get_current_step(){
    return $this->current_step;
  }

  /**
   * get ajax url
   * @return string ajax form submit url
   */
  public function get_ajax_url(){
    return $this->ajax_submit_url;
  }

  /**
   * renders form errors
   * @return string errors as an html <li> list
   */
  public function show_errors() {
    return (!$this->has_errors()) ? '' : "<li>".implode('</li><li>',$this->get_errors())."</li>";
  }

  /**
   * renders form highlights
   * @return string highlights as an html <li> list
   */
  public function show_highlights() {
    return (!$this->has_highlights()) ? '' : "<li>".implode('</li><li>',$this->get_highlights())."</li>";
  }

  /**
   * sets inline error preference
   * @return form
   */
  public function set_inline_errors($inline_errors) {
    $this->inline_errors = $inline_errors;

    return $this;
  }

  /**
   * returns inline error preference
   * @return boolean errors should be presented inline after every elemen
   */
  public function get_inline_errors() {
    return $this->inline_errors;
  }

  /**
   * returns inline error preference
   * @return boolean errors should be presented inline after every elemen
   */
  public function errors_inline() {
    return $this->get_inline_errors();
  }


  /**
   * pre-render hook. using this hook form elements can modify the form element
   */
  public function pre_render(){
    foreach ($this->get_fields($this->current_step) as $name => $field) {
      if( is_object($field) && method_exists ( $field , 'pre_render' ) ){
        $field->pre_render($this);
      }
    }
    $this->pre_rendered = TRUE;
  }

  /**
   * renders the form
   * @param  string $override_output_type output type
   * @return string                       the form html
   */
  public function render( $override_output_type = NULL ) {
    $output = '';
    $errors = '';
    $highlights = '';
    $fields_html = '';

    // render needs the form to be processed
    if( !$this->processed ) $this->process();

    if( !is_string($override_output_type) ) $override_output_type = NULL;
    $output_type = !empty($override_output_type) ? $override_output_type : $this->get_output_type();
    $output_type = trim(strtolower($output_type));
    if( $output_type == 'json' && empty($this->ajax_submit_url) ){
      $output_type = 'html';
    }

    if ( $this->valid() === FALSE) {
      $errors = $this->show_errors();
      $this->set_attribute('class',trim($this->get_attribute('class').' with-errors'));
      if(!$this->errors_inline()){
        foreach ($this->get_fields($this->current_step) as $field) {
          $errors .= $field->show_errors();
        }
      }
      if(trim($errors)!=''){
        $errors =  sprintf(FORMS_ERRORS_TEMPLATE, $errors);
      }
    }

    if( $this->has_highlights() ){
      $highlights = $this->show_highlights();
      if(trim($highlights)!=''){
        $highlights =  sprintf(FORMS_HIGHLIGHTS_TEMPLATE, $highlights);
      }
    }

    $insertorder = array_flip($this->insert_field_order);
    $weights = $order = array();
    foreach ($this->get_fields($this->current_step) as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    if( count( $this->get_fields($this->current_step) ) > 0 ){
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields($this->current_step));
    }


    foreach ($this->get_fields($this->current_step) as $name => $field) {
      if( is_object($field) && method_exists ( $field , 'render' ) ){
        $fields_html .= $field->render($this);
      }
    }

    $attributes = $this->get_attributes(array('action','method','id'));
    $js = $this->generate_js();

    if( form::is_partial() ){
      // ajax request - form item event

      $jsondata = json_decode($_REQUEST['jsondata']);
      $callback = $jsondata->callback;
      if( is_callable($callback) ){
        /** @var field $target_elem */
        $target_elem = $callback( $this );

        $html = $target_elem->render($this);

        if( count($target_elem->get_css()) > 0 ){
          $html .= '<style>'.implode("\n",$target_elem->get_css())."</style>";
        }

        $js = '';
        if(count($target_elem->get_js()) > 0){
          $js = "(function($){\n".
                  "\t$(document).ready(function(){\n".
                  "\t\t".implode( ";\n\t\t", $target_elem->get_js() ).";\n".
                  "\t});\n".
                "})(jQuery);";
        }

        return json_encode(array( 'html' => $html, 'js' => $js ));
      }

      return FALSE;
    }

    if(!empty($this->ajax_submit_url) && $this->get_output_type() == 'json' && $output_type == 'html'){
      // print initial js for ajax form

      $output = "<script type=\"text/javascript\">"
          .preg_replace("/\s+/"," ",str_replace("\n","",
          "(function(\$){
          \$(document).ready(function(){
            var {$this->get_id()}_attachFormBehaviours = function (){
              \$('#{$this->get_id()}').submit(function(evt){
                evt.preventDefault();
                \$.post( \"{$this->get_ajax_url()}\", \$('#{$this->get_id()}').serialize(), function( data ) {
                  var response;
                  if(typeof data =='object') response = data;
                  else response = \$.parseJSON(data);
                  \$('#{$this->get_id()}-formcontainer').html('');
                  \$(response.html).appendTo( \$('#{$this->get_id()}-formcontainer') );
                  if( \$.trim(response.js) != '' ){
                    eval( response.js );
                  };
                  {$this->get_id()}_attachFormBehaviours();
                });
                return false;
              });
            };
            \$.getJSON('{$this->get_ajax_url()}',function(response){
              \$(response.html).appendTo( \$('#{$this->get_id()}-formcontainer') );
              if( \$.trim(response.js) != '' ){
                eval( response.js );
              };
              {$this->get_id()}_attachFormBehaviours();
            });
          });
        })(jQuery);")).
        "</script>\n".
        "<div id=\"{$this->get_id()}-formcontainer\"></div>";
    }else{

      switch($output_type){
        case 'json':
          $output = array('html'=>'','js'=>'','is_submitted'=>$this->is_submitted());

          $output['html']  = $this->get_element_prefix();
          $output['html'] .= $this->get_prefix();
          $output['html'] .= $highlights;
          $output['html'] .= $errors;
          $output['html'] .= "<form action=\"{$this->action}\" id=\"{$this->form_id}\" method=\"{$this->method}\"{$attributes}>\n";
          $output['html'] .= $fields_html;
          $output['html'] .= "<input type=\"hidden\" name=\"form_id\" value=\"{$this->form_id}\" />\n";
          if( !$this->no_token ) $output['html'] .= "<input type=\"hidden\" name=\"form_token\" value=\"{$this->form_token}\" />\n";
          if( $this->get_num_steps() > 1) {
            $output['html'] .= "<input type=\"hidden\" name=\"current_step\" value=\"{$this->current_step}\" />\n";
          }
          $output['html'] .= "</form>\n";
          $output['html'] .= $this->get_suffix();
          $output['html'] .= $this->get_element_suffix();

          if(count($this->get_css())>0){
            $output['html'] .= "<style>".implode("\n",$this->get_css())."</style>";
          }

          if(!empty( $js )){
            $output['js'] = $js;
          }

          $output = json_encode($output);
        break;

        case 'html':
        default:
          $output = $this->get_element_prefix();
          $output .= $this->get_prefix();
          $output .= $highlights;
          $output .= $errors;
          $output .= "<form action=\"{$this->action}\" id=\"{$this->form_id}\" method=\"{$this->method}\"{$attributes}>\n";
          $output .= $fields_html;
          $output .= "<input type=\"hidden\" name=\"form_id\" value=\"{$this->form_id}\" />\n";
          if( !$this->no_token ) $output .= "<input type=\"hidden\" name=\"form_token\" value=\"{$this->form_token}\" />\n";
          if( $this->get_num_steps() > 1) {
            $output .= "<input type=\"hidden\" name=\"current_step\" value=\"{$this->current_step}\" />\n";
          }
          $output .= "</form>\n";
          if(count($this->get_css())>0){
            $output .= "<style>".implode("\n",$this->get_css())."</style>";
          }

          if(!empty( $js )){
            $output .= "\n<script type=\"text/javascript\">\n".$js."\n</script>\n";
          }
          $output .= $this->get_suffix();
          $output .= $this->get_element_suffix();
        break;
      }

    }
    return $output;
  }

  /**
   * generate the js string
   * @return string the js into a jquery sandbox
   */
  public function generate_js(){
    if( !$this->pre_rendered ) $this->pre_render(); // call all elements pre_render, so they can attach js to the form element;

    $js = array_filter(array_map('trim', $this->get_js() ));
    if(!empty( $js ) && !$this->js_generated ){
      foreach($js as &$js_string){
        if($js_string[strlen($js_string)-1] == ';'){
          $js_string = substr($js_string,0,strlen($js_string)-1);
        }
      }

      $this->js_generated = TRUE;
      return "(function($){\n".
        "\t$(document).ready(function(){\n".
        "\t\t".implode(";\n\t\t",$js).";\n".
        "\t});\n".
      "})(jQuery);";
    }
    return "";
  }

  /**
   * "required" validation function
   * @param  mixed $value the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_required($value = NULL) {
    if ( !empty($value) || (!is_array($value) && trim($value) != '') ) {
      return TRUE;
    } else {
      return "<em>%t</em> is required";
    }
  }

  /**
   * "notZero" required validation function - useful for radios
   * @param  mixed $value the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_notzero($value = NULL) {
    if ( (!empty($value) && (!is_array($value) && trim($value) != '0')) ) {
      return TRUE;
    } else {
      return "<em>%t</em> is required";
    }
  }

  /**
   * "max_length" validation function
   * @param  mixed $value   the element value
   * @param  mixed $options max length
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_max_length($value, $options) {
    // if(!is_string($value)) throw new Exception("Invalid value - max_length is meant for strings, ".gettype($value)." given");
    if (strlen($value) > $options) {
      return "Maximum length of <em>%t</em> is {$options}";
    }
    return TRUE;
  }

  /**
   * "min_length" validation function
   * @param  mixed $value   the element value
   * @param  mixed $options min length
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_min_length($value, $options) {
    // if(!is_string($value)) throw new Exception("Invalid value - min_length is meant for strings, ".gettype($value)." given");
    if (strlen($value) < $options) {
      return "<em>%t</em> must be longer than {$options}";
    }
    return TRUE;
  }

  /**
   * "exact_length" validation function
   * @param  mixed $value   the element value
   * @param  mixed $options length
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_exact_length($value, $options) {
    // if(!is_string($value)) throw new Exception("Invalid value - exact_length is meant for strings, ".gettype($value)." given");
    if (strlen($value) != $options) {
      return "<em>%t</em> must be {$options} characters long.";
    }
    return TRUE;
  }

  /**
   * "regexp" validation function
   * @param  mixed $value   the element value
   * @param  mixed $options regexp string
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_regexp($value, $options) {
    if (!preg_match( $options, $value)) {
      return "<em>%t</em> must match the regular expression \"$options\".";
    }
    return TRUE;
  }

  /**
   * "alpha" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_alpha($value) {
    // if(!is_string($value)) throw new Exception("Invalid value - alpha is meant for strings, ".gettype($value)." given");
    if (!preg_match( "/^([a-z])+$/i", $value)) {
      return "<em>%t</em> must contain alphabetic characters.";
    }
    return TRUE;
  }

  /**
   * "alpha_numeric" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_alpha_numeric($value) {
    // if(!is_string($value) && !is_numeric($value)) throw new Exception("Invalid value - alpha_numeric is meant for strings or numeric values, ".gettype($value)." given");
    if (!preg_match("/^([a-z0-9])+$/i", $value)) {
      return "<em>%t</em> must only contain alpha numeric characters.";
    }
    return TRUE;
  }

  /**
   * "alpha_dash" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_alpha_dash($value) {
    // if(!is_string($value)) throw new Exception("Invalid value - alpha_dash is meant for strings, ".gettype($value)." given");
    if (!preg_match("/^([-a-z0-9_-])+$/i", $value)) {
      return "<em>%t</em> must contain only alpha numeric characters, underscore, or dashes";
    }
    return TRUE;
  }

  /**
   * "numeric" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_numeric($value) {
    if (!is_numeric($value)) {
      return "<em>%t</em> must be numeric.";
    }
    return TRUE;
  }

  /**
   * "integer" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_integer($value) {
    if (!preg_match( '/^[\-+]?[0-9]+$/', $value)) {
      return "<em>%t</em> must be an integer.";
    }
    return TRUE;
  }

  /**
   * "match" validation function
   * @param  mixed $value   the element value
   * @param  mixed $options elements to find into _REQUEST array
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_match($value, $options) {
    $other = form::scan_array($options, $_REQUEST);
    if ($value != $other) {
      return "The field <em>%t</em> is invalid.";
    }
    return TRUE;
  }

  /**
   * "file_extension" validation function
   * @param  mixed $value   the element value
   * @param  mixed $options file extension
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_file_extension($value, $options) {
    if(!isset($value['filepath'])) return "<em>%t</em> - Error. value has no filepath attribute";
    $options = explode(',', $options);
    $ext = substr(strrchr($value['filepath'], '.'), 1);
    if (!in_array($ext, $options)) {
      return "File upload <em>%t</em> is not of required type";
    }
    return TRUE;
  }

  /**
   * "file_not_exists" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_file_not_exists($value) {
    if(!isset($value['filepath'])) return "<em>%t</em> - Error. value has no filepath attribute";
    if (file_exists($value['filepath'])) {
      return "The file <em>%t</em> has already been uploaded";
    }
    return TRUE;
  }

  /**
   * "max_file_size" validation function
   * @param  mixed $value   the element value
   * @param  mixed $options max file size
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_max_file_size($value, $options) {
    if(!isset($value['filesize'])) return "<em>%t</em> - Error. value has no filesize attribute";
    if ($value['filesize'] > $options) {
      $max_size = form::format_bytes($options);
      return "The file <em>%t</em> is too big. Maximum filesize is {$max_size}.";
    }
    return TRUE;
  }

  /**
   * format byte size
   * @param  integer $size size in bytes
   * @return string       formatted size
   */
  private static function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
  }


  /**
   * "email" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_email($email) {
    if (empty($email)) return FALSE;
    $check_dns = FORMS_VALIDATE_EMAIL_DNS;
    $blocked_domains = explode('|', FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS);
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
      return "<em>%t</em> is not a valid email. It must contain the @ symbol.";
    } else {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {
        return "<em>%t</em> is not a valid email. Local part is wrong length.";
      } else if ($domainLen < 1 || $domainLen > 255) {
        return "<em>%t</em> is not a valid email. Domain name is wrong length.";
      } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
        return "<em>%t</em> is not a valid email. Local part starts or ends with '.'";
      } else if (preg_match('/\\.\\./', $local)) {
        return "<em>%t</em> is not a valid email. Local part two consecutive dots.";
      } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
        return "<em>%t</em> is not a valid email. Invalid character in domain.";
      } else if (preg_match('/\\.\\./', $domain)) {
        return "<em>%t</em> is not a valid email. Domain name has two consecutive dots.";
      } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
        if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
          return "<em>%t</em> is not a valid email. Invalid character in local part.";
        }
      }
      if (in_array($domain, $blocked_domains)) {
        return "<em>%t</em> is not a valid email. Domain name is in list of disallowed domains.";
      }
      if ($check_dns && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
        return "<em>%t</em> is not a valid email. Domain name not found in DNS.";
      }
    }
    return TRUE;
  }

  /**
   * applies trim to text
   * @param  string $text text to trim
   * @return string       trimmed version of $text
   */
  public static function process_trim($text) {
    return trim($text);
  }

  /**
   * applies ltrim to text
   * @param  string $text text to ltrim
   * @return string       ltrimmed version of $text
   */
  public static function process_ltrim($text) {
    return ltrim($text);
  }

  /**
   * applies rtrim to text
   * @param  string $text text to rtrim
   * @return string       rtrimmed version of $text
   */
  public static function process_rtrim($text) {
    return rtrim($text);
  }

  /**
   * check if $text's character encoding is utf8
   * @param  string $text text to check
   * @return boolean       is utf8
   */
  private static function _validate_utf8($text) {
    if (strlen($text) == 0) {
      return TRUE;
    }
    return (preg_match('/^./us', $text) == 1);
  }

  /**
   * applies xss checks on string (weak version)
   * @param  string $string text to check
   * @return string         safe value
   */
  public static function process_xss_weak($string) {
    return form::process_xss($string, 'a|abbr|acronym|address|b|bdo|big|blockquote|br|caption|cite|code|col|colgroup|dd|del|dfn|div|dl|dt|em|h1|h2|h3|h4|h5|h6|hr|i|img|ins|kbd|li|ol|p|pre|q|samp|small|span|strong|sub|sup|table|tbody|td|tfoot|th|thead|tr|tt|ul|var');
  }

  /**
   * applies xss checks on string
   * @param  string $string text to check
   * @param  string $allowed_tags allowed tags
   * @return string         safe value
   */
  public static function process_xss($string, $allowed_tags = FORMS_XSS_ALLOWED_TAGS) {
    // Only operate on valid UTF-8 strings. This is necessary to prevent cross
    // site scripting issues on Internet Explorer 6.
    if (!form::_validate_utf8($string)) {
      return '';
    }
    // Store the input format
    form::_filter_xss_split($allowed_tags, TRUE);
    // Remove NUL characters (ignored by some browsers)
    $string = str_replace(chr(0), '', $string);
    // Remove Netscape 4 JS entities
    $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

    // Defuse all HTML entities
    $string = str_replace('&', '&amp;', $string);
    // Change back only well-formed entities in our whitelist
    // Decimal numeric entities
    $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
    // Hexadecimal numeric entities
    $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
    // Named entities
    $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);

    return preg_replace_callback('%
      (
      <(?=[^a-zA-Z!/])  # a lone <
      |                 # or
      <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
      |                 # or
      >                 # just a >
      )%x', 'form::_filter_xss_split', $string);
  }

  /**
   * _filter_xss_split private method
   * @param  string  $m     string to split
   * @param  boolean $store store elements into static $allowed html
   * @return string         string
   */
  private static function _filter_xss_split($m, $store = FALSE) {
    static $allowed_html;

    if ($store) {
      $m = explode("|", $m);
      $allowed_html = array_flip($m);
      return '';
    }

    $string = $m[1];

    if (substr($string, 0, 1) != '<') {
      // We matched a lone ">" character
      return '&gt;';
    }
    else if (strlen($string) == 1) {
      // We matched a lone "<" character
      return '&lt;';
    }

    if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$%', $string, $matches)) {
      // Seriously malformed
      return '';
    }

    $slash = trim($matches[1]);
    $elem = &$matches[2];
    $attrlist = &$matches[3];

    if (!isset($allowed_html[strtolower($elem)])) {
      // Disallowed HTML element
      return '';
    }

    if ($slash != '') {
      return "</$elem>";
    }

    // Is there a closing XHTML slash at the end of the attributes?
    // In PHP 5.1.0+ we could count the changes, currently we need a separate match
    $xhtml_slash = preg_match('%\s?/\s*$%', $attrlist) ? ' /' : '';
    $attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist);

    // Clean up attributes
    $attr2 = implode(' ', form::_filter_xss_attributes($attrlist));
    $attr2 = preg_replace('/[<>]/', '', $attr2);
    $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

    return "<$elem$attr2$xhtml_slash>";
  }

  /**
   * _filter_xss_attributes private method
   * @param  string $attr attributes string
   * @return array        filtered attributes array
   */
  private static function _filter_xss_attributes($attr) {
    $attrarr = array();
    $mode = 0;
    $attrname = '';
    $skip = FALSE;

    while (strlen($attr) != 0) {
      // Was the last operation successful?
      $working = 0;

      switch ($mode) {
        case 0:
          // Attribute name, href for instance.
          if (preg_match('/^([-a-zA-Z]+)/', $attr, $match)) {
            $attrname = strtolower($match[1]);
            $skip = ($attrname == 'style' || substr($attrname, 0, 2) == 'on');
            $working = $mode = 1;
            $attr = preg_replace('/^[-a-zA-Z]+/', '', $attr);
          }
          break;

        case 1:
          // Equals sign or valueless ("selected").
          if (preg_match('/^\s*=\s*/', $attr)) {
            $working = 1;
            $mode = 2;
            $attr = preg_replace('/^\s*=\s*/', '', $attr);
            break;
          }

          if (preg_match('/^\s+/', $attr)) {
            $working = 1;
            $mode = 0;
            if (!$skip) {
              $attrarr[] = $attrname;
            }
            $attr = preg_replace('/^\s+/', '', $attr);
          }
          break;

        case 2:
          // Attribute value, a URL after href= for instance.
          if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match)) {
            $thisval = form::_filter_xss_bad_protocol($match[1]);

            if (!$skip) {
              $attrarr[] = "$attrname=\"$thisval\"";
            }
            $working = 1;
            $mode = 0;
            $attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
            break;
          }

          if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match)) {
            $thisval = form::_filter_xss_bad_protocol($match[1]);

            if (!$skip) {
              $attrarr[] = "$attrname='$thisval'";
            }
            $working = 1;
            $mode = 0;
            $attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
            break;
          }

          if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match)) {
            $thisval = form::_filter_xss_bad_protocol($match[1]);

            if (!$skip) {
              $attrarr[] = "$attrname=\"$thisval\"";
            }
            $working = 1;
            $mode = 0;
            $attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
          }
          break;
      }

      if ($working == 0) {
        // Not well formed; remove and try again.
        $attr = preg_replace('/
          ^
          (
          "[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
          |               # or
          \'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
          |               # or
          \S              # - a non-whitespace character
          )*              # any number of the above three
          \s*             # any number of whitespaces
          /x', '', $attr);
        $mode = 0;
      }
    }

    // The attribute list ends with a valueless attribute like "selected".
    if ($mode == 1 && !$skip) {
      $attrarr[] = $attrname;
    }
    return $attrarr;
  }

  /**
   *[_filter_xss_bad_protocol private method
   * @param  string  $string string
   * @param  boolean $decode process entity decode on string
   * @return string          safe value
   */
  private static function _filter_xss_bad_protocol($string, $decode = TRUE) {
    if ($decode) {
      $string = form::process_entity_decode($string);
    }
    return form::process_plain(form::_strip_dangerous_protocols($string));
  }

  /**
   * _strip_dangerous_protocols private method
   * @param  string $uri uri
   * @return string      safe value
   */
  private static function _strip_dangerous_protocols($uri) {
    static $allowed_protocols;

    if (!isset($allowed_protocols)) {
      $allowed_protocols = array_flip(array('ftp', 'http', 'https', 'irc', 'mailto', 'news', 'nntp', 'rtsp', 'sftp', 'ssh', 'tel', 'telnet', 'webcal'));
    }

    // Iteratively remove any invalid protocol found.
    do {
      $before = $uri;
      $colonpos = strpos($uri, ':');
      if ($colonpos > 0) {
        // We found a colon, possibly a protocol. Verify.
        $protocol = substr($uri, 0, $colonpos);
        // If a colon is preceded by a slash, question mark or hash, it cannot
        // possibly be part of the URL scheme. This must be a relative URL, which
        // inherits the (safe) protocol of the base document.
        if (preg_match('![/?#]!', $protocol)) {
          break;
        }
        // Check if this is a disallowed protocol. Per RFC2616, section 3.2.3
        // (URI Comparison) scheme comparison must be case-insensitive.
        if (!isset($allowed_protocols[strtolower($protocol)])) {
          $uri = substr($uri, $colonpos + 1);
        }
      }
    } while ($before != $uri);

    return $uri;
  }


  /**
   * applies plain_text to text
   * @param  string $text text to encode
   * @return string       plain version of $text
   */
  public static function process_plain($text) {
      // if using PHP < 5.2.5 add extra check of strings for valid UTF-8
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  /**
   * applies entity_decode to text
   * @param  string $text text to decode
   * @return string       decoded version of $text
   */
  public static function process_entity_decode($text) {
    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
  }

  /**
   * applies addslashes to text
   * @param  string $text text to addslash
   * @return string       addslashed version of $text
   */
  public static function process_addslashes($text) {
    if(!get_magic_quotes_gpc() && !preg_match("/\\/i",$text))
      return addslashes($text);
    else return $text;
  }

  /**
   * scan_array private method
   * @param  string $string string to search
   * @param  array $array   array to check
   * @return mixed          found element / FALSE on failure
   */
  private static function scan_array($string, $array) {
    list($key, $rest) = preg_split('/[[\]]/', $string, 2, PREG_SPLIT_NO_EMPTY);
    if ( $key && $rest ) {
      return @form::scan_array($rest, $array[$key]);
    } elseif ( $key ) {
      return $array[$key];
    } else {
      return FALSE;
    }
  }

  /**
   * applies array_flatten to array
   * @param  array $array array to flatten
   * @return array        monodimensional array
   */
  public static function array_flatten($array) {
    $return = array();
    foreach ($array as $key => $value) {
      if (is_array($value)){
        $return = array_merge($return, form::array_flatten($value));
      } else {
        $return[$key] = $value;
      }
    }
    return $return;
  }

  /**
   * get array values by key
   * @param  string $search_key key to search
   * @param  array $array       where to search
   * @return array              the filtered array
   */
  public static function array_get_values($search_key, $array) {
    $return = array();
    foreach ($array as $key => $value) {
      if (is_array($value)){
        $return = array_merge($return, form::array_get_values($search_key, $value));
      }else if($key == $search_key){
        $return[] = $value;
      }
    }
    return $return;
  }

  /**
   * order elements by weight properties
   * @param  element $a first element
   * @param  element $b second element
   * @return int    position
   */
  public static function order_by_weight($a, $b){
    if ($a->get_weight() == $b->get_weight()) {
      return 0;
    }
    return ($a->get_weight() < $b->get_weight()) ? -1 : 1;
  }

  /**
   * order validation functions
   * @param  array $a first element
   * @param  array $b second element
   * @return int    position
   */
  public static function order_validators($a,$b){
    if(is_array($a) && isset($a['validator'])) $a = $a['validator'];
    if(is_array($b) && isset($b['validator'])) $b = $b['validator'];

    if($a == $b) return 0;
    if($a == 'required') return -1;
    if($b == 'required') return 1;

    return 0;
//    return $a > $b ? 1 : -1;
  }

  /**
   * translate strings, using a function named "__()" if is defined.
   * the function should take a string written in english as parameter and return the translated version
   * @param  string $string string to translate
   * @return string         the translated version
   */
  public static function translate_string($string){
    if(is_string($string) && function_exists('__')) return __($string);
    return $string;
  }

  /**
   * toString magic method
   * @return string the form html
   */
  public function __toString(){
    try{
      return $this->render();
    }catch(Exception $e){
      return $e->getMessage()."\n".$e->getTraceAsString();
    }
  }


  /**
   * on_add_return overload
   * @return string 'this'
   */
  protected function on_add_return(){
    return 'this';
  }

 /**
   * set the form definition function name
   * @param string $function_name form definition function name
   */
  public function set_definition_function($function_name){
    $this->definition_function = $function_name;
    return $this;
  }

 /**
   * get the form definition function body
   * @return string form definition function body
   */
  public function get_definition_body(){
    $body = FALSE;

    try{
      $definition_name = (!empty($this->definition_function) ? $this->definition_function : $this->get_form_id());
      if( is_callable($definition_name) ){

        if( function_exists($definition_name) ){
          $func = new \ReflectionFunction( $definition_name );
        } else {
            $func = new \ReflectionMethod( $definition_name );
        }

        if( is_object($func) ){
          $filename = $func->getFileName();
          $start_line = $func->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
          $end_line = $func->getEndLine();
          $length = $end_line - $start_line;

          $source = file($filename);
          $body = implode("", array_slice($source, $start_line, $length));
          $body = str_replace('<', '&lt;', $body);
          $body = str_replace('>', '&gt;', $body);
        }
      }
    }catch(Exception $e){
      var_dump($e->getMessage());
    }
    return $body;
  }
}


/* #########################################################
   ####                  FIELD BASE                     ####
   ######################################################### */

/**
 * the field element class.
 * @abstract
 */
abstract class field extends element{

  /**
   * validate functions list
   * @var array
   */
  protected $validate = array();

  /**
   * preprocess functions list
   * @var array
   */
  protected $preprocess = array();

  /**
   * postprocess functions list
   * @var array
   */
  protected $postprocess = array();

  /**
   * element js events list
   * @var array
   */
  protected $event = array();

  /**
   * element size
   * @var integer
   */
  protected $size = 20;

  /**
   * element type
   * @var string
   */
  protected $type = '';

  /**
   * "stop on first validation error" flag
   * @var boolean
   */
  protected $stop_on_first_error = FALSE;

  /**
   * "show tooltip instead of label" flag
   * @var boolean
   */
  protected $tooltip = FALSE;

  /**
   * element id
   * @var null
   */
  protected $id = NULL;

  /**
   * element title
   * @var null
   */
  protected $title = NULL;

  /**
   * element description
   * @var null
   */
  protected $description = NULL;

  /**
   * element disabled
   * @var boolean
   */
  protected $disabled = FALSE;

  /**
   * element default value
   * @var null
   */
  protected $default_value = NULL;

  /**
   * element value
   * @var null
   */
  protected $value = NULL;

  /**
   * "element already pre-rendered" flag
   * @var boolean
   */
  protected $pre_rendered = FALSE;

  /**
   * "this is a required field" position
   * @var string
   */
  protected $required_position = 'after';

  /**
   * element ajax url
   * @var null
   */
  protected $ajax_url = NULL;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {

    $this->build_options = $options;

    $this->name = $name;
    foreach ($options as $name => $value) {
      $name = trim($name);
      if( property_exists(get_class($this), $name) )
        $this->$name = $value;
    }

    if(!isset($this->attributes['class'])){
      $this->attributes['class'] = $this->get_element_class_name();
    }

    if(empty($this->type)){
      $this->type = preg_replace("/^Degami\\PHPFormsApi/","",get_class($this));
    }

    if(!$this->validate instanceof ordered_functions){
      $this->validate = new ordered_functions($this->validate,'validator','form::order_validators');
    }

    if(!$this->preprocess instanceof ordered_functions){
      $this->preprocess = new ordered_functions($this->preprocess, 'preprocessor');
    }

    if(!$this->postprocess instanceof ordered_functions){
      $this->postprocess = new ordered_functions($this->postprocess, 'postprocessor');
    }

    if(!$this->event instanceof ordered_functions){
      $this->event = new ordered_functions($this->event, 'event');
    }

    $this->value = $this->default_value;
  }


  /**
   * return field value
   * @return mixed field value
   */
  public function values() {
    return $this->get_value();
  }

  /**
   * return field value
   * @return mixed field value
   */
  public function get_value(){
    return $this->value;
  }

  /**
   * set field value
   * @param mixed $value value to set
   */
  public function set_value($value){
    $this->value = $value;

    return $this;
  }

  /**
   * get default value
   * @return mixed default value
   */
  public function get_default_value(){
    return $this->default_value;
  }

  /**
   * set default value
   * @param mixed $default_value default value
   */
  public function set_default_value($default_value){
    $this->default_value = $default_value;

    return $this;
  }

  /**
   * resets the field
   */
  public function reset() {
    $this->value = $this->default_value;
    $this->pre_rendered = FALSE;
    $this->set_errors( array() );
  }

  /**
   * get field type
   * @return string field type
   */
  public function get_type(){
    return $this->type;
  }

  /**
   * get field validate
   * @return ordered_functions field validate
   */
  public function get_validate(){
    return $this->validate;
  }

  /**
   * get field preprocess
   * @return ordered_functions field preprocess
   */
  public function get_preprocess(){
    return $this->preprocess;
  }

  /**
   * get field postprocess
   * @return ordered_functions field postprocess
   */
  public function get_postprocess(){
    return $this->postprocess;
  }

  /**
   * get field id
   * @return string field id
   */
  public function get_id(){
    return $this->id;
  }

  /**
   * set field id
   * @param string $id field id
   */
  public function set_id($id){
    $this->id = $id;
    return $this;
  }

  /**
   * get field html id
   * @return string the html id attributes
   */
  public function get_html_id(){
    return !empty($this->id) ? $this->get_id() : $this->get_name();
  }

  /**
   * get field ajax url
   * @return string field ajax url
   */
  public function get_ajax_url(){
    return $this->ajax_url;
  }


  /**
   * process (set) the field value
   * @param  mixed $value value to set
   */
  public function process($value) {
    $this->value = $value;
  }

  /**
   * execute the preprocess ( or postprocess ) list of functions
   * @param  string $process_type which list to process
   */
  public function preprocess($process_type = "preprocess") {
    foreach ($this->$process_type as $processor) {
      $processor_func = "process_{$processor}";
      if (function_exists($processor_func)) {
        $this->value = $processor_func($this->value);
      } else if(method_exists(get_class($this), $processor_func)){
          $this->value = call_user_func( array($this, $processor_func), $this->value );
      } else {
        if(method_exists('Degami\\PHPFormsApi\\form', $processor_func)){
          $this->value = call_user_func( array('Degami\\PHPFormsApi\\form',$processor_func), $this->value );
        }
      }
    }
  }

  /**
   * postprocess field
   */
  public function postprocess() {
    $this->preprocess("postprocess");
  }

  /**
   * check if field is valid using the validate functions list
   * @return boolean valid state
   */
  public function valid() {
    $this->set_errors( array() );

    foreach ($this->validate as $validator) {
      $matches = array();
      if(is_array($validator)){
        $validator_func = $validator['validator'];
      }else{
        $validator_func = $validator;
      }
      preg_match('/^([A-Za-z0-9_]+)(\[(.+)\])?$/', $validator_func, $matches);
      if(!isset($matches[1])) continue;
      $validator_func = "validate_{$matches[1]}";
      $options = isset($matches[3]) ? $matches[3] : NULL;
      if (function_exists($validator_func)) {
        $error = $validator_func($this->value, $options);
      } else if(method_exists(get_class($this), $validator_func)){
        $error = call_user_func( array(get_class($this), $validator_func), $this->value, $options );
      }else {
        if(method_exists('Degami\\PHPFormsApi\\form', $validator_func)){
          $error = call_user_func( array('Degami\\PHPFormsApi\\form', $validator_func), $this->value, $options );
        }
      }
      if (isset($error) && $error !== TRUE) {
        $titlestr = (!empty($this->title)) ? $this->title : (!empty($this->name) ? $this->name : $this->id);
        if(empty($error)) $error = '%t - Error.';
        $this->add_error(str_replace('%t', $titlestr, $this->get_text($error)), $validator_func);
        if(is_array($validator) && !empty($validator['error_message'])){
          $this->add_error(str_replace('%t', $titlestr, $this->get_text($validator['error_message'])),$validator_func);
        }

        if($this->stop_on_first_error){
          return FALSE;
        }
      }
    }

    if( $this->has_errors() ){
      return FALSE;
    }

    return TRUE;
  }

  /**
   * renders field errors
   * @return string errors as a <li> list
   */
  public function show_errors() {
    return (!$this->has_errors()) ? '' : "<li>".implode("</li><li>",$this->get_errors())."</li>";
  }

  /**
   * pre_render. this function will be overloaded by subclasses where needed
   * @param  form $form form object
   */
  public function pre_render(form $form){
    $this->pre_rendered = TRUE;

    //if(count($this->get_js()) > 0) {
    //  $form->add_js( $this->get_js() );
    //}

    // should not return value, just change element/form state
    return;
  }

  /**
   * render the field
   * @param  form $form form object
   * @return string        the field html
   */
  public function render(form $form) {

    $id = $this->get_html_id();
    $output = $this->get_element_prefix();
    $output.=$this->get_prefix();

    if( !($this instanceof fields_container) && !($this instanceof checkbox)){
      // containers do not need label. checkbox too, as the render function prints the label itself
      $required = ($this->validate->has_value('required')) ? '<span class="required">*</span>' : '';
      $requiredafter = $requiredbefore = $required;
      if($this->required_position == 'before') { $requiredafter = ''; $requiredbefore = $requiredbefore.' '; }
      else { $requiredbefore = ''; $requiredafter = ' '.$requiredafter; }

      if(!empty($this->title)){
        if ( $this->tooltip == FALSE ) {
          $this->label_class .= " label-" . $this->get_element_class_name();
          $this->label_class = trim($this->label_class);
          $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
          $output .= "<label for=\"{$id}\"{$label_class}>{$requiredbefore}".$this->get_text($this->title)."{$requiredafter}</label>\n";
        } else {
          if( !in_array('title', array_keys($this->attributes)) ){
            $this->attributes['title'] = strip_tags($this->get_text($this->title).$required);
          }

          $id = $this->get_html_id();
          $form->add_js("\$('#{$id}','#{$form->get_id()}').tooltip();");
        }
      }
    }

    if(!$this->pre_rendered){
      $this->pre_render($form);
      $this->pre_rendered = TRUE;
    }
    $output .= $this->render_field($form);

    if( !($this instanceof fields_container)){
      if (!empty($this->description)) {
        $output .= "<div class=\"description\">{$this->description}</div>";
      }
    }
    if($form->errors_inline() == TRUE && $this->has_errors() ){
      $output.= '<div class="inline-error has-errors">'.implode("<br />",$this->get_errors()).'</div>';
    }

    $output .= $this->get_suffix();
    $output .= $this->get_element_suffix();

    if( count($this->event) > 0 && trim($this->get_ajax_url()) != '' ){
      foreach($this->event as $event){
        $eventjs = $this->generate_event_js($event, $form);
        $this->add_js(preg_replace("/\s+/"," ",str_replace("\n","","".$eventjs)));
      }
    }

    return $output ;
  }

  /**
   * generate the necessary js to handle ajax field event property
   * @param  array  $event event element
   * @param  form $form  form object
   * @return string         javascript code
   */
  public function generate_event_js($event, form $form){
    $id = $this->get_html_id();
    if(empty($event['event'])) return FALSE;
    $question_ampersand = '?';
    if(preg_match("/\?/i", $this->get_ajax_url())) $question_ampersand = '&';

    $eventjs = "\$('#{$id}','#{$form->get_id()}').on('{$event['event']}',function(evt){
      evt.preventDefault();
      var \$target = ".((isset($event['target']) && !empty($event['target'])) ? "\$('#".$event['target']."')" : "\$('#{$id}').parent()").";
      var jsondata = { 'name':\$('#{$id}').attr('name'), 'value':\$('#{$id}').val(),'callback':'{$event['callback']}' };
      var postdata = new FormData();
      postdata.append('form_id', '{$form->get_id()}');
      postdata.append('jsondata', JSON.stringify(jsondata));
      \$('#{$form->get_id()} input,#{$form->get_id()} select,#{$form->get_id()} textarea').each(function(index, elem){
        var \$this = \$(this);
        if( \$this.serialize() != '' ){
          var elem = \$this.serialize().split('=',2);
          postdata.append(elem[0], elem[1]);
        }else if( \$this.prop('tagName').toLowerCase() == 'input' && \$this.attr('type').toLowerCase() == 'file' ){
          postdata.append(\$this.attr('name'), (\$this)[0].files[0] );
        }
      });
      var \$loading = \$('<div id=\"{$id}-event-loading\"></div>')
                      .appendTo(\$target)
                      .css({'font-size':'0.5em'})
                      .progressbar({value: false});
      \$.data(\$target[0],'loading', \$loading.attr('id'));
      \$.ajax({
        type: \"POST\",
        contentType: false,
        processData: false,
        url: \"{$this->get_ajax_url()}{$question_ampersand}partial=true&triggering_element={$this->get_html_id()}\",
        data: postdata,
        success: function( data ){
          var response;
          if(typeof data =='object') { response = data; }
          else { response = \$.parseJSON(data); }
          ".((!empty($event['method']) && $event['method'] == 'replace') ? "\$target.html('');":"")."
          ".((!empty($event['effect']) && $event['effect'] == 'fade') ? "\$target.hide(); \$(response.html).appendTo(\$target); \$target.fadeIn('fast');":"\$(response.html).appendTo(\$target);")."
          if( \$.trim(response.js) != '' ){ eval( response.js ); };

          var element_onsuccess = \$.data( \$('#{$id}','#{$form->get_id()}')[0], 'element_onsuccess' );
          if( !!(element_onsuccess && element_onsuccess.constructor && element_onsuccess.call && element_onsuccess.apply) ){
            element_onsuccess();
          }
        },
        error: function ( jqXHR, textStatus, errorThrown ){
          var element_onerror = \$.data( \$('#{$id}','#{$form->get_id()}')[0], 'element_onerror' );
          if( !!(element_onerror && element_onerror.constructor && element_onerror.call && element_onerror.apply) ){
            element_onerror();
          }

          if(\$.trim(errorThrown) != '') alert(textStatus+': '+errorThrown);
        },
        complete: function( jqXHR, textStatus ){
          var loading = \$.data(\$target[0],'loading');
          \$('#'+loading).remove();
        }
      });
      return false;
    });";
    return $eventjs;
  }

  /**
   * ABSTRACT - the function that actually renders the html field
   * @param  form $form form object
   * @return string        the field html
   */
  abstract public function render_field(form $form); // renders html

  /**
   * ABSTRACT - this function tells to the form if this element is a value that needs to be included into parent values() function call result
   * @return boolean include_me
   */
  abstract public function is_a_value();                // tells if component value is passed on the parent values() function call

  /**
   * alter request hook
   * @param  array &$request request array
   */
  public function alter_request(&$request){
    // implementing this function fields can change the request array
  }
  /**
   * after validate hook
   * @param  form $form form object
   */
  public function after_validate(form $form){
    // here field can do things after the validation has passed
  }
}


/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

/**
 * the "actionable" field element class (a button, a submit or a reset)
 * @abstract
 */
abstract class action extends field{

  /**
   * "use jqueryui button method on this element" flag
   * @var boolean
   */
  protected $js_button = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    if($this->js_button == TRUE){
      $id = $this->get_html_id();
      $this->add_js("\$('#{$id}','#{$form->get_id()}').button();");
    }
    parent::pre_render($form);
  }

  /**
   * is_a_value hook
   * @return boolean this is not a value
   */
  public function is_a_value(){
    return FALSE;
  }

  /**
   * validate function
   * @return boolean this field is always valid
   */
  public function valid() {
    return TRUE;
  }

}

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
  public function __construct($options = array(), $name = NULL) {
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

/**
 * the submit input type field class
 */
class submit extends clickable {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Submit';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"submit\" id=\"{$id}\" name=\"{$this->name}\" value=\"".$this->get_text($this->value)."\"{$attributes} />\n";
    return $output;
  }

}

/**
 * the button field class
 */
class button extends clickable {

  /**
   * element label
   * @var string
   */
  protected $label;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL){
    parent::__construct($options,$name);
    if(empty($this->label)) $this->label = $this->value;
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<button id=\"{$id}\" name=\"{$this->name}\"{$attributes} value=\"{$this->value}\">".$this->get_text($this->label)."</button>\n";
    return $output;
  }

}

/**
 * the image submit input type field class
 */
class image_button extends clickable {

  /**
   * image source
   * @var string
   */
  protected $src;

  /**
   * image alternate
   * @var string
   */
  protected $alt;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    $this->default_value = array(
      'x'=>-1,
      'y'=>-1,
    );

    parent::__construct($options, $name);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','value','src','alt'));
    //  value=\"{$this->value}\"
    $output = "<input id=\"{$id}\" name=\"{$this->name}\" type=\"image\" src=\"{$this->src}\" alt=\"{$this->alt}\"{$attributes} />\n";
    return $output;
  }

  /**
   * alter_request hook
   * @param  array $request request array
   */
  public function alter_request(&$request){
    foreach($request as $key => $val){
      //IMAGE BUTTONS HANDLE
      if(preg_match('/^(.*?)_(x|y)$/',$key,$matches) && $this->get_name() == $matches[1] ){
        //assume this is an input type="image"
        if( isset($request[$matches[1].'_'.(($matches[2] == 'x')?'y':'x')]) ){
          $request[$matches[1]] = array(
            'x'=>$request[$matches[1].'_x'],
            'y'=>$request[$matches[1].'_y'],
          );

          unset($request[$matches[1].'_x']);
          unset($request[$matches[1].'_y']);
        }
      }
    }
  }

}

/**
 * the reset button field class
 */
class reset extends action {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Reset';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"reset\" id=\"{$id}\" name=\"{$this->name}\" value=\"".$this->get_text($this->value)."\"{$attributes} />\n";
    return $output;
  }

}

/**
 * the value field class
 * this field is not rendered as part of the form, but the value is passed on form submission
 */
class value extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    $this->container_tag = '';
    $this->container_class = '';
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        an empty string
   */
  public function render_field(form $form) {
    return '';
  }

  /**
   * validate function
   * @return boolean this field is always valid
   */
  public function valid() {
    return TRUE;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * the markup field class.
 * this is not a value
 */
class markup extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element value
   */
  public function render_field(form $form) {
    $output = $this->value;
    return $output;
  }

  /**
   * validate function
   * @return boolean this field is always valid
   */
  public function valid() {
    return TRUE;
  }

  /**
   * is_a_value hook
   * @return boolean this is not a value
   */
  public function is_a_value(){
    return FALSE;
  }
}


/**
 * the progressbar field class
 */
class progressbar extends markup {

  /**
   * "indeterminate progressbar" flag
   * @var boolean
   */
  protected $indeterminate = FALSE;

  /**
   * "show label" flag
   * @var boolean
   */
  protected $show_label = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    if($this->indeterminate == TRUE || !is_numeric($this->value) ){
      $this->add_js("\$('#{$id}','#{$form->get_id()}').progressbar({ value: false });");
    }else if( $this->show_label == TRUE ){
      $this->add_js("
        \$('#{$id}','#{$form->get_id()}').progressbar({ value: parseInt({$this->value}) });
        \$('#{$id} .progress-label','#{$form->get_id()}').text('{$this->value}%');
      ");
    }else{
      $this->add_js("\$('#{$id}','#{$form->get_id()}').progressbar({ value: parseInt({$this->value}) });");
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();

    if($this->show_label == TRUE){
      $this->add_css("#{$form->get_id()} #{$id}.ui-progressbar {position: relative;}");
      $this->add_css("#{$form->get_id()} #{$id} .progress-label {position: absolute;left: 50%;top: 4px;}");
    }

    return "<div id=\"{$id}\"{$attributes}>".(($this->show_label == TRUE ) ? "<div class=\"progress-label\"></div>":"")."</div>\n";
  }
}

/**
 * the hidden input field class
 */
class hidden extends field {

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    $this->container_tag = '';
    $this->container_class = '';
    parent::__construct($options,$name);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();
    return "<input type=\"hidden\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * the text input field class
 */
class textfield extends field {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    if( is_array($this->value) ) $this->value = '';
    $output = "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"".htmlspecialchars($this->value)."\"{$attributes} />\n";
    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * the "autocomplete" text input field class
 */
class autocomplete extends textfield{

  /**
   * autocomplete path
   * @var mixed
   */
  protected $autocomplete_path = FALSE;

  /**
   * options for autocomplete (if autocomplete path was not provided)
   * @var array
   */
  protected $options = array();

  /**
   * minimum string length for autocomplete
   * @var integer
   */
  protected $min_length = 3;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options, $name = NULL){
    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' autocomplete';

    parent::__construct($options, $name);
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    $this->add_js("
      \$('#{$id}','#{$form->get_id()}')
      .bind( 'keydown', function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB && \$( this ).autocomplete( 'instance' ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        source: ".((!empty($this->options)) ? json_encode($this->options) : "'{$this->autocomplete_path}'").",
        minLength: {$this->min_length},
        focus: function() {
          return false;
        }
      });
    ");

    parent::pre_render($form);
  }
}

/**
 * the "masked" text input field class
 */
class maskedfield extends textfield{

  /**
   * input mask string
   * @var string
   */
  protected $mask;

  /**
   * jQuery Mask Plugin patterns
   * @var array
   */
  private $translation = array(
    '0'  =>  "\d",
    '9'  =>  "\d?",
    '#'  =>  "\d+",
    'A'  =>  "[a-zA-Z0-9]",
    'S'  =>  "[a-zA-Z]",
  );

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options, $name = NULL){
    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' maskedfield';

    parent::__construct($options, $name);
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').mask('{$this->mask}');");
    parent::pre_render($form);
  }

  /**
   * validate hook
   * @return boolean this TRUE if this element conforms to mask
   */
  public function valid() {
    $mask = $this->mask;
    $mask = preg_replace("(\[|\]|\(|\))","\\\1",$mask);
    foreach($this->translation as $search => $replace){
      $mask = str_replace($search, $replace, $mask);
    }
    $mask = '/^'.$mask.'$/';
    if(!preg_match($mask,$this->value)){
      $this->add_error($this->get_text("Value does not conform to mask"),__FUNCTION__);

      if($this->stop_on_first_error)
        return FALSE;
    }

    return parent::valid();
  }
}

/**
 * the textarea field class
 */
class textarea extends field {

  /**
   * rows
   * @var integer
   */
  protected $rows = 5;

  /**
   * resizable flag
   * @var boolean
   */
  protected $resizable = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    if($this->resizable == TRUE){
      $this->add_js("\$('#{$id}','#{$form->get_id()}').resizable({handles:\"se\"});");
    }
    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    $errors = $this->get_errors();
    if (!empty($errors)) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('name','id','value','rows','cols'));
    $output = "<textarea id=\"{$id}\" name=\"{$this->name}\" cols=\"{$this->size}\" rows=\"{$this->rows}\"{$attributes}>\n".$this->value."</textarea>";
    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * tinymce beautified textarea
 */
class tinymce extends textarea {
  /**
   * tinymce options
   * @var array
   */
  private $tinymce_options = array();

  /**
   * get tinymce options array
   * @return array tinymce options
   */
  public function &get_tinymce_options(){
    return $this->tinymce_options;
  }

  /**
   * set tinymce options array
   * @param array $options array of valid tinymce options
   */
  public function set_tinymce_options($options){
    $options = (array) $options;
    $options = array_filter($options, array($this,'is_valid_tinymce_option'));
    $this->tinymce_options = $options;

    return $this;
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->tinymce_options['selector'] = "#{$id}";
    $tinymce_options = new stdClass;
    foreach ($this->tinymce_options as $key => $value) {
      if( ! $this->is_valid_tinymce_option($key) ) continue;
      $tinymce_options->$key = $value;
    }
    $this->add_js("tinymce.init(".json_encode($tinymce_options).");");
    parent::pre_render($form);
  }

  /**
   * filters valid tinymce options
   * @param  string  $propertyname property name
   * @return boolean               TRUE if is a valid tinymce option
   */
  private function is_valid_tinymce_option($propertyname){
    // could be used to filter elements
    return TRUE;
  }
}


/**
 * the password input field class
 */
class password extends field {

  /**
   * "with confirmation" flag
   * @var boolean
   */
  protected $with_confirm = FALSE;

  /**
   * confirmation input label
   * @var string
   */
  protected $confirm_string = "Confirm password";

  /**
   * "include javascript strength check" flag
   * @var boolean
   */
  protected $with_strength_check = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    if($this->with_strength_check == TRUE){
      $id = $this->get_html_id();

      $this->add_js("
      \$('#{$id}','#{$form->get_id()}').keyup(function() {
        \$('#{$id}_result').html(

        (function(password){
            var strength = 0;
            if (password.length < 6) {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('short');
              return '".$this->get_text('Too short')."';
            }

            if (password.length > 7) strength += 1;
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))  strength += 1;
            if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))  strength += 1;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))  strength += 1;
            if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
            if (strength < 2 ){
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('weak');
              return '".$this->get_text('Weak')."';
            } else if (strength == 2 ) {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('good');
              return '".$this->get_text('Good')."';
            } else {
              \$('#{$id}_result').removeClass().addClass('password_strength_checker').addClass('strong');
              return '".$this->get_text('Strong')."';
            }
          })(\$('#{$id}','#{$form->get_id()}').val())

        );
      });");

      $this->add_css("#{$form->get_id()} .password_strength_checker.short{color:#FF0000;}");
      $this->add_css("#{$form->get_id()} .password_strength_checker.weak{color:#E66C2C;}");
      $this->add_css("#{$form->get_id()} .password_strength_checker.good{color:#2D98F3;}");
      $this->add_css("#{$form->get_id()} .password_strength_checker.strong{color:#006400;}");
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"password\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"\"{$attributes} />\n";
    if($this->with_confirm == TRUE){
      $output .= "<label for=\"{$id}-confirm\">".$this->get_text($this->confirm_string)."</label>";
      $output .= "<input type=\"password\" id=\"{$id}-confirm\" name=\"{$this->name}_confirm\" size=\"{$this->size}\" value=\"\"{$attributes} />\n";
    }
    if($this->with_strength_check){
      $output .= "<span id=\"{$id}_result\" class=\"password_strength_checker\"></span>";
    }
    return $output;
  }

  /**
   * validate hook
   * @return boolean check if element is valid
   */
  public function valid(){
    if($this->with_confirm == TRUE){
      if(!isset($_REQUEST["{$this->name}_confirm"]) || $_REQUEST["{$this->name}_confirm"] != $this->value ) {
        $this->add_error($this->get_text("The passwords do not match"),__FUNCTION__);

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

/**
 * the multivalues field class (a select, a radios or a checkboxes group)
 * @abstract
 */
abstract class field_multivalues extends field {

  /**
   * options array
   * @var array
   */
  protected $options = array();

  /**
   * get elements options array by reference
   * @return array element options
   */
  public function &get_options(){
    return $this->options;
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
      } else if(is_array($value)) {
        if( field_multivalues::has_key($needle, $value) == TRUE ){
          return TRUE;
        }
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
    if(!is_array($this->value) && !empty($this->value)){
      $check = $this->options_has_key($this->value);
      if(!$check) return FALSE;
    }else if(is_array($this->value)){
      $check = TRUE;
      foreach ($this->value as $key => $value) {
        $check &= $this->options_has_key($value);
      }
      if(!$check) {
        $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;
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

/**
 * the option element class
 */
class option extends element{

  /**
   * option label
   * @var string
   */
  protected $label;

  /**
   * option key
   * @var string
   */
  protected $key;

  /**
   * class constructor
   * @param string $key     key
   * @param string $label   label
   * @param array  $options build options
   */
  function __construct($key, $label, $options = array()) {
    $this->key = trim($key);
    $this->label = $label;

    foreach ($options as $key => $value) {
      $key = trim($key);
      if( property_exists(get_class($this), $key) )
        $this->$key = $value;
    }
  }

  /**
   * render the option
   * @param  select $form_field select field
   * @return string        the option html
   */
  public function render(select $form_field){
    $this->no_translation = $form_field->no_translation;
    $selected = '';
    $field_value = $form_field->get_value();
    if(is_array($field_value) || $form_field->is_multiple() == TRUE){
      if( !is_array($field_value) ) $field_value = array($field_value);
      $selected = in_array($this->key, array_values($field_value), TRUE) ? ' selected="selected"' : '';
    }else{
      $selected = ($this->key === $field_value) ? ' selected="selected"' : '';
    }
    $attributes = $this->get_attributes(array('value','selected'));
    $output = "<option value=\"{$this->key}\"{$selected}{$attributes}>".$this->get_text($this->label)."</option>\n";
    return $output;
  }

  /**
   * get the element key
   * @return mixed the element key
   */
  public function get_key(){
    return $this->key;
  }

   /**
   * set the element key
   * @param  mixed $label element key
   */
  public function set_key($key){
    $this->key = $key;

    return $this;
  }

  /**
   * get the element label
   * @return mixed the element label
   */
  public function get_label(){
    return $this->label;
  }

   /**
   * set the element label
   * @param  mixed $label element label
   */
  public function set_label($label){
    $this->label = $label;

    return $this;
  }



}


/**
 * the optgroup element class
 */
class optgroup extends element{

  /**
   * options array
   * @var array
   */
  protected $options;

  /**
   * element label
   * @var string
   */
  protected $label;

  /**
   * class constructor
   * @param string $label   label
   * @param array  $options options array
   */
  function __construct($label, $options) {
    $this->label = $label;

    if(isset($options['options'])){
      foreach ($options['options'] as $key => $value) {
        if($value instanceof option) {
          $this->add_option($value);
          $value->set_parent($this);
        } else {
          $this->add_option( new option($key , $value) );
        }
      }
      unset($options['options']);
    }

    foreach ($options as $key => $value) {
      $key = trim($key);
      if( property_exists(get_class($this), $key) )
        $this->$key = $value;
    }
  }

  /**
   * check if key is present into element options array
   * @param  mixed $needle element to find
   * @return boolean         TRUE if element is present
   */
  public function options_has_key($needle){
    return field_multivalues::has_key($needle,$this->options);
  }

  /**
   * add option
   * @param option $option option to add
   */
  public function add_option(option $option){
    $option->set_parent($this);
    $this->options[] = $option;
  }

  /**
   * render the optgroup
   * @param  select $form_field select field
   * @return string        the optgroup html
   */
  public function render(select $form_field){
    $this->no_translation = $form_field->no_translation;
    $attributes = $this->get_attributes(array('label'));
    $output = "<optgroup label=\"".$this->get_text($this->label)."\"{$attributes}>\n";
    foreach ($this->options as $option) {
      $output .= $option->render($form_field);
    }
    $output .= "</optgroup>\n";
    return $output;
  }
}

/**
 * the select field class
 */
class select extends field_multivalues {

  /**
   * multiple attribute
   * @var boolean
   */
  protected $multiple = FALSE;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options,$name) {

    if(isset($options['options'])){
      foreach($options['options'] as $k => $o){
        if( $o instanceof option || $o instanceof optgroup ){
          $o->set_parent($this);
          $this->options[] = $o;
        }else if(is_array($o)){
          $option = new optgroup( $k , array('options' => $o) );
          $option->set_parent($this);
          $this->options[] = $option;
        }else{
          $option = new option( $k , $o );
          $option->set_parent($this);
          $this->options[] = $option;
        }
      }
      unset($options['options']);
    }

    if(isset($options['default_value'])){
      if( !$this->is_multiple() && !(isset($options['multiple']) && $options['multiple']==TRUE) ){
        if(is_array($options['default_value'])) $options['default_value'] = reset($options['default_value']);
        $options['default_value'] = "".$options['default_value'];
      }else{
        if(!is_array($options['default_value'])) $options['default_value'] = array($options['default_value']);
        foreach( $options['default_value'] as $k => $v) {
          $options['default_value'][$k] = "".$v;
        }
      }
    }

    parent::__construct($options,$name);
  }

  /**
   * return field multiple attribute
   * @return boolean field is multiple
   */
  public function is_multiple(){
    return $this->multiple;
  }

  /**
   * set field multiple attribute
   * @param  boolean $multiple multiple attribute
   */
  public function set_multiple($multiple = TRUE){
    $this->multiple = ($multiple == TRUE);
    return $this;
  }

  /**
   * return field value
   * @return mixed field value
   */
  public function get_value(){
    return $this->value;
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $extra = ($this->multiple) ? ' multiple="multiple" size="'.$this->size.'" ' : '';
    $field_name = ($this->multiple) ? "{$this->name}[]" : $this->name;
    $output .= "<select name=\"{$field_name}\" id=\"{$id}\"{$extra}{$attributes}>\n";
    if(isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])){
      $output .= '<option disabled '.( isset($this->default_value) ? '' : 'selected').'>'.$this->attributes['placeholder'].'</option>';
    }
    foreach ($this->options as $key => $value) {
      $output .= $value->render($this);
    }
    $output .= "</select>\n";
    return $output;
  }
}

/**
 * the "selectmenu" select field class
 */
class selectmenu extends select{

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').selectmenu({width: 'auto' });");

    parent::pre_render($form);
  }
}

/*
 * the "Multiselect select" field class
 */
class multiselect extends select{

  private $leftOptions;
  private $rightOptions;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options,$name) {
      if(!is_array($options)) $options = array();
      $options['multiple'] = TRUE;
      parent::__construct($options,$name);

      $this->leftOptions = $this->options;
      $this->rightOptions = array();

      foreach ($this->get_default_value() as $value) {
        foreach( $this->leftOptions as $k => $v ){
          if( $v->get_key() == $value ){
            $this->rightOptions[] = clone $v;
            unset($this->leftOptions[$k]);
          }
        }
      }

      $this->set_attribute('style','width: 100%;');
  }

  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}_move_right, #{$id}_move_left','#{$form->get_id()}').click(function(evt){
      evt.preventDefault();
      var \$this = \$(this);
      var \$from = \$('#{$id}_from','#{$form->get_id()}');
      var \$to = \$('#{$id}_to','#{$form->get_id()}');

      if( /_move_right\$/i.test(\$this.attr('id')) ){
        \$from.find('option:selected').each(function(index,elem){ var \$elem = \$(elem); \$elem.appendTo(\$to); });
      }
      if( /_move_left\$/i.test(\$this.attr('id')) ){
        \$to.find('option:selected').each(function(index,elem){ var \$elem = \$(elem); \$elem.appendTo(\$from); });
      }
    });");

    $this->add_js("\$('#{$form->get_id()}').submit(function(evt){
      var \$to = \$('#{$id}_to','#{$form->get_id()}');
      \$to.find('option').each(function(index,elem){elem.selected=true;});
    });");

    parent::pre_render($form);
  }

  public function process($value = array()){
    parent::process($value);

    $this->leftOptions = $this->options;
    $this->rightOptions = array();

    $values = $this->get_value();
    foreach( array_values($values) as $keyval){
      foreach( $this->leftOptions as $k => $v ){
        if( $v->get_key() == $keyval ){
          $this->rightOptions[] = clone $v;
          unset($this->leftOptions[$k]);
        }
      }
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $extra = ' multiple="multiple" size="'.$this->size.'" ';
    $field_name = "{$this->name}[]";

    $output .= "<table id=\"{$id}-table\" border=0 colspan=0 cellpadding=0><tr><td style=\"width: 45%\">\n";
    $output .= "<select name=\"{$this->name}_from\" id=\"{$id}_from\"{$extra}{$attributes}>\n";
    if(isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])){
      $output .= '<option disabled '.( isset($this->default_value) ? '' : 'selected').'>'.$this->attributes['placeholder'].'</option>';
    }
    foreach ($this->leftOptions as $key => $value) {
      $output .= $value->render($this);
    }
    $output .= "</select>\n</td><td style=\"width: 10%\" align=\"center\">";

    $output .= '<div class="buttons">';
    $output .= "<button id=\"{$this->name}_move_right\">&gt;&gt;</button><br /><br />";
    $output .= "<button id=\"{$this->name}_move_left\">&lt;&lt;</button>";
    $output .= "</div>\n";

    $output .= "</td><td style=\"width: 45%\"><select name=\"{$field_name}\" id=\"{$id}_to\"{$extra}{$attributes}>\n";
    if(isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])){
      $output .= '<option disabled '.( isset($this->default_value) ? '' : 'selected').'>'.$this->attributes['placeholder'].'</option>';
    }
    foreach ($this->rightOptions as $key => $value) {
      $output .= $value->render($this);
    }
    $output .= "</select>\n</td></tr></table>";
    return $output;
  }
}


/**
 * the "slider" select field class
 */
class slider extends select{

  /**
   * show value on change
   * @var boolean
   */
  protected $with_val = FALSE;


  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options, $name = NULL){
    // get the "default_value" index value
    $values = form::array_get_values($this->default_value,$this->options);
    $oldkey_value = end($values);

    // flatten the options array ang get a numeric keyset
    // $this->options = form::array_flatten($this->options);
    $options['options'] = form::array_flatten($options['options']);

    // search the new index
    $this->value = $this->default_value = array_search($oldkey_value,$this->options);

    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' slider';

    if( isset($options['multiple']) ) $options['multiple'] = FALSE;

    parent::__construct($options, $name);
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $add_js = '';
    if($this->with_val == TRUE){
      $add_js .= "
      var text = \$( '#{$id}' )[ 0 ].options[ \$( '#{$id}' )[ 0 ].selectedIndex ].label;
      \$('#{$id}-show_val','#{$form->get_id()}').text( text );";
    }
    $this->add_js("
      \$('#{$id}-slider','#{$form->get_id()}').slider({
        min: 1,
        max: ".count($this->options).",
        value: \$( '#{$id}' )[ 0 ].selectedIndex + 1,
        slide: function( event, ui ) {
          \$( '#{$id}' )[ 0 ].selectedIndex = ui.value - 1;
          ".$add_js."
        }
      });
    \$( '#{$id}' ).change(function() {
      \$('#{$id}-slider').slider('value', this.selectedIndex + 1 );
    }).hide();");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form){
    $id = $this->get_html_id();
    $text =  isset($this->default_value) && $this->options_has_key($this->default_value) ? $this->options[ $this->default_value ]->get_label() : '';
    if(trim($text) == '' && count($this->options) > 0){
      $option = reset($this->options);
      $text = $option->get_label();
    }
    if(!preg_match( "/<div id=\"{$id}-slider\"><\/div>/i", $this->suffix )){
      $this->suffix = "<div id=\"{$id}-slider\"></div>" . (( $this->with_val == TRUE ) ? "<div id=\"{$id}-show_val\">{$text}</div>" : '') . $this->suffix;
    }
    return parent::render_field($form);
  }
}

/**
 * the radios group field class
 */
class radios extends field_multivalues {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '<div class="options">';
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    foreach ($this->options as $key => $value) {
      $attributes = $this->get_attributes();
      if(is_array($value) && isset($value['attributes'])){
        $attributes = $this->get_attributes_string($value['attributes'],array('type','name','id','value'));
      }
      if(is_array($value)){
        $value = $value['value'];
      }

      $checked = ($this->value == $key) ? ' checked="checked"' : '';
      $output .= "<label class=\"label-radio\" for=\"{$id}-{$key}\"><input type=\"radio\" id=\"{$id}-{$key}\" name=\"{$this->name}\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
    }
    $output .= '</div>';
    return $output;
  }
}

/**
 * the checkboxes group field class
 */
class checkboxes extends field_multivalues {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if(!is_array($this->default_value)) {
      $this->default_value = array($this->default_value);
    }

    $output = '<div class="options">';
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    foreach ($this->options as $key => $value) {
      $attributes = $this->get_attributes();
      if( $value instanceof checkbox ){
        $value->set_name("{$this->name}".(count($this->options)>1 ? "[]":""));
        $value->set_id("{$this->name}-{$key}");
        $output .= $value->render($form);
      }else{
        if(is_array($value) && isset($value['attributes'])){
          $attributes = $this->get_attributes_string($value['attributes'],array('type','name','id','value'));
        }
        if(is_array($value)){
          $value = $value['value'];
        }

        $checked = (is_array($this->default_value) && in_array($key, $this->default_value)) ? ' checked="checked"' : '';
        $output .= "<label class=\"label-checkbox\" for=\"{$id}-{$key}\"><input type=\"checkbox\" id=\"{$id}-{$key}\" name=\"{$this->name}".(count($this->options)>1 ? "[]" : "")."\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
      }
    }
    $output .= '</div>';
    return $output;
  }
}

/**
 * the single checkbox input field class
 */
class checkbox extends field {

  protected $text_position = 'after';

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    $this->value = NULL;
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $checked = ($this->value == $this->default_value) ? ' checked="checked"' : '';

    $this->label_class .= " label-" . $this->get_element_class_name();
    $this->label_class = trim($this->label_class);
    $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
    $output = "<label for=\"{$id}\"{$label_class}>".(($this->text_position == 'before') ? $this->get_text($this->title) : '')."<input type=\"checkbox\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->default_value}\"{$checked}{$attributes} /> ".(($this->text_position != 'before') ? $this->get_text($this->title) : '')."</label>\n";
    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * the file input field class
 */
class file extends field {

  /**
   * "file already uploaded" flag
   * @var boolean
   */
  protected $uploaded = FALSE;

  /**
   * file destination directory
   * @var string
   */
  protected $destination;

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    $form->set_attribute('enctype', 'multipart/form-data');

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','size'));

    $output .= "<input type=\"hidden\" name=\"{$this->name}\" value=\"{$this->name}\" />";
    $output .= "<input type=\"file\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\"{$attributes} />";
    return $output;
  }

  /**
   * process hook
   * @param  mixed $value value to set
   * @param  string $name file input name
   */
  public function process($value) {
    $this->value = array(
      'filepath' => (isset($value['filepath'])) ? $value['filepath'] : $this->destination .'/'. basename($_FILES[$this->get_name()]['name']),
      'filename' => (isset($value['filename'])) ? $value['filename'] : basename($_FILES[$this->get_name()]['name']),
      'filesize' => (isset($value['filesize'])) ? $value['filesize'] : $_FILES[$this->get_name()]['size'],
      'mimetype' => (isset($value['mimetype'])) ? $value['mimetype'] : $_FILES[$this->get_name()]['type'],
    );
    if(isset($value['uploaded'])){
      $this->uploaded = $value['uploaded'];
    }
    if ($this->valid()) {
      if( @move_uploaded_file($_FILES[$this->get_name()]['tmp_name'], $this->value['filepath']) == TRUE ){
        $this->uploaded = TRUE;
      }
    }
  }

  /**
   * check if file was uploaded
   * @return boolean TRUE if file was uploaded
   */
  public function is_uploaded(){
    return $this->uploaded;
  }

  /**
   * "required" validation function
   * @param  mixed $value the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_required($value = NULL) {
    if (!empty($value) &&
      (isset($value['filepath']) && !empty($value['filepath'])) &&
      (isset($value['filename']) && !empty($value['filename'])) &&
      (isset($value['mimetype']) && !empty($value['mimetype'])) &&
      (isset($value['filesize']) && $value['filesize']>=0)
    ) {
      return TRUE;
    } else {
      return "<em>%t</em> is required";
    }
  }

  /**
   * validate function
   * @return boolean this field is always valid
   */
  public function valid() {
    if ($this->uploaded) {
      return TRUE;
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

/**
 * the date select group field class
 */
class date extends field {

  /**
   * granularity (day / month / year)
   * @var string
   */
  protected $granularity = 'day';

  /**
   * start year
   * @var integer
   */
  protected $start_year;

  /**
   * end year
   * @var integer
   */
  protected $end_year;

  /**
   * "use js selects" flag
   * @var boolean
   */
  protected $js_selects = FALSE;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {

    $this->start_year = date('Y')-100;
    $this->end_year = date('Y')+100;
    $this->default_value = array(
      'year'=>date('Y'),
      'month'=>date('m'),
      'day'=>date('d'),
    );

    parent::__construct($options, $name);
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    if($this->js_selects == TRUE){
      $id = $this->get_html_id();
      $this->add_js("\$('#{$id} select[name=\"{$this->name}[year]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
      if($this->granularity != 'year'){
        $this->add_js("\$('#{$id} select[name=\"{$this->name}[month]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
        if($this->granularity != 'month'){
          $this->add_js("\$('#{$id} select[name=\"{$this->name}[day]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
        }
      }
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','size','day','month','year'));

    $output .= "<div id=\"{$id}\"{$attributes}>";

    if($this->granularity!='year' && $this->granularity!='month'){
      $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
      if(isset($this->attributes['day']) && is_array($this->attributes['day'])){
        if($this->disabled == TRUE) $this->attributes['day']['disabled']='disabled';
        $attributes = $this->get_attributes_string($this->attributes['day'],array('type','name','id','value'));
      }
      $output .= "<select name=\"{$this->name}[day]\"{$attributes}>";
      for($i=1;$i<=31;$i++){
        $selected = ($i == $this->value['day']) ? ' selected="selected"' : '';
        $output .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
      }
      $output .= "</select>";
    }
    if($this->granularity!='year'){
      $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
      if(isset($this->attributes['month']) && is_array($this->attributes['month'])){
        if($this->disabled == TRUE) $this->attributes['month']['disabled']='disabled';
        $attributes = $this->get_attributes_string($this->attributes['month'],array('type','name','id','value'));
      }
      $output .= "<select name=\"{$this->name}[month]\"{$attributes}>";
      for($i=1;$i<=12;$i++){
        $selected = ($i == $this->value['month']) ? ' selected="selected"' : '';
        $output .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
      }
      $output .= "</select>";
    }
    $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
    if(isset($this->attributes['year']) && is_array($this->attributes['year'])){
      if($this->disabled == TRUE) $this->attributes['year']['disabled']='disabled';
      $attributes = $this->get_attributes_string($this->attributes['year'],array('type','name','id','value'));
    }
    $output .= "<select name=\"{$this->name}[year]\"{$attributes}>";
    for($i=$this->start_year;$i<=$this->end_year;$i++){
      $selected = ($i == $this->value['year']) ? ' selected="selected"' : '';
      $output .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
    }
    $output .= "</select>";
    $output .= "</div>";

    return $output;
  }

  /**
   * process hook
   * @param  array $value value to set
   */
  public function process($value) {
    $this->value = array(
      'year' => $value['year'],
    );
    if($this->granularity!='year'){
      $this->value['month'] = $value['month'];
      if($this->granularity!='month'){
        $this->value['day'] = $value['day'];
      }
    }
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    $year = $this->value['year'];
    $month = isset($this->value['month']) ? $this->value['month'] : 1;
    $day = isset($this->value['day']) ? $this->value['day'] : 1;

    if( !checkdate( $month , $day , $year ) ) {
      $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;
      $this->add_error(str_replace("%t",$titlestr,$this->get_text("%t: Invalid date")), __FUNCTION__);

      if($this->stop_on_first_error)
        return FALSE;
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

  /**
   * get start timestamp
   * @return int start timestamp
   */
  public function ts_start(){
    $year = $this->value['year'];
    $month = isset($this->value['month']) ? $this->value['month'] : 1;
    $day = isset($this->value['day']) ? $this->value['day'] : 1;

    return mktime(0,0,0,$month,$day,$year);
  }

  /**
   * get end timestamp
   * @return int end timestamp
   */
  public function ts_end(){
    $year = $this->value['year'];
    $month = isset($this->value['month']) ? $this->value['month'] : 1;
    $day = isset($this->value['day']) ? $this->value['day'] : 1;

    return mktime(23,59,59,$month,$day,$year);
  }

  /**
   * get value as a date string
   * @return string date value
   */
  public function value_string(){
    $value = $this->values();
    $out = (($value['year'] < 10) ? '0':'').((int) $value['year']);
    if($this->granularity!='year'){
      $out .= '-'.(($value['month'] < 10) ? '0':'').((int) $value['month']);
      if($this->granularity!='month'){
        $out .= '-'.(($value['day'] < 10) ? '0':'').((int) $value['day']);
      }
    }
    return $out;
  }
}

/**
 * the datepicker text input field class
 */
class datepicker extends field {

  /**
   * date format
   * @var string
   */
  protected $date_format = 'yy-mm-dd';

  /**
   * change month flag
   * @var boolean
   */
  protected $change_month = FALSE;

  /**
   * change year flag
   * @var boolean
   */
  protected $change_year = FALSE;

  /**
   * min date
   * @var string
   */
  protected $mindate = '-10Y';

  /**
   * max date
   * @var string
   */
  protected $maxdate = '+10Y';

  /**
   * year range
   * @var string
   */
  protected $yearrange = '-10:+10';

  /**
   * disabled dates array
   * @var array
   */
  protected $disabled_dates = array(); // an array of date strings compliant to $date_format

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    $dateFormat = $this->date_format;
    $changeMonth = ($this->change_month) ? 'true'  :'false';
    $changeYear = ($this->change_year == TRUE) ? 'true'  :'false';

    $this->add_js(
      preg_replace("/\s+/"," ",str_replace("\n","","".
        ((count($this->disabled_dates)>0) ? "var disabled_dates_array_{$form->get_id()}_{$id} = ".json_encode((array) $this->disabled_dates).";" : "")."
            \$('#{$id}','#{$form->get_id()}').datepicker({
            dateFormat: '{$this->date_format}',
            ".( (count($this->disabled_dates)>0) ? "beforeShowDay: function(date){
              var string = $.datepicker.formatDate('{$this->date_format}', date);
              return [ disabled_dates_array_{$form->get_id()}_{$id}.indexOf(string) == -1 ];
            },": "")."
            changeMonth: {$changeMonth},
            changeYear: {$changeYear},
            minDate: \"{$this->mindate}\",
            maxDate: \"{$this->maxdate}\",
            yearRange: \"{$this->yearrange}\"
          });")));

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $output = "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"{$this->value}\"{$attributes} />\n";

    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * the time select group field class
 */
class time extends field {

  /**
   * granularity (seconds / minutes / hours)
   * @var string
   */
  protected $granularity = 'seconds';

  /**
   * "use js selects" flag
   * @var boolean
   */
  protected $js_selects = FALSE;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {

    $this->default_value = array(
      'hours'=>0,
      'minutes'=>0,
      'seconds'=>0,
    );

    parent::__construct($options, $name);
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    if($this->js_selects == TRUE){
      $id = $this->get_html_id();

      $this->add_js("\$('#{$id} select[name=\"{$this->name}[hours]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
      if($this->granularity != 'hours'){
        $this->add_js("\$('#{$id} select[name=\"{$this->name}[minutes]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");

        if($this->granularity != 'minutes'){
          $this->add_js("\$('#{$id} select[name=\"{$this->name}[seconds]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
        }
      }
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','size','hours','minutes','seconds'));

    $output .= "<div id=\"{$id}\"{$attributes}>";

    $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
    if(isset($this->attributes['hours']) && is_array($this->attributes['hours'])){
      if($this->disabled == TRUE) $this->attributes['hours']['disabled']='disabled';
      $attributes = $this->get_attributes_string($this->attributes['hours'],array('type','name','id','value'));
    }
    $output .= "<select name=\"{$this->name}[hours]\"{$attributes}>";
    for($i=0;$i<=23;$i++){
      $selected = ($i == $this->value['hours']) ? ' selected="selected"' : '';
      $output .= "<option value=\"{$i}\"{$selected}>".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>";
    }
    $output .= "</select>";
    if($this->granularity != 'hours'){

      $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
      if(isset($this->attributes['minutes']) && is_array($this->attributes['minutes'])){
        if($this->disabled == TRUE) $this->attributes['minutes']['disabled']='disabled';
        $attributes = $this->get_attributes_string($this->attributes['minutes'],array('type','name','id','value'));
      }
      $output .= "<select name=\"{$this->name}[minutes]\"{$attributes}>";
      for($i=0;$i<=59;$i++){
        $selected = ($i == $this->value['minutes']) ? ' selected="selected"' : '';
        $output .= "<option value=\"{$i}\"{$selected}>".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>";
      }
      $output .= "</select>";
      if($this->granularity != 'minutes'){

        $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
        if(isset($this->attributes['seconds']) && is_array($this->attributes['seconds'])){
          if($this->disabled == TRUE) $this->attributes['seconds']['disabled']='disabled';
          $attributes = $this->get_attributes_string($this->attributes['seconds'],array('type','name','id','value'));
        }
        $output .= "<select name=\"{$this->name}[seconds]\"{$attributes}>";
        for($i=0;$i<=59;$i++){
          $selected = ($i == $this->value['seconds']) ? ' selected="selected"' : '';
          $output .= "<option value=\"{$i}\"{$selected}>".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>";
        }
        $output .= "</select>";
      }
    }
    $output .= "</div>";

    return $output;
  }

  /**
   * process hook
   * @param  array $value value to set
   */
  public function process($value) {
    $this->value = array(
      'hours' => $value['hours'],
    );
    if($this->granularity!='hours'){
      $this->value['minutes'] = $value['minutes'];
      if($this->granularity!='minutes'){
        $this->value['seconds'] = $value['seconds'];
      }
    }
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {

    $check = TRUE;
    $check &= ($this->value['hours']>=0 && $this->value['hours']<=23);

    if($this->granularity != 'hours'){
      $check &= ($this->value['minutes']>=0 && $this->value['minutes']<=59);

      if($this->granularity != 'minutes'){
        $check &= ($this->value['seconds']>=0 && $this->value['seconds']<=59);
      }
    }

    if( ! $check ) {
      $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;
      $this->add_error(str_replace("%t",$titlestr,$this->get_text("%t: Invalid time")), __FUNCTION__);

      if($this->stop_on_first_error)
        return FALSE;
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

  /**
   * get value as a date string
   * @return string date value
   */
  public function value_string(){
    $value = $this->values();
    $out = (($value['hours'] < 10) ? '0':'').((int) $value['hours']);

    if($this->granularity!='hours'){
      $out .= ':'.(($value['minutes'] < 10) ? '0':'').((int) $value['minutes']);
      if($this->granularity!='minutes'){
        $out .= ':'.(($value['seconds'] < 10) ? '0':'').((int) $value['seconds']);
      }
    }

    return $out;
  }
}

/**
 * the datetime select group field class
 */
class datetime extends tag_container {

  /**
   * date sub element
   * @var date
   */
  protected $date = NULL;

  /**
   * time sub_element
   * @var time
   */
  protected $time = NULL;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);

    unset($options['title']);
    $options['container_tag'] = '';

    $options['type'] = 'date';
    $this->date = new date($options,$name.'_date');

    $options['type'] = 'time';
    $this->time = new time($options,$name.'_time');
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_css("#{$id} div.date,#{$id} div.time{display: inline-block;margin-right: 5px;}");
    parent::pre_render($form);

    $this->date->pre_render($form);
    $this->time->pre_render($form);
  }

  /**
   * preprocess hook . it simply calls the sub elements preprocess
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    $this->date->preprocess($process_type);
    $this->time->preprocess($process_type);
  }

  /**
   * process hook . it simply calls the sub elements process
   * @param  array $values value to set
   */
  public function process($values) {
    $this->date->process($values[$this->get_name().'_date']);
    $this->time->process($values[$this->get_name().'_time']);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    return $this->date->valid() && $this->time->valid();
  }

  /**
   * renders form errors
   * @return string errors as an html <li> list
   */
  public function show_errors() {
    return (trim($this->date->show_errors() . $this->time->show_errors()) == '') ? '' : trim($this->date->show_errors() . $this->time->show_errors());
  }

  /**
   * resets the sub elements
   */
  public function reset() {
    $this->date->reset();
    $this->time->reset();
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();

    $this->tag = 'div';
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $required = ($this->validate->has_value('required')) ? '<span class="required">*</span>' : '';
    $requiredafter = $requiredbefore = $required;
    if($this->required_position == 'before') { $requiredafter = ''; $requiredbefore = $requiredbefore.' '; }
    else { $requiredbefore = ''; $requiredafter = ' '.$requiredafter; }

    if(!empty($this->title)){
      if ( $this->tooltip == FALSE ) {
        $this->label_class .= " label-" .$this->get_element_class_name();
        $this->label_class = trim($this->label_class);
        $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
        $output .= "<label for=\"{$id}\"{$label_class}>{$requiredbefore}".$this->get_text($this->title)."{$requiredafter}</label>\n";
      } else {
        if( !in_array('title', array_keys($this->attributes)) ){
          $this->attributes['title'] = strip_tags($this->get_text($this->title).$required);
        }

        $id = $this->get_html_id();
        $form->add_js("\$('#{$id}','#{$form->get_id()}').tooltip();");
      }
    }
    $output .= $this->date->render($form);
    $output .= $this->time->render($form);
    $output .= "</{$this->tag}>\n";
    return $output;
  }

  /**
   * return field value
   * @return array field value
   */
  public function values() {
    return array(
      'date'=> $this->date->values(),
      'time'=> $this->time->values(),
      'datetime' => $this->date->value_string().' '.$this->time->value_string(),
    );
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }

  /**
   * on_add_return overload
   * @return string 'parent'
   */
  protected function on_add_return(){
    return 'parent';
  }
}

/**
 * the spinner number input field class
 */
class spinner extends field {

  /**
   * minimum value
   * @var null
   */
  protected $min = NULL;

  /**
   * maximum value
   * @var null
   */
  protected $max = NULL;

  /**
   * step value
   * @var integer
   */
  protected $step = 1;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    $js_options = '';
    if( is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min ){
      $js_options = "{min: $this->min, max: $this->max, step: $this->step}";
    }

    $this->add_js("\$('#{$id}','#{$form->get_id()}').attr('type','text').spinner({$js_options});");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    $html_options = '';
    if( is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min ){
      $html_options = " min=\"{$this->min}\" max=\"{$this->max}\" step=\"{$this->step}\"";
    }

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','value','min','max','step'));

    $output .= "<input type=\"number\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"{$this->value}\"{$html_options}{$attributes} />\n";

    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * the recaptcha field class
 */
class recaptcha extends field {

  /**
   * public key
   * @var string
   */
  protected $publickey = '';

  /**
   * private key
   * @var string
   */
  protected $privatekey = '';

  /**
   * "already validated" flag
   * @var boolean
   */
  protected $already_validated = FALSE;

  /**
   * process hook
   * @param  mixed $values value to set
   */
  public function process($values){
    parent::process($values);
    if(isset($values['already_validated'])) $this->already_validated = $values['already_validated'];
  }

  /**
   * check if element is already validated
   * @return boolean TRUE if element has already been validated
   */
  public function is_already_validated(){
    return $this->already_validated;
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    if(!function_exists('recaptcha_get_html')) return '';
    return recaptcha_get_html($this->publickey);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    if($this->already_validated == TRUE) return TRUE;
    if(isset($this->value['already_validated']) && $this->value['already_validated'] == TRUE) return TRUE;
    if(!function_exists('recaptcha_check_answer')){
      $this->already_validated = TRUE;
      return TRUE;
    }

    if(!is_array($this->value)) $this->value = array();

    // if something is missing...
    $this->value += array(
      'challenge_field' => '',
      'response_field' => '',
    );

    $resp = recaptcha_check_answer ($this->privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $this->value["challenge_field"],
                                    $this->value["response_field"]);
    if(!$resp->is_valid){
      $this->add_error($this->get_text("Recaptcha response is not valid"), __FUNCTION__);
    }else{
      $this->already_validated = TRUE;
      $this->value['already_validated'] = TRUE;
    }

    return $resp->is_valid;
  }

  /**
   * is_a_value hook
   * @return boolean this is not a value
   */
  public function is_a_value(){
    return FALSE;
  }

  /**
   * alter_request hook
   * @param array $request request array
   */
  public function alter_request(&$request){
    foreach($request as $key => $val){
      //RECAPTCHA HANDLE
      if( preg_match('/^recaptcha\_(challenge|response)\_field$/',$key,$matches) ){
        $fieldname = $this->get_name();
        if(!empty($request["recaptcha_challenge_field"])){
          $request[$fieldname]["challenge_field"] = $request["recaptcha_challenge_field"];
          unset($request["recaptcha_challenge_field"]);
        }
        if(!empty($request["recaptcha_response_field"])){
          $request[$fieldname]["response_field"] = $request["recaptcha_response_field"];
          unset($request["recaptcha_response_field"]);
        }
      }
    }
  }

  /**
   * after_validate hook
   * @param  form $form form object
   */
  public function after_validate(form $form){
    $_SESSION[$form->get_id()]['steps'][$form->get_current_step()][$this->get_name()] = $this->values();
    $_SESSION[$form->get_id()]['steps'][$form->get_current_step()][$this->get_name()]['already_validated'] = $this->is_already_validated();
  }

}
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

/**
 * a field that contains other fields class
 * @abstract
 */
abstract class fields_container extends field {

  /**
   * keeps fields insert order
   * @var array
   */
  protected $insert_field_order = array();

  /**
   * element fields
   * @var array
   */
  protected $fields = array();

  /**
   * get the fields array by reference
   * @return array        the array of field elements
   */
  public function &get_fields(){
    return $this->fields;
  }

  /**
   * get the form fields by type
   * @param  array $field_types field types
   * @return array              fields in the element
   */
  public function get_fields_by_type($field_types){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof fields_container){
        $out = array_merge($out, $field->get_fields_by_type($field_types));
      }else{
        if($field instanceof field && in_array($field->get_type(), $field_types)) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  /**
   * get the step fields by type and name
   * @param  array $field_types field types
   * @param  string $name       field name
   * @return array              fields in the element matching the search criteria
   */
  public function get_fields_by_type_and_name($field_types,$name){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof fields_container){
        $out = array_merge($out, $field->get_fields_by_type_and_name($field_types,$name));
      }else{
        if($field instanceof field && in_array($field->get_type(), $field_types) && $field->get_name() == $name) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  /**
   * get field by name
   * @param  string  $field_name field name
   * @return element subclass field object
   */
  public function get_field($field_name){
    return isset($this->fields[$field_name]) ? $this->fields[$field_name] : NULL;
  }

  /**
   * add field to form
   * @param string  $name  field name
   * @param mixed   $field field to add, can be an array or a field subclass
   */
  public function add_field($name, $field) {
    if (!is_object($field)) {
      $field_type = "Degami\\PHPFormsApi\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      if(!class_exists($field_type)){
        throw new Exception("Error adding field. Class $field_type not found", 1);
      }
      $field = new $field_type($field, $name);
    }else{
      $field->set_name($name);
    }

    $field->set_parent($this);

    $this->fields[$name] = $field;
    $this->insert_field_order[] = $name;

    if( !method_exists($field, 'on_add_return') ) {
      if(  $field instanceof fields_container && !( $field instanceof datetime || $field instanceof geolocation ) )
        return $field;
      return $this;
    }
    if($field->on_add_return() == 'this') return $field;
    return $this;
  }

  /**
   * remove field from form
   * @param  string $field field name
   */
  public function remove_field($name){
    unset($this->fields[$name]);
    if(($key = array_search($name, $this->insert_field_order)) !== false) {
        unset($this->insert_field_order[$key]);
    }
    return $this;
  }

  /**
   * return form elements values into this element
   * @return array form values
   */
  public function values() {
    $output = array();
    foreach ($this->get_fields() as $name => $field) {
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
   * preprocess hook
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    foreach ($this->get_fields() as $field) {
      $field->preprocess($process_type);
    }
  }

  /**
   * process (set) the fields value
   * @param  mixed $values value to set
   */
  public function process($values) {
    foreach ($this->get_fields() as $name => $field) {
      if( $field instanceof fields_container ) {
        $this->get_field($name)->process($values);
      } else if ( preg_match_all('/(.*?)(\[(.*?)\])+/i',$name, $matches, PREG_SET_ORDER) ) {
        if(isset($values[ $matches[0][1] ])){
          $value = $values[ $matches[0][1] ];
          foreach($matches as $match){
            if(isset($value[ $match[3] ])){
              $value = $value[ $match[3] ];
            }
          }
        }
        $field->process($value);
      }else if(isset($values[$name])){
        $this->get_field($name)->process($values[$name]);
      } else if( $field instanceof checkbox ){
        // no value on request[name] && field is a checkbox - process anyway with an empty value
        $this->get_field($name)->process(NULL);
      } else if( $field instanceof select ){
        if($field->is_multiple()) $this->get_field($name)->process(array());
        else $this->get_field($name)->process(NULL);
      } else if( $field instanceof field_multivalues ){
        // no value on request[name] && field is a multivalue (eg. checkboxes ?) - process anyway with an empty value
        $this->get_field($name)->process(array());
      }
    }
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    foreach ($this->get_fields() as $name => $field) {
      if( is_object($field) && method_exists ( $field , 'pre_render' ) ){
        $field->pre_render($form);
      }
    }
    parent::pre_render($form);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    $valid = TRUE;
    foreach ($this->get_fields() as $field) {
      if (!$field->valid()) {
        // not returnig FALSE to let all the fields to be validated
        $valid = FALSE;
      }
    }
    return $valid;
  }

  /**
   * renders form errors
   * @return string errors as an html <li> list
   */
  public function show_errors() {
    $output = "";
    foreach ($this->get_fields() as $field) {
      $output .= $field->show_errors();
    }
    return $output;
  }

  /**
   * resets the fields
   */
  public function reset() {
    foreach ($this->get_fields() as $field) {
      $field->reset();
    }
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }

  /**
   * alter_request hook
   * @param array $request request array
   */
  public function alter_request(&$request){
    foreach($this->get_fields() as $field){
      $field->alter_request($request);
    }
  }

  /**
   * after_validate hook
   * @param  form $form form object
   */
  public function after_validate(form $form){
    foreach($this->get_fields() as $field){
      $field->after_validate($form);
    }
  }

  /**
   * on_add_return overload
   * @return string 'this'
   */
  protected function on_add_return(){
    return 'this';
  }
}

/**
 * a field container that can specify container's html tag
 */
class tag_container extends fields_container {
  /**
   * container html tag
   * @var string
   */
  protected $tag = 'div';

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(),$name = NULL){
    parent::__construct($options,$name);

    if($this->attributes['class'] == 'tag_container'){ // if set to the default
      $this->attributes['class'] = $this->tag.'_container';
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $insertorder = array_flip($this->insert_field_order);
    $weights = array();
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    if( count( $this->get_fields() ) > 0 )
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());
    foreach ($this->get_fields() as $name => $field) {
      $output .= $field->render($form);
    }
    $output .= "</{$this->tag}>\n";

    return $output;
  }
}

/**
 * a fieldset field container
 */
class fieldset extends fields_container {

  /**
   * collapsible flag
   * @var boolean
   */
  protected $collapsible = FALSE;

  /**
   * collapsed flag
   * @var boolean
   */
  protected $collapsed = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    static $js_collapsible_added = FALSE;
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->collapsible) {
      $this->attributes['class'] .= ' collapsible';
      if ($this->collapsed) {
        $this->attributes['class'] .= ' collapsed';
      } else {
        $this->attributes['class'] .= ' expanded';
      }

      if( !$js_collapsible_added ){
        $this->add_js("
          \$('fieldset.collapsible').find('legend').css({'cursor':'pointer'}).click(function(evt){
            evt.preventDefault();
            var \$this = \$(this);
            \$this.parent().find('.fieldset-inner').toggle( 'blind', {}, 500, function(){
              if(\$this.parent().hasClass('expanded')){
                \$this.parent().removeClass('expanded').addClass('collapsed');
              }else{
                \$this.parent().removeClass('collapsed').addClass('expanded');
              }
            });
          });
          \$('fieldset.collapsible.collapsed .fieldset-inner').hide();");
        $js_collapsible_added = TRUE;
      }
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    $attributes = $this->get_attributes();
    $output .= "<fieldset id=\"{$id}\"{$attributes}>\n";
    if (!empty($this->title)) {
      $output .= "<legend>".$this->get_text($this->title)."</legend>\n";
    }

    $insertorder = array_flip($this->insert_field_order);
    $weights = array();
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    if( count( $this->get_fields() ) > 0 )
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());

    $output .= "<div class=\"fieldset-inner\">\n";
    foreach ($this->get_fields() as $name => $field) {
      $output .= $field->render($form);
    }
    $output .= "</div></fieldset>\n";
    return $output;
  }
}

/**
 * a field container subdivided in groups
 * @abstract
 */
abstract class fields_container_multiple extends fields_container{

  /**
   * element subelements
   * @var array
   */
  protected $partitions = array();

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
    $this->partitions[] = array('title'=>$title,'fieldnames'=>array());

    return $this;
  }

  /**
   * add field to element
   * @param string  $name     field name
   * @param mixed   $field    field to add, can be an array or a field subclass
   * @param integer $partitions_index index of partition to add field to
   */
  public function add_field($name, $field, $partitions_index = 0) {
    if (!is_object($field)) {
      $field_type = "Degami\\PHPFormsApi\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
      if(!class_exists($field_type)){
        throw new Exception("Error adding field. Class $field_type not found", 1);
      }
      $field = new $field_type($field, $name);
    }else{
      $field->set_name($name);
    }

    $field->set_parent($this);

    $this->fields[$name] = $field;
    $this->insert_field_order[$partitions_index][] = $name;
    if(!isset($this->partitions[$partitions_index])){
      $this->partitions[$partitions_index] = array('title'=>'','fieldnames'=>array());
    }
    $this->partitions[$partitions_index]['fieldnames'][] = $name;

    if( !method_exists($field, 'on_add_return') ) {
      if(  $field instanceof fields_container && !( $field instanceof datetime || $field instanceof geolocation ) )
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
  public function get_partition_fields($partitions_index){
    $out = array();
    $fieldsnames = $this->partitions[$partitions_index]['fieldnames'];
    foreach($fieldsnames as $name){
      $out[$name] = $this->get_field($name);
    }
    return $out;
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

/**
 * a "tabbed" field container
 */
class tabs extends fields_container_multiple {

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').tabs();");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    $tabs_html = array();
    $tab_links = array();
    foreach($this->partitions as $tabindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$tabindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_partition_fields($tabindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($tabindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($tabindex));

      $addclass_tab = ' class="tabel '.( $this->partition_has_errors($tabindex, $form) ? 'has-errors' : '' ).'"';
      $tab_links[$tabindex] = "<li{$addclass_tab}><a href=\"#{$id}-tab-inner-{$tabindex}\">".$this->get_text($this->partitions[$tabindex]['title'])."</a></li>";
      $tabs_html[$tabindex] = "<div id=\"{$id}-tab-inner-{$tabindex}\" class=\"tab-inner".( $this->partition_has_errors($tabindex, $form) ? ' has-errors' : '' )."\">\n";
      foreach ($this->get_partition_fields($tabindex) as $name => $field) {
        $tabs_html[$tabindex] .= $field->render($form);
      }
      $tabs_html[$tabindex] .= "</div>\n";
    }
    $output .= "<ul>".implode("",$tab_links)."</ul>".implode("",$tabs_html). "</div>\n";

    return $output;
  }

  public function add_tab($title){
    return $this->add_partition($title);
  }
}

/**
 * an accordion field container
 */
class accordion extends fields_container_multiple {

  /**
   * height style
   * @var string
   */
  protected $height_style = 'auto';

  /**
   * active tab
   * @var numeric
   */
  protected $active = '0';


  /**
   * collapsible
   * @var boolean
   */
  protected $collapsible = FALSE;


  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $collapsible = ($this->collapsible) ? 'true':'false';
    $this->add_js("\$('#{$id}','#{$form->get_id()}').accordion({  heightStyle: \"{$this->height_style}\", active: {$this->active}, collapsible: {$collapsible} });");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    foreach($this->partitions as $accordionindex => $accordion){
      $insertorder = array_flip($this->insert_field_order[$accordionindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_partition_fields($accordionindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($accordionindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($accordionindex));


      $addclass_tab = ' class="tabel '.( $this->partition_has_errors($accordionindex, $form) ? 'has-errors' : '' ).'"';
      $output .= "<h3{$addclass_tab}>".$this->get_text($this->partitions[$accordionindex]['title'])."</h3>";
      $output .= "<div id=\"{$id}-tab-inner-{$accordionindex}\" class=\"tab-inner".( $this->partition_has_errors($accordionindex, $form) ? ' has-errors' : '' )."\">\n";
      foreach ($this->get_partition_fields($accordionindex) as $name => $field) {
        $output .= $field->render($form);
      }
      $output .= "</div>\n";
    }
    $output .= "</div>\n";

    return $output;
  }

  public function add_accordion($title){
    return $this->add_partition($title);
  }
}

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
  protected $deltas = array();

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
    $output = array();

    $fields_with_delta = $this->get_fields_with_delta();
    usort($fields_with_delta, 'sortable_container::orderby_delta');

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
    $out = array();
    foreach($this->get_fields() as $key => $field){
      $out[$key]=array('field'=> $field,'delta'=>$this->deltas[$key]);
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

/**
 * a sortable field container
 */
class sortable extends sortable_container{

  /**
   * add field to element
   * @param string  $name     field name
   * @param mixed   $field    field to add, can be an array or a field subclass
   */
  public function add_field($name, $field, $_p = NULL) {
    //force every field to have its own tab.
    $this->deltas[$name] = count($this->get_fields());
    return parent::add_field($name, $field, $this->deltas[$name]);
  }

  /**
   * remove field from form
   * @param  string $field field name
   */
  public function remove_field($name, $_p = NULL){
    parent::remove_field($name, $this->deltas['name']);
    unset($this->deltas[$name]);
    return $this;
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').sortable({
        placeholder: \"ui-state-highlight\",
        stop: function( event, ui ) {
          \$(this).find('input[type=hidden][name*=\"sortable-delta-\"]').each(function(index,elem){
            \$(elem).val(index);
          });
        }
      });");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $handle_position = trim(strtolower($this->get_handle_position()));

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    foreach($this->partitions as $partitionindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$partitionindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_partition_fields($partitionindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($partitionindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($partitionindex));

      $output .= "<div id=\"{$id}-sortable-{$partitionindex}\"  class=\"tab-inner ui-state-default\">\n".(($handle_position == 'right') ? '' : "<span class=\"ui-icon ui-icon-arrowthick-2-n-s\" style=\"display: inline-block;\"></span>")."<div style=\"display: inline-block;\">\n";
      foreach ($this->get_partition_fields($partitionindex) as $name => $field) {
        $output .= $field->render($form);
      }
      $output .= "<input type=\"hidden\" name=\"{$id}-delta-{$partitionindex}\" value=\"{$partitionindex}\" />\n";
      $output .= "</div>".(($handle_position == 'right') ? "<span class=\"ui-icon ui-icon-arrowthick-2-n-s\" style=\"display: inline-block;float: right;\"></span>" : '')."</div>\n";
    }
    $output .= "</div>\n";

    return $output;
  }
}

/**
 * a sortable table rows field container
 */
class sortable_table extends sortable_container{

  /**
   * table header
   * @var array
   */
  protected $table_header = array();

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("
      \$('#{$id} tbody','#{$form->get_id()}').sortable({
        helper: function(e, ui) {
          ui.children().each(function() {
            \$(this).width($(this).width());
          });
          return ui;
        },
        placeholder: \"ui-state-highlight\",
        stop: function( event, ui ) {
          \$(this).find('input[type=hidden][name*=\"sortable-delta-\"]').each(function(index,elem){
            \$(elem).val(index);
          });
        }
      });");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $handle_position = trim(strtolower($this->get_handle_position()));

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<table id=\"{$id}\"{$attributes}>\n";

    if(!empty($this->table_header) ){
      if(!is_array($this->table_header)) {
        $this->table_header = array($this->table_header);
      }

      $output .= "<thead>\n";
      if($handle_position != 'right') $output .= "<th>&nbsp;</th>";
      foreach($this->table_header as $th){
        $output .= "<th>".$this->get_text($th)."</th>";
      }
      if($handle_position == 'right') $output .= "<th>&nbsp;</th>";
      $output .= "</thead>\n";
    }

    $output .= "<tbody>\n";
    foreach($this->partitions as $trindex => $tr){
      $insertorder = array_flip($this->insert_field_order[$trindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_partition_fields($trindex) as $key => $elem) {
        /** @var field $elem */
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($trindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($trindex));

      $output .= "<tr id=\"{$id}-sortable-{$trindex}\"  class=\"tab-inner ui-state-default\">\n".(($handle_position == 'right') ? '' : "<td width=\"16\" style=\"width: 16px;\"><span class=\"ui-icon ui-icon-arrowthick-2-n-s\"></span></td>")."\n";
      foreach ($this->get_partition_fields($trindex) as $name => $field) {
        /** @var field $field */
        $fieldhtml = $field->render($form);
        if( trim($fieldhtml) != '' )
          $output .= "<td>".$fieldhtml."</td>\n";
      }
      $output .= "<input type=\"hidden\" name=\"{$id}-delta-{$trindex}\" value=\"{$trindex}\" />\n";
      $output .= (($handle_position == 'right') ? "<td width=\"16\" style=\"width: 16px;\"><span class=\"ui-icon ui-icon-arrowthick-2-n-s\"></span></td>" : '')."</tr>\n";
    }
    $output .= "</tbody>\n</table>\n";

    return $output;
  }
}

/**
 * a table field container
 */
class table_container extends fields_container_multiple{

  /**
   * table header
   * @var array
   */
  protected $table_header = array();

  /**
   * attributes for TRs or TDs
   * @var array
   */
  protected $col_row_attributes = array();

  /**
   * set table header array
   * @param array $table_header table header elements array
   */
  public function set_table_header(array $table_header){
    $this->table_header = $table_header;
    return $this;
  }

  /**
   * get table header array
   * @return array table header array
   */
  public function get_table_header(){
    return $this->table_header;
  }

  /**
   * set rows / cols attributes array
   * @param array $col_row_attributes attributes array
   */
  public function set_col_row_attributes(array $col_row_attributes){
    $this->col_row_attributes = $col_row_attributes;
    return $this;
  }

  /**
   * get rows / cols attributes array
   * @return array attributes array
   */
  public function get_col_row_attributes(){
    return $this->col_row_attributes;
  }

  /**
   * add a new table row
   */
  public function add_row(){
    $this->add_partition('table_row_'.$this->num_partitions());
    return $this;
  }

  /**
   * return number of table rows
   * @return integer number of table rows
   */
  public function num_rows(){
    return $this->num_partitions();
  }


  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $table_matrix = array();
    $rows = 0;

    foreach($this->partitions as $trindex => $tr){
      $table_matrix[$rows] = array();
      $cols = 0;
      foreach ($this->get_partition_fields($trindex) as $name => $field) {
        $table_matrix[$rows][$cols] = '';
        if(isset($this->col_row_attributes[$rows][$cols])){
          if( is_array($this->col_row_attributes[$rows][$cols]) ){
            $this->col_row_attributes[$rows][$cols] = $this->get_attributes_string( $this->col_row_attributes[$rows][$cols] );
          }
          $table_matrix[$rows][$cols] = $this->col_row_attributes[$rows][$cols];
        }
        $cols++;
      }
      $rows++;
    }

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<table id=\"{$id}\"{$attributes}>\n";

    if(!empty($this->table_header) ){
      if(!is_array($this->table_header)) {
        $this->table_header = array($this->table_header);
      }

      $output .= "<thead>\n";
      foreach($this->table_header as $th){
        if(is_array($th)){
          $th_attributes = '';
          if(!empty($th['attributes'])){
            $th_attributes = $this->get_attributes_string($th['attributes']);
          }
          $output .= "<th{$th_attributes}>".$this->get_text($th['value'])."</th>";
        }else{
          $output .= "<th>".$this->get_text($th)."</th>";
        }
      }
      $output .= "</thead>\n";
    }

    $output .= "<tbody>\n";
    $rows = 0;
    foreach($this->partitions as $trindex => $tr){
      $insertorder = array_flip($this->insert_field_order[$trindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_partition_fields($trindex) as $key => $elem) {
        /** @var field $elem */
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($trindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($trindex));

      $output .= "<tr id=\"{$id}-row-{$trindex}\">\n";
      $cols = 0;
      foreach ($this->get_partition_fields($trindex) as $name => $field) {
        /** @var field $field */
        $fieldhtml = $field->render($form);
        if( trim($fieldhtml) != '' ){
          $td_attributes = '';
          if(!empty($table_matrix[$rows][$cols])){
            $td_attributes = $table_matrix[$rows][$cols];
          }
          $output .= "<td{$td_attributes}>".$fieldhtml."</td>\n";
        }
        $cols++;
      }
      $output .= "</tr>\n";
      $rows++;
    }
    $output .= "</tbody>\n</table>\n";

    return $output;
  }
}

/**
 * the pupload field class
 */
class plupload extends field {

  /**
   * filters
   * @var array
   */
  protected $filters = array();

  /**
   * upload.php url
   * @var string
   */
  protected $url     = ''; // url upload.php

  /**
   * Moxie.swf url
   * @var string
   */
  protected $swf_url = ''; // url Moxie.swf

  /**
   * Moxie.xap url
   * @var string
   */
  protected $xap_url = ''; // url Moxie.xap

  /**
   * process hook
   * @param  mixed $value value to set
   */
  public function process($value) {
    $this->value = json_decode($value);
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $form_id = $form->get_id();

    $this->add_js("
      var {$id}_files_remaining = 0;
      $('#{$id}_uploader').pluploadQueue({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        chunk_size : '1mb',
        unique_names : true,

        // Resize images on client-side if we can
        resize : {width : 320, height : 240, quality : 90},

        url : '{$this->url}',
        flash_swf_url : '{$this->swf_url}',
        silverlight_xap_url : '{$this->xap_url}',
        filters : ".json_encode($this->filters).",

        // PreInit events, bound before any internal events
        preinit : {
            Init: function(up, info) {
            },

            UploadFile: function(up, file) {
                // You can override settings before the file is uploaded
                // up.setOption('url', 'upload.php?id=' + file.id);
                // up.setOption('multipart_params', {param1 : 'value1', param2 : 'value2'});
            }
        },

        // Post init events, bound after the internal events
        init : {

            FileUploaded: function(up, file, info) {
                // Called when file has finished uploading
                response = JSON.parse( info.response )

                if(file.status == plupload.DONE && response.result == null){
                  var value = \$.trim( \$('#{$id}_uploaded_json').val() );
                  if(value != '') {value = JSON.parse( value );}
                  else value = [];
                  if(value == null) value = [];
                  var obj = {temppath: response.temppath, name: file.name};
                  value.push( obj );

                  \$('#{$id}_uploaded_json').val( JSON.stringify(value) );
                }
            },

            FilesRemoved: function(up, files) {
              plupload.each(files, function(file) {
                {$id}_files_remaining--;
              });
              if({$id}_files_remaining == 0){
                \$('#{$form_id} input[type=submit]').removeAttr('disabled');
              }
            },

            FilesAdded: function(up, files) {
              \$('#{$form_id} input[type=submit]').attr('disabled','disabled');
              plupload.each(files, function(file) {
                {$id}_files_remaining++;
              });
            },

            UploadComplete: function(up, file, info) {
              \$('#{$form_id} input[type=submit]').removeAttr('disabled');
              {$id}_files_remaining = 0;
            },

            Error: function(up, args) {
                // Called when error occurs
                log('[Error] ', args);
            }
        }
    });


    function log() {
        var str = '';

        plupload.each(arguments, function(arg) {
            var row = '';

            if (typeof(arg) != 'string') {
                plupload.each(arg, function(value, key) {
                    // Convert items in File objects to human readable form
                    if (arg instanceof plupload.File) {
                        // Convert status to human readable
                        switch (value) {
                            case plupload.QUEUED:
                                value = 'QUEUED';
                                break;

                            case plupload.UPLOADING:
                                value = 'UPLOADING';
                                break;

                            case plupload.FAILED:
                                value = 'FAILED';
                                break;

                            case plupload.DONE:
                                value = 'DONE';
                                break;
                        }
                    }

                    if (typeof(value) != 'function') {
                        row += (row ? ', ' : '') + key + '=' + value;
                    }
                });

                str += row + ' ';
            } else {
                str += arg + ' ';
            }
        });

        var \$log = \$('#{$id}_log');
        \$('<div>'+str+'</div>').appendTo(\$log)
    }");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form){
    $id = $this->get_html_id();

    return "<div id=\"{$id}_uploader\"><p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p></div>
    <div id=\"{$id}_log\"></div>
    <input type=\"hidden\" id=\"{$id}_uploaded_json\" name=\"{$this->name}\" value=\"".json_encode($this->value)."\" />";
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}

/**
 * the geolocation field class
 */
class geolocation extends tag_container {

  /**
   * latitude
   * @var float
   */
  protected $latitude;

  /**
   * longitude
   * @var float
   */
  protected $longitude;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);

    $defaults = isset($options['default_value']) ? $options['default_value'] : array('latitude' => 0, 'longitude' => 0);

    unset($options['title']);
    unset($options['prefix']);
    unset($options['suffix']);
    $options['container_tag'] = '';

    if(!isset($options['size']))
    $options['size'] = 5;

    $options['type'] = 'textfield';
    $options['suffix'] = $this->get_text('latitude').' ';
    $options['default_value'] = (is_array($defaults) && isset($defaults['latitude'])) ? $defaults['latitude'] : 0;
    $this->latitude = new textfield($options,$name.'_latitude');

    $options['type'] = 'textfield';
    $options['suffix'] = $this->get_text('longitude').' ';
    $options['default_value'] = (is_array($defaults) && isset($defaults['longitude'])) ? $defaults['longitude'] : 0;
    $this->longitude = new textfield($options,$name.'_longitude');
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    parent::pre_render($form);

    $this->latitude->pre_render($form);
    $this->longitude->pre_render($form);
  }

  /**
   * preprocess hook . it simply calls the sub elements preprocess
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    $this->latitude->preprocess($process_type);
    $this->longitude->preprocess($process_type);
  }

  /**
   * process hook . it simply calls the sub elements process
   * @param  array $values value to set
   */
  public function process($values) {
    $this->latitude->process($values[$this->get_name().'_latitude']);
    $this->longitude->process($values[$this->get_name().'_longitude']);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    return $this->latitude->valid() && $this->longitude->valid();
  }


  /**
   * renders form errors
   * @return string errors as an html <li> list
   */
  public function show_errors() {
    return (trim($this->latitude->show_errors() . $this->longitude->show_errors()) == '') ? '' : trim($this->latitude->show_errors() . $this->longitude->show_errors());
  }


  /**
   * resets the sub elements
   */
  public function reset() {
    $this->latitude->reset();
    $this->longitude->reset();
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();

    $this->tag = 'div';
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $required = ($this->validate->has_value('required')) ? '<span class="required">*</span>' : '';
    $requiredafter = $requiredbefore = $required;
    if($this->required_position == 'before') { $requiredafter = ''; $requiredbefore = $requiredbefore.' '; }
    else { $requiredbefore = ''; $requiredafter = ' '.$requiredafter; }

    if(!empty($this->title)){
      if ( $this->tooltip == FALSE ) {
        $this->label_class .= " label-" .$this->get_element_class_name();
        $this->label_class = trim($this->label_class);
        $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
        $output .= "<label for=\"{$id}\"{$label_class}>{$requiredbefore}".$this->get_text($this->title)."{$requiredafter}</label>\n";
      } else {
        if( !in_array('title', array_keys($this->attributes)) ){
          $this->attributes['title'] = strip_tags($this->get_text($this->title).$required);
        }

        $id = $this->get_html_id();
        $form->add_js("\$('#{$id}','#{$form->get_id()}').tooltip();");
      }
    }
    $output .= $this->latitude->render($form);
    $output .= $this->longitude->render($form);
    $output .= "</{$this->tag}>\n";
    return $output;
  }

  /**
   * return field value
   * @return array field value
   */
  public function values() {
    return array(
      'latitude'=> $this->latitude->values(),
      'longitude'=> $this->longitude->values(),
    );
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }

  /**
   * on_add_return overload
   * @return string 'parent'
   */
  protected function on_add_return(){
    return 'parent';
  }
}

/**
 * the google maps geolocation field class
 */
class gmaplocation extends geolocation {

  /**
   * "current location" button
   * @var button
   */
  protected $current_location_btn;


  /**
   * zoom
   * @var integer
   */
  protected $zoom = 8;

  /**
   * scrollwheel
   * @var boolean
   */
  protected $scrollwheel = FALSE;

  /**
   * map width
   * @var string
   */
  protected $mapwidth = '100%';

  /**
   * map height
   * @var string
   */
  protected $mapheight = '500px';

  /**
   * marker title
   * @var null
   */
  protected $markertitle = NULL;

  /**
   * map type - one of:
   * google.maps.MapTypeId.HYBRID,
   * google.maps.MapTypeId.ROADMAP,
   * google.maps.MapTypeId.SATELLITE,
   * google.maps.MapTypeId.TERRAIN
   * @var string
   */
  protected $maptype = 'google.maps.MapTypeId.ROADMAP';

  /**
   * enable geocode box
   * @var boolean
   */
  protected $with_geocode = FALSE;

  /**
   * enable current location button
   * @var boolean
   */
  protected $with_current_location = FALSE;

  /**
   * input type where latitude and longitude are stored (hidden / textfield)
   * @var string
   */
  protected $lat_lon_type = 'hidden';

  /**
   * textfield subelement for geocode box
   * @var null
   */
  protected $geocode_box = NULL;

  /**
   * textarea subelement for reverse geocoding informations
   * @var null
   */
  protected $reverse_geocode_box = NULL;

  /**
   * "show map" flag
   * @var boolean
   */
  protected $with_map = TRUE;

  /**
   * enable reverse geociding information box
   * @var boolean
   */
  protected $with_reverse = FALSE;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    $defaults = isset($options['default_value']) ? $options['default_value'] : array('latitude' => 0, 'longitude' => 0);

    unset($options['title']);
    unset($options['prefix']);
    unset($options['suffix']);
    $options['container_tag'] = '';

    $opt = $options;
    $opt['type'] = 'hidden';
    $opt['attributes']['class'] = 'latitude';
    if($this->lat_lon_type == 'textfield') $opt['type'] = 'textfield';
    $opt['default_value'] = (is_array($defaults) && isset($defaults['latitude'])) ? $defaults['latitude'] : 0;
    if($this->lat_lon_type == 'textfield') $this->latitude = new textfield($opt,$name.'_latitude');
    else $this->latitude = new hidden($opt,$name.'_latitude');

    $opt = $options;
    $opt['type'] = 'hidden';
    $opt['attributes']['class'] = 'longitude';
    if($this->lat_lon_type == 'textfield') $opt['type'] = 'textfield';
    $opt['default_value'] = (is_array($defaults) && isset($defaults['longitude'])) ? $defaults['longitude'] : 0;
    if($this->lat_lon_type == 'textfield') $this->longitude = new textfield($opt,$name.'_longitude');
    else $this->longitude = new hidden($opt,$name.'_longitude');

    if($this->with_geocode == TRUE){
      $opt = $options;
      $opt['type'] = 'textfield';
      $opt['size'] = 50;
      $opt['attributes']['class'] = 'geocode';
      $opt['default_value'] = (is_array($defaults) && isset($defaults['geocodebox'])) ? $defaults['geocodebox'] : '';
      $this->geocode_box = new textfield($opt,$name.'_geocodebox');
    }

    if( $this->with_reverse == TRUE ){
      $opt = $options;
      $opt['type'] = 'textarea';
      $opt['attributes']['class'] = 'reverse';
      $opt['default_value'] = (is_array($defaults) && isset($defaults['reverse_geocodebox'])) ? $defaults['reverse_geocodebox'] : '';
      $this->reverse_geocode_box = new textarea($opt,$name.'_reverse_geocodebox');
    }

    if($this->with_current_location == TRUE){
      $opt = $options;
      $opt['type'] = 'button';
      $opt['size'] = 50;
      $opt['attributes']['class'] = 'current_location';
      $opt['default_value'] = $this->get_text('Current Location');
      $this->current_location_btn = new button($opt,$name.'_current_location_btn');
    }
  }


  /**
   * preprocess hook . it simply calls the sub elements preprocess
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    parent::preprocess($process_type);
    if($this->with_geocode == TRUE){
      $this->geocode_box->preprocess($process_type);
    }
    if($this->with_reverse == TRUE){
      $this->reverse_geocode_box->preprocess($process_type);
    }
  }


  /**
   * process hook . it simply calls the sub elements process
   * @param  array $values value to set
   */
  public function process($values) {
    parent::process($values);
    if($this->with_geocode == TRUE){
      $this->geocode_box->process($values[$this->get_name().'_geocodebox']);
    }
    if($this->with_reverse == TRUE){
      $this->reverse_geocode_box->process($values[$this->get_name().'_reverse_geocodebox']);
    }
  }

  /**
   * return field value
   * @return array field value
   */
  public function values() {
    $out = parent::values();
    if($this->with_geocode == TRUE){
      $out += array( 'geocodebox' => $this->geocode_box->values() );
    }
    if($this->with_reverse == TRUE){
      $out += array( 'reverse_geocodebox' => $this->reverse_geocode_box->values() );
    }
    return $out;
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    if($this->with_geocode == TRUE){
      $update_map_func = "";
      if($this->with_map == TRUE){
        $update_map_func = "
        var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
        var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
        marker.setPosition( new google.maps.LatLng( lat, lng ) );
        map.panTo( new google.maps.LatLng( lat, lng ) );
        ";
      }

      $this->add_js("
          var {$id}_api_endpoint = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
          \$('#{$id}_geocodebox').autocomplete({
            source: function (request, response) {
                jQuery.get({$id}_api_endpoint+\$('#{$id}_geocodebox').val(), {
                    query: request.term
                }, function (data) {
                  response($.map( data.results, function( item ) {
                      return {
                          label: item.formatted_address,
                          id: item.geometry.location.lat+'|'+item.geometry.location.lng
                      }
                  }));
                });
            },
            minLength: 5,
            select: function( event, ui ) {
              var tmp = ui.item.id.split('|');
              var lat = tmp[0];
              var lng = tmp[1];

              \$('input[name=\"{$id}_latitude\"]','#{$id}').val( lat );
              \$('input[name=\"{$id}_longitude\"]','#{$id}').val( lng );
              ".(($this->with_reverse == TRUE) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."

              {$update_map_func}

            }
          });
      ");
    }

    if($this->with_map == TRUE){
      $this->add_css("#{$form->get_id()} #{$id}-map {width: {$this->mapwidth}; height: {$this->mapheight}; }");
      $this->add_js("
        var {$id}_latlng = {lat: ".$this->latitude->values().", lng: ".$this->longitude->values()."};

        var {$id}_map = new google.maps.Map(document.getElementById('{$id}-map'), {
          center: {$id}_latlng,
          mapTypeId: {$this->maptype},
          scrollwheel: ".($this->scrollwheel ? 'true' : 'false').",
          zoom: {$this->zoom}
        });
        var {$id}_marker = new google.maps.Marker({
          map: {$id}_map,
          draggable: true,
          animation: google.maps.Animation.DROP,
          position: {$id}_latlng,
          title: '".(($this->markertitle == NULL) ? "lat: ".$this->latitude->values().", lng: ".$this->longitude->values() : $this->markertitle)."'
        });
        \$.data( \$('#{$id}-map')[0] , 'map_obj', {$id}_map);
        \$.data( \$('#{$id}-map')[0] , 'marker_obj', {$id}_marker);

        google.maps.event.addListener({$id}_marker, 'dragend', function() {
          var mapdiv = {$id}_marker.map.getDiv();
          \$('input[name=\"{$id}_latitude\"]','#'+\$(mapdiv).parent().attr('id')).val( {$id}_marker.getPosition().lat() );
          \$('input[name=\"{$id}_longitude\"]','#'+\$(mapdiv).parent().attr('id')).val( {$id}_marker.getPosition().lng() );
          ".(($this->with_reverse == TRUE) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."
        });
      ");

      if($this->lat_lon_type == 'textfield'){
        $this->add_js("
            \$('input[name=\"{$id}_latitude\"],input[name=\"{$id}_longitude\"]','#{$id}').change(function(evt){
              var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
              var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
              var lat = \$('input[name=\"{$id}_latitude\"]','#{$id}').val();
              var lng = \$('input[name=\"{$id}_longitude\"]','#{$id}').val();
              marker.setPosition( new google.maps.LatLng( lat, lng ) );
              map.panTo( new google.maps.LatLng( lat, lng ) );
            });
        ");
      }

    }

    if( $this->with_reverse == TRUE ){
        $this->add_js("
            var {$id}_geocoder = new google.maps.Geocoder;
            \$('#{$id}').bind('lat_lon_updated',function(evt){
              var latlng = {lat: parseFloat( \$('input[name=\"{$id}_latitude\"]','#{$id}').val() ), lng: parseFloat( \$('input[name=\"{$id}_longitude\"]','#{$id}').val() )};
              {$id}_geocoder.geocode({'location': latlng}, function(results, status) {
                if (status === 'OK') {
                  \$('#{$id}_reverse_geocodebox').val( JSON.stringify(results) );
                } else {
                  \$('#{$id}_reverse_geocodebox').val('Geocoder failed due to: ' + status);
                }
              });
            });
        ");

        if($this->lat_lon_type == 'textfield'){
          $this->add_js("
              \$('input[name=\"{$id}_latitude\"],input[name=\"{$id}_longitude\"]','#{$id}').change(function(evt){
                \$('#{$id}').trigger('lat_lon_updated');
              });
          ");
        }
    }

    if($this->with_current_location == TRUE){
        $update_map_func = "";
        if($this->with_map == TRUE){
          $update_map_func = "
            var map = \$.data( \$('#{$id}-map')[0] , 'map_obj');
            var marker = \$.data( \$('#{$id}-map')[0] , 'marker_obj');
            marker.setPosition( new google.maps.LatLng( lat, lng ) );
            map.panTo( new google.maps.LatLng( lat, lng ) );
          ";
        }
        $this->add_js("
            \$('#{$id}_current_location_btn').click(function(evt){
              evt.preventDefault();
              var lat = \$('input[name=\"{$id}_latitude\"]','#{$id}').val();
              var lng = \$('input[name=\"{$id}_longitude\"]','#{$id}').val();

              if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                  lat = position.coords.latitude;
                  lng = position.coords.longitude;
                  \$('input[name=\"{$id}_latitude\"]','#{$id}').val(lat);
                  \$('input[name=\"{$id}_longitude\"]','#{$id}').val(lng);
                  ".(($this->with_reverse == TRUE) ? "\$('#{$id}').trigger('lat_lon_updated');":"")."

                  {$update_map_func}
                }, function() {
                  /*handleLocationError();*/
                });
              }
            });
        ");
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();

    $this->tag = 'div';
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $required = ($this->validate->has_value('required')) ? '<span class="required">*</span>' : '';
    $requiredafter = $requiredbefore = $required;
    if($this->required_position == 'before') { $requiredafter = ''; $requiredbefore = $requiredbefore.' '; }
    else { $requiredbefore = ''; $requiredafter = ' '.$requiredafter; }

    if(!empty($this->title)){
      if ( $this->tooltip == FALSE ) {
        $this->label_class .= " label-" .$this->get_element_class_name();
        $this->label_class = trim($this->label_class);
        $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
        $output .= "<label for=\"{$id}\"{$label_class}>{$requiredbefore}".$this->get_text($this->title)."{$requiredafter}</label>\n";
      } else {
        if( !in_array('title', array_keys($this->attributes)) ){
          $this->attributes['title'] = strip_tags($this->get_text($this->title).$required);
        }

        $id = $this->get_html_id();
        $form->add_js("\$('#{$id}','#{$form->get_id()}').tooltip();");
      }
    }

    if($this->with_geocode == TRUE){
      $output .= $this->geocode_box->render($form); // ."<button id=\"{$id}_searchbox_btn\">".$this->get_text('search')."</button>";
    }

    if($this->with_map == TRUE){
      $mapattributes = ' class="gmap"';
      $output .= "<div id=\"{$id}-map\"{$mapattributes}></div>\n";
    }

    $output .= $this->latitude->render($form);
    $output .= $this->longitude->render($form);

    if($this->with_current_location == TRUE){
      $output .= $this->current_location_btn->render($form);
    }

    if($this->with_reverse == TRUE){
      $output .= $this->reverse_geocode_box->render($form);
    }

    $output .= "</{$this->tag}>\n";
    return $output;
  }
}

class nestable extends fields_container {
  public $level = 0;
  public $childnum = 0;
  public $tag = 'ol';
  public $tagclass = 'dd-list';
  public $children = array();
  public $fields_panel = NULL;
  public $maxDepth = 5;
  public static $_groupcounter = 0;
  public static $_css_rendered = FALSE;
  public $group = 0;

  public function __construct($options = array(), $name = NULL){
    parent::__construct($options, $name);
    $this->fields_panel = new tag_container(array(
      'type' => 'tag_container',
      'tag' => 'div',
      'container_class' => '',
      'container_tag' => '',
      'prefix' => '<div class="dd-panel"><div class="dd-handle" style="vertical-align: top;display: inline-block;">&nbsp;</div><div class="dd-content">',
      'suffix' => '</div></div>',
      'attributes' => array('class' => 'level-'.$this->level),
    ),'panel-'.$this->get_level().'-'.$this->get_name());

    parent::add_field($this->fields_panel->get_name(), $this->fields_panel);

    $this->group = nestable::$_groupcounter++;
  }

  public function get_level(){
    return $this->level;
  }

  public function add_child($tag = NULL, $tagclass = NULL){
    if($tag == NULL) $tag = $this->tag;
    if($tagclass == NULL) $tagclass = $this->tagclass;

    $nextchild = new nestable(array(
      'type' => 'nestable',
      'level' => $this->level+1,
      'tag' => $tag,
      'container_class' => '',
      'container_tag' => '',
      'attributes' => array('class' => $tagclass),
      'childnum' => $this->num_children(),
    ),
    //'leaf-'.$this->get_level().'-'.$this->num_children()
    $this->get_name().'-leaf-'. $this->num_children()
    );

    $this->children[] = $nextchild;
    parent::add_field($nextchild->get_name(), $nextchild);

    return $this->children[$this->num_children()-1];
  }

  public function num_children() {
    return count($this->get_children());
  }

  public function has_children(){
    return $this->num_children() > 0;
  }

  public function &get_child($numchild){
    return isset($this->children[$numchild]) ? $this->children[$numchild] : FALSE;
  }

  public function &get_children(){
    return $this->children;
  }

  public function add_field($name, $field){
    $field_type = NULL;
    if (!is_object($field)) {
      $field_type = "Degami\\PHPFormsApi\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
    }else{
      $field_type = get_class($field);
    }

    if(!class_exists($field_type)){
      throw new Exception("Error adding field. Class $field_type not found", 1);
    }

    $fakefield = new $field_type($field, 'fake_'.$name);
    if($fakefield instanceof fields_container && !( $fakefield instanceof geolocation || $fakefield instanceof datetime ) ){
      throw new Exception("Can't add a fields_container to a tree_container.", 1);
    }

    $this->fields_panel->add_field($name, $field);
    return $this;
  }

  /**
   * remove field from form
   * @param  string $field field name
   */
  public function remove_field($name){
    $this->fields_panel->remove_field($name);
    return $this;
  }

  public function process($values){
    parent::process( $values );
    if(isset($values[$this->get_name()])){
      $this->value = json_decode($values[$this->get_name()], TRUE);
      //$this->value[0]['values'] = nestable::find_by_id($this->value[0]['id']);
    }
  }

  private function get_panel_by_id($nestableid){
    if($this->get_html_id() == $nestableid) return $this->fields_panel;
    foreach ($this->get_children() as $key => $child) {
      $return = $child->get_panel_by_id($nestableid);
      if( $return != FALSE ) return $return;
    }
    return FALSE;
  }

  private static function create_values_array( $tree, nestable $nestablefield ){
    $out = array();
    $panel = $nestablefield->get_panel_by_id($tree['id']);
    if( $panel instanceof fields_container ){
      //$out[$tree['id']]['value'] = $panel->values();
      $out['value'] = $panel->values();
      if(isset($tree['children'])){
        foreach($tree['children'] as $child){
          //$out[$tree['id']]['children'][] = nestable::create_values_array($child, $nestablefield);
          $out['children'][] = nestable::create_values_array($child, $nestablefield);
        }
      }
    }
    return $out;
  }

  public function values(){
    if($this->value) {
      // return $this->value;
      // var_dump($this->value);die();
      $out = array();
      foreach($this->value as $tree){
        // $out = array_merge($out, nestable::create_values_array($tree, $this) );
        $out[] = nestable::create_values_array($tree, $this);
      }
      return $out;
    }
    return parent::values();
  }

  public function render_field(form $form){
    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    $this->attributes['class'] .= ' '.$this->tagclass;
    $id = $this->get_html_id();

    $attributes = $this->get_attributes();
    $out = "";
    if($this->get_level() == 0) $out .= "<div class=\"dd\" id=\"{$id}\"><{$this->tag}{$attributes}>";
    $out .= '<li class="dd-item level-'.$this->level.' child-'.$this->childnum.'" data-id="'.$id.'">';
    $out .= $this->fields_panel->render($form);
    if( $this->has_children() ) {
      $out .= "<{$this->tag} {$attributes}>";
      $children = $this->get_children();
      foreach ($children as $key => &$child) {
        $out .= $child->render($form);
      }
      $out .= "</{$this->tag}>";
    }
    $out .= '</li>';

    if($this->get_level() == 0) $out .= "</{$this->tag}></div><textarea name=\"{$this->get_name()}\" id=\"{$id}-output\" style=\"display: none; width: 100%; height: 200px;\"></textarea>";

    return $out;
  }

  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    if($this->get_level() == 0){

    $this->add_js(preg_replace("/\s+/"," ",str_replace("\n","","".
      "\$('#{$id}','#{$form->get_id()}').data('output', \$('#{$id}-output'));
       \$('#{$id}','#{$form->get_id()}').nestable({group: {$this->group}, maxDepth: {$this->maxDepth} }).on('change', function(e){
        var list   = e.length ? e : $(e.target),
        output = list.data('output');
        if (window.JSON) {
            output.val(window.JSON.stringify(list.nestable('serialize')));
        } else {
            output.val('JSON browser support required for this.');
        }
      }).trigger('change');"
    )));

      if(!nestable::$_css_rendered){

    $this->add_css('
.dd { position: relative; display: block; margin: 0; padding: 0; list-style: none; font-size: 13px; line-height: 20px; }

.dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
.dd-list .dd-list { padding-left: 30px; }
.dd-collapsed .dd-list { display: none; }
.dd-item,.dd-empty,.dd-placeholder { display: block; position: relative; margin: 10px 0 0 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }

.dd-handle { display: block; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
    background: #fafafa;
    background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:         linear-gradient(top, #fafafa 0%, #eee 100%);
    -webkit-border-radius: 3px;
            border-radius: 3px;
    box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-handle:hover { color: #2ea8e5; background: #fff; }

.dd-item > button { display: block; position: relative; cursor: pointer; z-index: 20; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
.dd-item > button:before { content: \'+\'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
.dd-item > button[data-action="collapse"]:before { content: \'-\'; }

.dd-placeholder,
.dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; display: block; }
.dd-empty {
    border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
    background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                      -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-image:    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                         -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-image:         linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                              linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
    background-size: 60px 60px;
    background-position: 0 0, 30px 30px;
}

.dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
.dd-dragel  > .dd-panel > .dd-item .dd-handle { margin-top: 0; }
.dd-dragel .dd-handle {
    -webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
            box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
}

.dd-panel{ position: relative; }
.dd-content { display: block; min-height: 30px; margin: 5px 0; padding: 5px 10px 5px 40px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
    background: #fafafa;
    background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
    background:         linear-gradient(top, #fafafa 0%, #eee 100%);
    -webkit-border-radius: 3px;
            border-radius: 3px;
    box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-content:hover { color: #2ea8e5; background: #fff; }
.dd-dragel > .dd-item > .dd-panel > .dd-content { margin: 0; }
.dd-item > button { margin-left: 30px; }

.dd-handle { position: absolute; margin: 0; left: 0; top: 0; cursor: pointer; width: 30px; text-indent: 100%; white-space: nowrap; overflow: hidden;
  border: 1px solid #aaa;
  background: #ddd;
  background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
  background:    -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
  background:         linear-gradient(top, #ddd 0%, #bbb 100%);
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  height: 100%;
}
.dd-handle:before { content: \'≡\'; display: block; position: absolute; left: 0; top: 3px; width: 100%; text-align: center; text-indent: 0; color: #fff; font-size: 20px; font-weight: normal; }
.dd-handle:hover { background: #ddd; }
');
        nestable::$_css_rendered = TRUE;
      }

    }

    parent::pre_render($form);
  }

}


/* #########################################################
   ####                 ACCESSORIES                     ####
   ######################################################### */

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
  private $array = array();

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

    $tmparr = array();
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
    $out = array();
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
    $this->array = array_diff($this->array, array($value));
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



/**
 * the form builder class
 */
class form_builder {

  /**
   * returns the form_id
   * @param  callable $function_name  the function name
   * @return string                   the form_id
   */
  static function get_form_id( $function_name ){
    if( is_string($function_name) ) return $function_name;
    if( is_callable($function_name) && is_array($function_name) ) return $function_name[1];
    return 'cs_form';
  }

  /**
   * returns callable function name string
   * @param  callabe $function_name callable element
   * @return string                 the function name
   */
  static function get_definition_function_name( $function_name ){
    if( is_string($function_name) ) return $function_name;
    if( is_callable($function_name) && is_array($function_name) ){
      if( is_string( $function_name[0]) ) return $function_name[0].'::'.$function_name[1];
      if( is_object( $function_name[0]) ) return get_class($function_name[0]).'::'.$function_name[1];
    }

    return NULL;
  }

  /**
   * returns a form object.
   * this function calls the form definitor function passing an initial empty form object and the form state
   * @param  callable $callable  form_id (and also form definitor function name)
   * @param  array &$form_state  form state by reference
   * @param  array $form_options  additional form constructor options
   * @return form             a new form object
   */
  static function build_form($callable, &$form_state, $form_options = array()){
    $form_id = form_builder::get_form_id($callable);
    $function_name = form_builder::get_definition_function_name( $callable );

    $form = new form(array(
      'form_id' => $form_id,
      'definition_function' => $function_name,
    ) + $form_options);

    $form_state += form_builder::get_request_values($function_name);
    if(is_callable($function_name)){
      $form_obj = call_user_func_array($function_name , array_merge( array($form, &$form_state), $form_state['build_info']['args']) );
      if( ! $form_obj instanceof form ){
        throw new Exception("Error. function {$function_name} does not return a valid form object", 1);
      }

      $form =  $form_obj;
      $form->set_definition_function( $function_name );
      $_SESSION['form_definition'][$form->get_id()] = $form->toArray();
    }
    return $form;
  }

  /**
   * get a new form object
   * @param  string $form_id form_id (and also form definitor function name)
   * @return form         a new form object
   */
  static function get_form($form_id){
    $form_state = array();
    $args = func_get_args();
    // Remove $form_id from the arguments.
    array_shift($args);
    $form_state['build_info']['args'] = $args;

    $form = form_builder::build_form($form_id, $form_state);
    return $form;
  }

  /**
   * returns rendered form's html string
   * @param  string $form_id form_id (and also form definitor function name)
   * @return string          form html
   */
  static function render_form($form_id){
    $form = form_builder::get_form($form_id);
    return $form->render();
  }

  /**
   * prepares the form_state array
   * @param  string $form_id the form_id
   * @return array           the form_state array
   */
  static function get_request_values($form_id){
    $out = array('input_values' => array() , 'input_form_definition'=>NULL);
    foreach(array('_POST' => $_POST,'_GET' => $_GET,'_REQUEST' => $_REQUEST) as $key => $array){
      if(!empty($array['form_id']) && $array['form_id'] == $form_id){
        $out['input_values'] = $array; //array_merge($out, $array);
        $out['input_values']['__values_container'] = $key; //array_merge($out, $array);

        if(isset($array['form_id']) && isset($_SESSION['form_definition'][ $array['form_id'] ]) ){
          $out['input_form_definition'] = $_SESSION['form_definition'][ $array['form_id'] ];
        }

        break;
      }
    }
    return $out;
  }

  static function guessFormType( $value, $element_name = NULL ){
    $default_value = $value;
    $vtype = gettype( $default_value );
    switch( $vtype ){
      case 'object':
        $vtype = get_class( $default_value );
      break;
    }

    $type = NULL;
    $validate = array();
    switch ( strtolower($vtype) ){
      case 'string':
        $type = 'textfield';
      break;
      case 'integer':
        $type = 'spinner';$validate = array('integer');
      break;
      case 'float':
      case 'double':
        $type = 'textfield';$validate = array('numeric');
      break;
      case 'boolean':
      case 'bool':
        $type = 'checkbox';
      break;
      case 'datetime':
        $type = 'datetime';

        $default_value = array(
          'year'    => $default_value->format('Y'),
          'month'   => $default_value->format('m'),
          'day'     => $default_value->format('d'),
          'hours'   => $default_value->format('H'),
          'minutes' => $default_value->format('i'),
          'seconds' => $default_value->format('s'),
        );

      break;
      case 'date':
        $type = 'date';

        $default_value = array(
          'year'    => $default_value->format('Y'),
          'month'   => $default_value->format('m'),
          'day'     => $default_value->format('d'),
          'hours'   => $default_value->format('H'),
          'minutes' => $default_value->format('i'),
          'seconds' => $default_value->format('s'),
        );

      break;
      case 'array':
      case 'object':
        $type = 'textarea';
        $default_value = json_encode($default_value);
      break;
    }

    if( $type == NULL && ( $default_value == NULL || is_scalar($default_value) ) ){
      switch ($element_name) {
        case 'id':
        case 'surname':
        case 'name':
          $type = 'textfield';
          break;
        case 'email':
          $type = 'textfield';
          $validate = array('email');
          break;
        case 'date':
        case 'day':
        case 'birth':
        case 'birthdate':
        case 'birthday':
          $type = 'date';
          break;
        case 'time':
          $type = 'time';
          break;
        default:
          break;
      }
    }

    if( $type == NULL ) $type = 'textfield';
    return array( 'type' => $type, 'validate' => $validate, 'default_value' => $default_value );
  }

  static function objFormDefinition(form $form, &$form_state, $object){
    $form->set_form_id( get_class($object) );
    $fields = get_object_vars($object) + get_class_vars( get_class($object) );

    $fieldset = $form->add_field( get_class($object), array(
      'type' => 'fieldset',
      'title' => get_class($object),
    ));

    foreach( $fields as $k => $v ){
      list($type, $validate, $default_value) = array_values( form_builder::guessFormType($v, $k) );
      $fieldset->add_field( $k, array(
        'type' => $type,
        'title' => $k,
        'validate' => $validate,
        'default_value' => $default_value,
      ) );
    }

    $form
    ->add_field('submit', array(
      'type' => 'submit',
    ));

    return $form;
  }

  /**
   * returns a form object representing the object parameter
   * @param object $object the object to map
   * @return form form object
   */
  static function object_form( $object ){
    $form_state = array();
    $form_state['build_info']['args'] = array($object);

    $form = form_builder::build_form(
      array(__CLASS__, 'objFormDefinition'),
      $form_state,
      array(
        'submit' => array(strtolower(get_class($object).'_submit')),
        'validate' => array(strtolower(get_class($object).'_validate')),
      )
    );
    return $form;
  }
}


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

}
