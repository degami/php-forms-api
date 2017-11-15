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
   * keeps fields insert order
   * @var array
   */
  protected $insert_field_order = [];

  /**
   * form fields
   * @var array
   */
  protected $fields = [];

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
    if (is_array($field)) {
      $field_type = __NAMESPACE__ . "\\" . ( isset($field['type']) ? "{$field['type']}" : 'textfield' );
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
   * "is_date" validation function
   * @param  mixed $value   the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_is_date($value) {
    if ( !$value || ($value && ($date = date_create($value)) === false) ) {
      return "<em>%t</em> is not a valid date.";
    }
    return TRUE;
  }

  /**
   * format byte size
   * @param  integer $size size in bytes
   * @return string       formatted size
   */
  private static function format_bytes($size) {
    $units = [' B', ' KB', ' MB', ' GB', ' TB'];
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
      )%x', [__CLASS__, '_filter_xss_split'], $string);
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
    $attrarr = [];
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
      $allowed_protocols = array_flip( ['ftp', 'http', 'https', 'irc', 'mailto', 'news', 'nntp', 'rtsp', 'sftp', 'ssh', 'tel', 'telnet', 'webcal'] );
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
    $return = [];
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
    $return = [];
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
