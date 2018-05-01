<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                      FORM                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

use \Exception;
use Degami\PHPFormsApi\Traits\tools;
use Degami\PHPFormsApi\Traits\processors;
use Degami\PHPFormsApi\Traits\validators;
use Degami\PHPFormsApi\Traits\containers;
use Degami\PHPFormsApi\Abstracts\Base\element;
use Degami\PHPFormsApi\Abstracts\Base\field;
use Degami\PHPFormsApi\Abstracts\Base\fields_container;
use Degami\PHPFormsApi\Accessories\ordered_functions;
use Degami\PHPFormsApi\Accessories\form_values;
use Degami\PHPFormsApi\Fields\datetime;
use Degami\PHPFormsApi\Fields\geolocation;
use Degami\PHPFormsApi\Fields\checkbox;
use Degami\PHPFormsApi\Fields\radios;
use Degami\PHPFormsApi\Fields\select;
use Degami\PHPFormsApi\Abstracts\Fields\field_multivalues;


/**
 * the form object class
 */
class form extends element{

  use tools, validators, processors, containers;

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
  protected $validate = [];

  /**
   * submit functions list
   * @var array
   */
  protected $submit = [];

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
   * ajax submit url
   * @var string
   */
  protected $ajax_submit_url = '';

  /**
   * print form on a dialog
   * @var boolean
   */
  protected $on_dialog = false;

  /**
   * current step
   * @var integer
   */
  private $current_step = 0;

  /**
   * array of submit functions results
   * @var array
   */
  private $submit_functions_results = [];


  /**
   * "do not process form token" flag
   * @var boolean
   */
  private $no_token = FALSE;

  /**
   * class constructor
   * @param array $options build options
   */
  public function __construct($options = []) {
    $this->build_options = $options;

    $this->container_tag = FORMS_DEFAULT_FORM_CONTAINER_TAG;
    $this->container_class = FORMS_DEFAULT_FORM_CONTAINER_CLASS;

    foreach ($options as $name => $value) {
      $name = trim($name);
      if( property_exists(get_class($this), $name) )
        $this->{$name} = $value;
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

    $has_session = form_builder::session_present();
    if ($has_session) {
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
   * set the form on_dialog preference
   * @param string $on_dialog the form on_dialog preference
   * @return form
   */
  public function set_on_dialog($on_dialog){
    $this->on_dialog = $on_dialog;
    return $this;
  }

  /**
   * get the form on_dialog preference
   * @return string the form on_dialog preference
   */
  public function get_on_dialog(){
    return $this->on_dialog;
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
    $output = [];
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
    $output = [];
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
    $this->set_errors( [] );
    $this->valid = NULL;
    $this->current_step = 0;
    $this->submit_functions_results = [];
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
        if($field->is_multiple()) $field->process([]);
        else $field->process(NULL);
      } else if( $field instanceof field_multivalues ){
        // no value on request[name] && field is a multivalue (eg. checkboxes ?) - process anyway with an empty value
        $field->process([]);
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

    $has_session = form_builder::session_present();
    if( $has_session ){
      $_SESSION[$this->form_id]['steps'][$this->current_step] = $request;
    }
  }

  /**
   * starts the form processing, validating and submitting
   * @param  array  $values the request values array
   */
  public function process( $values = [] ) {

    $has_session = form_builder::session_present();
    if( $has_session ){
      foreach($_SESSION['form_token'] as $key => $time){
        if ( $time < ($_SERVER['REQUEST_TIME'] - FORMS_SESSION_TIMEOUT) ) {
          unset($_SESSION['form_token'][$key]);
        }
      }
    }

    // let others alter the form
    $defined_functions = get_defined_functions();
    foreach( $defined_functions['user'] as $function_name){
      if( preg_match("/.*?_{$this->form_id}_form_alter$/i", $function_name) ){
        call_user_func_array( $function_name, [ &$this ] );
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
          if($has_session && isset($_SESSION[$this->form_id]['steps'][$step])){
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

        if($has_session && isset($_SESSION[$this->form_id])){
          unset($_SESSION[$this->form_id]);
        }

        for($step = 0; $step < $this->get_num_steps(); $step++){
          foreach( $this->get_fields($step) as $name => $field ){
            $field->postprocess();
          }
        }

        foreach($this->submit as $submit_function){
          if( is_callable($submit_function) ) {
            if(!is_array($this->submit_functions_results)){
              $this->submit_functions_results = [];
            }
            $submitresult = '';
            ob_start();
            $submitresult = call_user_func_array( $submit_function, [ &$this, $request ] );
            if($submitresult == NULL ){
              $submitresult = ob_get_contents();
            }
            ob_end_clean();
            $this->submit_functions_results[form_builder::get_definition_function_name($submit_function)] = $submitresult;
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
      $has_session = form_builder::session_present();
      if($this->valid == NULL) $this->valid = TRUE;
      if ($has_session && !$this->no_token) {
        $this->valid = FALSE;
        $this->add_error($this->get_text('Form is invalid or has expired'),__FUNCTION__);
        if (isset($_REQUEST['form_token']) && isset($_SESSION['form_token'][$_REQUEST['form_token']])) {
          if ( $_SESSION['form_token'][$_REQUEST['form_token']] >= ($_SERVER['REQUEST_TIME'] - FORMS_SESSION_TIMEOUT) ) {
            $this->valid = TRUE;
            $this->set_errors( [] );
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

    $field = $this->get_field_obj($name, $field);
    $field->set_parent($this);

    $this->fields[$step][$name] = $field;
    $this->insert_field_order[] = $name;

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
    $notfound = [];
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
    if(!is_array($field_types)) $field_types = [$field_types];
    $out = [];
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
    if(!is_array($field_types)) $field_types = [$field_types];
    $out = [];

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
    if(!is_array($field_types)) $field_types = [$field_types];
    $out = [];

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
    $fields = $this->get_fields_by_type(['submit','button','image_button']);
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
   * set form submit functions list
   * @param ordered_functions $submit set the form submit functions list
   * @return form
   */
  public function set_submit($submit){
    if(!($submit instanceof ordered_functions)){
      $submit = new ordered_functions($submit,'submitter');
    }
    $this->submit = $submit;
    return $this;
  }


  /**
   * get the form validate
   * @return ordered_functions form validate function(s)
   */
  public function get_validate(){
    return $this->validate;
  }

  /**
   * set form validate functions list
   * @param ordered_functions $validate set the form validate functions list
   * @return form
   */
  public function set_validate($validate){
    if(!($validate instanceof ordered_functions)){
      $validate = new ordered_functions($validate,'validator');
    }
    $this->validate = $validate;
    return $this;
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
    if( $this->on_dialog == TRUE ){
      $this->add_js('$("#'.$this->get_form_id().'").dialog()');
    }

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
    $weights = $order = [];
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

    $attributes = $this->get_attributes(['action','method','id']);
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

        return json_encode([ 'html' => $html, 'js' => $js ]);
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
          $output = ['html'=>'','js'=>'','is_submitted'=>$this->is_submitted()];

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
