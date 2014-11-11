<?php

/*
 *  Turn on error reporting during development
 */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

/*
 *  PHP Forms API library configuration
 */

define('FORMS_DEFAULT_FORM_CONTAINER_TAG', 'div');
define('FORMS_DEFAULT_FORM_CONTAINER_CLASS', 'form-container');
define('FORMS_DEFAULT_FIELD_CONTAINER_TAG', 'div');
define('FORMS_DEFAULT_FIELD_CONTAINER_CLASS', 'form-item');
define('FORMS_VALIDATE_EMAIL_DNS', TRUE);
define('FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS', 'mailinator.com|guerrillamail.com');
define('FORMS_BASE_PATH', '');
define('FORMS_XSS_ALLOWED_TAGS', 'a|em|strong|cite|code|ul|ol|li|dl|dt|dd');

// Here are some prioity things I'm working on:
// TODO: Support edit forms by allowing an array of values to be specified, not just taken from _REQUEST

abstract class cs_element{
  protected $container_tag = FORMS_DEFAULT_FIELD_CONTAINER_TAG;
  protected $container_class = FORMS_DEFAULT_FIELD_CONTAINER_CLASS;
  protected $errors = array();
  protected $attributes = array();
  protected $prefix = '';
  protected $suffix = '';

  public function add_error($error_string,$validate_function_name){
    $this->errors[$validate_function_name] = $error_string;
  }

  public function get_errors(){
    return $this->errors;
  }

  public function set_attribute($name,$value){
    $this->attributes[$name] = $value;

    return $this;
  }

  public function get_attributes($reserved_arr = array('type','name','id','value')){
    return $this->get_attributes_string($this->attributes, $reserved_arr);
  }

  public function get_attributes_string( $attributes_arr, $reserved_arr = array('type','name','id','value')){
    $attributes = '';
    foreach ($reserved_arr as $key => $reserved) {
      if(isset($attributes_arr[$reserved])) unset($attributes_arr[$reserved]);
    }
    foreach ($attributes_arr as $key => $value) {
      if(!is_string($value)) continue;
      $value = cs_form::process_plain($value);
      if(!empty($value)){
        $value=trim($value);
        $attributes .= " {$key}=\"{$value}\"";
      }
    }
    $attributes = trim($attributes);
    return empty($attributes) ? '' : ' ' . $attributes;
  }

  public function get_prefix(){
    if(!empty($this->container_tag)){

      if(preg_match("/<\/?(.*?)\s.*?(class=\"(.*?)\")?.*?>/i",$this->container_tag,$matches)){
        // if a <tag> is contained try to get tag and class
        $this->container_tag = $matches[1];
        $this->container_class = (!empty($this->container_class) ? $this->container_class : '') . (!empty($matches[3]) ? ' '.$matches[3] : '');
      }

      $class = $this->container_class;
      if(isset($this->attributes['class']) && !empty($this->attributes['class'])){
        $class .= ' '.$this->attributes['class'].'-container';
      }
      if (!empty($this->get_errors())) {
        $class .= ' error';
      }
      $class = trim($class);
      return "<{$this->container_tag} class=\"{$class}\">";
    }
    return '';
  }

  public function get_suffix(){
    if(!empty($this->container_tag)){
      return "</{$this->container_tag}>";
    }
    return '';
  }
}

/* #########################################################
   ####                      FORM                       ####
   ######################################################### */


class cs_form extends cs_element{

  protected $form_id = 'cs_form';
  protected $form_token = '';
  protected $action = '';
  protected $method = 'post';
  protected $processed = FALSE;
  protected $validated = FALSE;
  protected $submitted = FALSE;
  protected $valid = NULL;
  protected $validate = array();
  protected $submit = array();

  protected $inline_errors = FALSE;
  protected $js = array();
  protected $js_generated = FALSE;

  protected $insert_field_order = array();
  protected $fields = array();

  public function __construct($options = array()) {

    $this->container_tag = FORMS_DEFAULT_FORM_CONTAINER_TAG;
    $this->container_class = FORMS_DEFAULT_FORM_CONTAINER_CLASS;

    foreach ($options as $name => $value) {
      if( property_exists(get_class($this), $name) )
        $this->$name = $value;
    }
    if (empty($this->submit) || !is_callable($this->submit)) {
      array_push($this->submit, "{$this->form_id}_submit");
    }
    if (empty($this->validate) || !is_callable($this->validate)) {
      array_push($this->validate, "{$this->form_id}_validate");
    }

    if(!$this->validate instanceof cs_ordered_functions){
      $this->validate = new cs_ordered_functions($this->validate,'validator');
    }

    if(!$this->submit instanceof cs_ordered_functions){
      $this->submit = new cs_ordered_functions($this->submit,'submitter');
    }

    $sid = session_id();
    if (!empty($sid)) {
      $this->form_token = sha1(mt_rand(0, 1000000));
      $_SESSION['form_token'][$this->form_token] = $_SERVER['REQUEST_TIME'];
    }
  }

  // Warning: some messy logic in calling process->submit->values
  public function values() {
    if (!$this->processed) {
      $this->process();
    }
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
    unset($_REQUEST['form_id']);
    $this->processed = FALSE;
    $this->validated = FALSE;
    $this->submitted = FALSE;
    $this->js_generated = FALSE;
    $this->errors = array();
    $this->valid = NULL;
  }

  public function is_submitted() {
    return $this->submitted;
  }

  public function process() {

    // let others alter the form
    $defined_functions = get_defined_functions();
    foreach( $defined_functions['user'] as $function_name){
      if( preg_match("/.*?_{$this->form_id}_form_alter$/i", $function_name) ){
        $function_name($this);
      }
    }

    if (!$this->processed) {
      $request = (strtolower($this->method) == 'post') ? $_POST : $_GET;

      foreach($request as $key => $val){
        if(preg_match('/^(.*?)_(x|y)$/',$key,$matches) && !empty($this->get_fields_by_type_and_name('image_button',$matches[1])) ){
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

      if (isset($request['form_id']) && $request['form_id'] == $this->form_id) {
        foreach ($this->get_fields() as $name => $field) {
          if( $field instanceof cs_fields_container ) $this->get_field($name)->process($request);
          else if ( isset($request[$name]) ) {
            $this->get_field($name)->process($request[$name], $name);
          }
        }
        $this->processed = TRUE;
      }
    }
    if($this->processed == TRUE){
      foreach ($this->get_fields() as $name => $field) {
        $field->preprocess();
      }
      if ((!$this->submitted) && $this->valid()) {
        $this->submitted = TRUE;
        foreach ($this->get_fields() as $name => $field) {
          $field->postprocess();
        }

        foreach($this->submit as $submit_function){
          if (function_exists($submit_function)) {
            $submit_function($this, (strtolower($this->method) == 'post') ? $_POST : $_GET);
          }
        }
      }
    }
  }

  public function valid() {
    if ($this->validated) {
      return $this->valid;
    }
    if (!isset($_REQUEST['form_id'])) {
      return NULL;
    } else if ($_REQUEST['form_id'] == $this->form_id) {
      $sid = session_id();
      if (!empty($sid)) {
        $this->valid = FALSE;
        $this->add_error('Form is invalid or has expired',__FUNCTION__);
        if (isset($_REQUEST['form_token']) && isset($_SESSION['form_token'][$_REQUEST['form_token']])) {
          if ($_SESSION['form_token'][$_REQUEST['form_token']] >= $_SERVER['REQUEST_TIME'] - 7200) {
            $this->valid = TRUE;
            $this->errors = array();
            unset($_SESSION['form_token'][$_REQUEST['form_token']]);
          }
        }
      }
      foreach ($this->get_fields() as $field) {
        if (!$field->valid()) {
          $this->valid = FALSE;
        }
      }

      foreach($this->validate as $validate_function){
        if (function_exists($validate_function)) {
          if ( ($error = $validate_function($this, (strtolower($this->method) == 'post') ? $_POST : $_GET)) !== TRUE ){
            $this->valid = FALSE;
            $this->add_error( is_string($error) ? $error : 'Error. Form is not valid', $validate_function );
          }
        }
      }
      $this->validated = TRUE;
      return $this->valid;
    }
    return NULL;
  }

  public function add_field($name, $field) {
    if (is_array($field)) {
      $field_type = isset($field['type']) ? "cs_{$field['type']}" : 'cs_textfield';
      if(!class_exists($field_type)){
        throw new Exception("Error adding field. Class $field_type not found", 1);
      }
      $field = new $field_type($field, $name);
    }else if($field instanceof cs_field){
      $field->set_name($name);
    }else{
      throw new Exception("Error adding field. Array or cs_field subclass expected, ".gettype($field)." given", 1);
    }

    $this->fields[$name] = $field;
    $this->insert_field_order[] = $name;

    if($field instanceof cs_fields_container)
      return $field;

    return $this;
  }

  public function &get_fields(){
    return $this->fields;
  }

  public function get_fields_by_type($field_types){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof cs_fields_container){
        $out = array_merge($out,$field->get_fields_by_type($field_types));
      }else{
        if($field instanceof cs_field && in_array($field->get_type(), $field_types)) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  public function get_fields_by_type_and_name($field_types,$name){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof cs_fields_container){
        $out = array_merge($out, $field->get_fields_by_type_and_name($field_types,$name));
      }else{
        if($field instanceof cs_field && in_array($field->get_type(), $field_types) && $field->get_name() == $name) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  public function get_field($field_name){
    return isset($this->fields[$field_name]) ? $this->fields[$field_name] : NULL;
  }

  public function get_triggering_element(){
    $fields = $this->get_fields_by_type(array('submit','button','image_button'));
    foreach($fields as $field){
      if($field->get_clicked() == TRUE) return $field;
    }
    return NULL;
  }

  public function get_submit(){
    return $this->submit;
  }

  public function get_validate(){
    return $this->validate;
  }

  public function get_id(){
    return $this->form_id;
  }

  public function show_errors() {
    return empty($this->get_errors()) ? '' : "<li>".implode('</li><li>',$this->get_errors())."</li>";
  }

  public function errors_inline() {
    return $this->inline_errors;
  }

  public function pre_render(){
    foreach ($this->get_fields() as $name => $field) {
      if( is_object($field) && method_exists ( $field , 'pre_render' ) ){
        $field->pre_render($this);
      }
    }
  }

  public function render() {
    $output = $this->get_prefix();
    $output .= $this->prefix;

    if ( $this->valid() === FALSE) {
      $errors = $this->show_errors();
      if(!$this->errors_inline()){
        foreach ($this->get_fields() as $field) {
          $errors .= $field->show_errors();
        }
      }
      if(trim($errors)!=''){
        $output .= "<div class=\"errors ui-state-error ui-corner-all\"><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><ul>";
        $output .= $errors;
        $output .= "</ul></div>";
      }
    }

    $insertorder = array_flip($this->insert_field_order);
    $weights = array();
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());

    $fields_html = '';
    foreach ($this->get_fields() as $name => $field) {
      if( is_object($field) && method_exists ( $field , 'render' ) ){
        $fields_html .= $field->render($this);
      }
    }

    $attributes = $this->get_attributes(array('action','method','id'));

    $output .= "<form action=\"{$this->action}\" id=\"{$this->form_id}\" method=\"{$this->method}\"{$attributes}>\n";
    $output .= $fields_html;
    $output .= "<input type=\"hidden\" name=\"form_id\" value=\"{$this->form_id}\" />\n";
    $output .= "<input type=\"hidden\" name=\"form_token\" value=\"{$this->form_token}\" />\n";
    $output .= "</form>\n";

    $js = $this->generate_js();
    if(!empty( $js )){
      $output .= "\n<script type=\"text/javascript\">\n".$js."\n</script>\n";
    }

    $output .= $this->suffix;
    $output .= $this->get_suffix();
    return $output;
  }

  public function add_js($js){
    $this->js[] = $js;

    return $this;
  }

  public function &get_js($js){
    return $this->js;
  }

  public function generate_js(){
    $this->js = array_filter(array_map('trim',$this->js));
    if(!empty( $this->js ) && !$this->js_generated ){
      foreach($this->js as &$js_string){
        if($js_string[strlen($js_string)-1] == ';'){
          $js_string = substr($js_string,0,strlen($js_string)-1);
        }
      }

      $this->js_generated = TRUE;
      return "(function($){\n".
        "\t$(document).ready(function(){\n".
        "\t\t".implode(";\n\t\t",$this->js).";\n".
        "\t});\n".
      "})(jQuery);";
    }
    return "";
  }

  public static function validate_required($value = NULL) {
    if (!empty($value)) {
      return TRUE;
    } else {
      return "<em>%t</em> is required";
    }
  }

  public static function validate_max_length($value, $options) {
    // if(!is_string($value)) throw new Exception("Invalid value - max_length is meant for strings, ".gettype($value)." given");
    if (strlen($value) > $options) {
      return "Maximum length of <em>%t</em> is {$options}";
    }
    return TRUE;
  }

  public static function validate_min_length($value, $options) {
    // if(!is_string($value)) throw new Exception("Invalid value - min_length is meant for strings, ".gettype($value)." given");
    if (strlen($value) < $options) {
      return "<em>%t</em> must be longer than {$options}";
    }
    return TRUE;
  }

  public static function validate_exact_length($value, $options) {
    // if(!is_string($value)) throw new Exception("Invalid value - exact_length is meant for strings, ".gettype($value)." given");
    if (strlen($value) != $options) {
      return "<em>%t</em> must be {$options} characters long.";
    }
    return TRUE;
  }

  public static function validate_alpha($value) {
    // if(!is_string($value)) throw new Exception("Invalid value - alpha is meant for strings, ".gettype($value)." given");
    if (!preg_match( "/^([a-z])+$/i", $value)) {
      return "<em>%t</em> must contain alphabetic characters.";
    }
    return TRUE;
  }

  protected function validate_alpha_numeric($value) {
    // if(!is_string($value) && !is_numeric($value)) throw new Exception("Invalid value - alpha_numeric is meant for strings or numeric values, ".gettype($value)." given");
    if (!preg_match("/^([a-z0-9])+$/i", $value)) {
      return "<em>%t</em> must only contain alpha numeric characters.";
    }
    return TRUE;
  }

  protected function validate_alpha_dash($value) {
    // if(!is_string($value)) throw new Exception("Invalid value - alpha_dash is meant for strings, ".gettype($value)." given");
    if (!preg_match("/^([-a-z0-9_-])+$/i", $value)) {
      return "<em>%t</em> must contain only alpha numeric characters, underscore, or dashes";
    }
    return TRUE;
  }

  protected function validate_numeric($value) {
    if (!is_numeric($value)) {
      return "<em>%t</em> must be numeric.";
    }
    return TRUE;
  }

  protected function validate_integer($value) {
    if (!preg_match( '/^[\-+]?[0-9]+$/', $value)) {
      return "<em>%t</em> must be an integer.";
    }
    return TRUE;
  }

  public static function validate_match($value, $options) {
    $other = cs_form::scan_array($options, $_REQUEST);
    if ($value != $other) {
      return "The field <em>%t</em> is invalid.";
    }
    return TRUE;
  }

  public static function validate_file_extension($value, $options) {
    if(!isset($value['filepath'])) return "<em>%t</em> - Error. value has no filepath attribute";
    $options = explode(',', $options);
    $ext = substr(strrchr($value['filepath'], '.'), 1);
    if (!in_array($ext, $options)) {
      return "File upload <em>%t</em> is not of required type";
    }
    return TRUE;
  }

  public static function validate_file_not_exists($value) {
    if(!isset($value['filepath'])) return "<em>%t</em> - Error. value has no filepath attribute";
    if (file_exists($value['filepath'])) {
      return "The file <em>%t</em> has already been uploaded";
    }
    return TRUE;
  }

  public static function validate_max_file_size($value, $options) {
    if(!isset($value['filesize'])) return "<em>%t</em> - Error. value has no filesize attribute";
    if ($value['filesize'] > $options) {
      $max_size = cs_form::format_bytes($options);
      return "The file <em>%t</em> is too big. Maximum filesize is {$max_size}.";
    }
    return TRUE;
  }

  private static function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
  }

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

  public static function process_trim($text) {
    return trim($text);
  }
  public static function process_ltrim($text) {
    return ltrim($text);
  }
  public static function process_rtrim($text) {
    return rtrim($text);
  }

  private static function _validate_utf8($text) {
    if (strlen($text) == 0) {
      return TRUE;
    }
    return (preg_match('/^./us', $text) == 1);
  }

  public static function process_xss_weak($string) {
    return filter_xss($string, array('a|abbr|acronym|address|b|bdo|big|blockquote|br|caption|cite|code|col|colgroup|dd|del|dfn|div|dl|dt|em|h1|h2|h3|h4|h5|h6|hr|i|img|ins|kbd|li|ol|p|pre|q|samp|small|span|strong|sub|sup|table|tbody|td|tfoot|th|thead|tr|tt|ul|var'));
  }

  public static function process_xss($string, $allowed_tags = FORMS_XSS_ALLOWED_TAGS) {
    // Only operate on valid UTF-8 strings. This is necessary to prevent cross
    // site scripting issues on Internet Explorer 6.
    if (!cs_form::_validate_utf8($string)) {
      return '';
    }
    // Store the input format
    cs_form::_filter_xss_split($allowed_tags, TRUE);
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
      )%x', 'cs_form::_filter_xss_split', $string);
  }

  private static function _filter_xss_split($m, $store = FALSE) {
    static $allowed_html;

    if ($store) {
      $m = explode("|", $m);
      $allowed_html = array_flip($m);
      return;
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
    $attr2 = implode(' ', cs_form::_filter_xss_attributes($attrlist));
    $attr2 = preg_replace('/[<>]/', '', $attr2);
    $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

    return "<$elem$attr2$xhtml_slash>";
  }

  private static function _filter_xss_attributes($attr) {
    $attrarr = array();
    $mode = 0;
    $attrname = '';

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
            $thisval = cs_form::_filter_xss_bad_protocol($match[1]);

            if (!$skip) {
              $attrarr[] = "$attrname=\"$thisval\"";
            }
            $working = 1;
            $mode = 0;
            $attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
            break;
          }

          if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match)) {
            $thisval = cs_form::_filter_xss_bad_protocol($match[1]);

            if (!$skip) {
              $attrarr[] = "$attrname='$thisval'";
            }
            $working = 1;
            $mode = 0;
            $attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
            break;
          }

          if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match)) {
            $thisval = cs_form::_filter_xss_bad_protocol($match[1]);

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

  private static function _filter_xss_bad_protocol($string, $decode = TRUE) {
    if ($decode) {
      $string = process_entity_decode($string);
    }
    return process_plain(cs_form::_strip_dangerous_protocols($string));
  }

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

  public static function process_plain($text) {
      // if using PHP < 5.2.5 add extra check of strings for valid UTF-8
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  public static function process_entity_decode($text) {
    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
  }

  public static function process_addslashes($text) {
    if(!get_magic_quotes_gpc() && !preg_match("/\\/i",$text))
      return addslashes($text);
    else return $text;
  }

  private static function scan_array($string, $array) {
    list($key, $rest) = preg_split('/[[\]]/', $string, 2, PREG_SPLIT_NO_EMPTY);
    if ( $key && $rest ) {
      return @cs_form::scan_array($rest, $array[$key]);
    } elseif ( $key ) {
      return $array[$key];
    } else {
      return FALSE;
    }
  }

  public static function array_flatten($array) {
    $return = array();
    foreach ($array as $key => $value) {
      if (is_array($value)){
        $return = array_merge($return, cs_form::array_flatten($value));
      } else {
        $return[$key] = $value;
      }
    }
    return $return;
  }

  public static function array_get_values($search_key, $array) {
    $return = array();
    foreach ($array as $key => $value) {
      if (is_array($value)){
        $return = array_merge($return, cs_form::array_get_values($search_key, $value));
      }else if($key == $search_key){
        $return[] = $value;
      }
    }
    return $return;
  }

  public static function order_by_weight($a, $b){
    if ($a->get_weight() == $b->get_weight()) {
      return 0;
    }
    return ($a->get_weight() < $b->get_weight()) ? -1 : 1;
  }

  public static function order_validators($a,$b){
    if(is_array($a) && isset($a['validator'])) $a = $a['validator'];
    if(is_array($b) && isset($b['validator'])) $b = $b['validator'];

    if($a == $b) return 0;
    if($a == 'required') return -1;
    if($b == 'required') return 1;

    return 0;
//    return $a > $b ? 1 : -1;
  }
}


/* #########################################################
   ####                  FIELD BASE                     ####
   ######################################################### */


abstract class cs_field extends cs_element{

  protected $ajax = FALSE;
  protected $validate = array();
  protected $preprocess = array();
  protected $postprocess = array();
  protected $size = 20;
  protected $weight = 0;
  protected $type = '';
  protected $stop_on_first_error = FALSE;
  protected $tooltip = FALSE;
  protected $name = NULL;
  protected $id = NULL;
  protected $title = NULL;
  protected $description = NULL;
  protected $disabled = FALSE;
  protected $default_value = NULL;
  protected $value = NULL;
  protected $pre_rendered = FALSE;

  public function __construct($options = array(), $name = NULL) {
    $this->name = $name;
    foreach ($options as $name => $value) {
      if( property_exists(get_class($this), $name) )
        $this->$name = $value;
    }

    if(!isset($this->attributes['class'])){
      $this->attributes['class'] = preg_replace("/^cs_/","",get_class($this));
    }

    if(empty($this->type)){
      $this->type = preg_replace("/^cs_/","",get_class($this));
    }

    if(!$this->validate instanceof cs_ordered_functions){
      $this->validate = new cs_ordered_functions($this->validate,'validator','cs_form::order_validators');
    }

    if(!$this->preprocess instanceof cs_ordered_functions){
      $this->preprocess = new cs_ordered_functions($this->preprocess, 'preprocessor');
    }

    if(!$this->postprocess instanceof cs_ordered_functions){
      $this->postprocess = new cs_ordered_functions($this->postprocess, 'postprocessor');
    }

    $this->value = $this->default_value;
  }

  public function values() {
    return $this->value;
  }

  public function reset() {
    $this->value = $this->default_value;
    $this->pre_rendered = FALSE;
    $this->errors = array();
  }

  public function get_type(){
    return $this->type;
  }

  public function get_validate(){
    return $this->validate;
  }

  public function get_preprocess(){
    return $this->preprocess;
  }

  public function get_postprocess(){
    return $this->postprocess;
  }

  public function set_name($name){
    $this->name = $name;

    return $this;
  }
  public function get_name(){
    return $this->name;
  }
  public function get_id(){
    return $this->id;
  }

  public function get_html_id(){
    return !empty($this->id) ? $this->get_id() : $this->get_name();
  }

  public function get_weight() {
    return $this->weight;
  }

  public function process($value) {
    $this->value = $value;
  }

  public function preprocess($process_type = "preprocess") {
    foreach ($this->$process_type as $processor) {
      $processor_func = "process_{$processor}";
      if (function_exists($processor_func)) {
        $this->value = $processor_func($this->value);
      } else if(method_exists(get_class($this), $processor_func)){
          $this->value = call_user_func( array($this, $processor_func), $this->value );
      } else {
        if(method_exists('cs_form', $processor_func)){
          //$this->value = cs_form::$processor_func($this->value);
          $this->value = call_user_func( array('cs_form',$processor_func), $this->value );
        }
      }
    }
  }

  public function postprocess() {
    $this->preprocess("postprocess");
  }

  public function valid() {
    $this->errors = array();

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
        // $classmethod = get_class($this)."::".$validator_func;
        // $error = call_user_func( $classmethod, $this->value, $options );
        $error = call_user_func( array(get_class($this), $validator_func), $this->value, $options );
      }else {
        if(method_exists('cs_form', $validator_func)){
          //$error = cs_form::$validator_func($this->value, $options);
          $error = call_user_func( array('cs_form', $validator_func), $this->value, $options );
        }
      }
      if (isset($error) && $error !== TRUE) {
        $titlestr = (!empty($this->title)) ? $this->title : (!empty($this->name) ? $this->name : $this->id);
        if(empty($error)) $error = '%t - Error.';
        $this->add_error(str_replace('%t', $titlestr, $error), $validator_func);
        if(is_array($validator) && !empty($validator['error_message'])){
          $this->add_error(str_replace('%t', $titlestr, $validator['error_message']),$validator_func);
        }

        if($this->stop_on_first_error){
          return FALSE;
        }
      }
    }

    if( !empty($this->get_errors()) ){
      return FALSE;
    }

    return TRUE;
  }

  public function show_errors() {
    return empty($this->get_errors()) ? '' : "<li>".implode("</li><li>",$this->get_errors())."</li>";
  }

  public function pre_render(cs_form $form){
    $this->pre_rendered = TRUE;
    // should not return value, just change element/form state
    return;
  }

  public function render(cs_form $form) {

    $id = $this->get_html_id();
    $output = $this->get_prefix();
    $output.=$this->prefix;

    if( !($this instanceof cs_fields_container)){
      // $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
      $required = ($this->validate->has_value('required')) ? ' <span class="required">*</span>' : '';
      if(!empty($this->title)){
        if ( $this->tooltip == FALSE ) {
          $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
        } else {
          if( !in_array('title', array_keys($this->attributes)) ){
            $this->attributes['title'] = strip_tags($this->title.$required);
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

    if( !($this instanceof cs_fields_container)){
      if (!empty($this->description)) {
        $output .= "<div class=\"description\">{$this->description}</div>";
      }
    }

    if($form->errors_inline() == TRUE && !empty($this->get_errors()) ){
      $output.= '<div class="inline-error error">'.implode("<br />",$this->get_errors()).'</div>';
    }

    $output .= $this->suffix;
    $output .= $this->get_suffix();

    return $output ;
  }

  abstract public function render_field(cs_form $form); // renders html
  abstract public function is_a_value();                // tells if component value is passed on the parent values() function call
}


/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */
abstract class cs_action extends cs_field{
  protected $js_button = FALSE;

  public function pre_render(cs_form $form){
    if($this->js_button == TRUE){
      $id = $this->get_html_id();

      $form->add_js("\$('#{$id}','#{$form->get_id()}').button();");
    }
    parent::pre_render($form);
  }

  public function is_a_value(){
    return FALSE;
  }

  public function valid() {
    return TRUE;
  }

}

abstract class cs_clickable extends cs_action{
  protected $clicked = FALSE;

  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
    $this->clicked = FALSE;
  }

  public function get_clicked(){
    return $this->clicked;
  }

  public function process($value){
    parent::process($value);
    $this->clicked = TRUE;
  }

  public function reset(){
    $this->clicked = FALSE;
    parent::reset();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_submit extends cs_clickable {
  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Submit';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"submit\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    return $output;
  }

}


class cs_button extends cs_clickable {
  protected $label;

  public function __construct($options = array(), $name = NULL){
    parent::__construct($options,$name);
    if(empty($this->label)) $this->label = $this->value;
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<button id=\"{$id}\" name=\"{$this->name}\"{$attributes} value=\"{$this->value}\">{$this->label}</button>\n";
    return $output;
  }

}

class cs_image_button extends cs_clickable {
  protected $src;
  protected $alt;

  public function __construct($options = array(), $name = NULL) {
    $this->default_value = array(
      'x'=>-1,
      'y'=>-1,
    );

    parent::__construct($options, $name);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','value','src','alt'));
    //  value=\"{$this->value}\"
    $output = "<input id=\"{$id}\" name=\"{$this->name}\" type=\"image\" src=\"{$this->src}\" alt=\"{$this->alt}\"{$attributes} />\n";
    return $output;
  }

}

class cs_reset extends cs_action {

  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Reset';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"reset\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    return $output;
  }

}

class cs_value extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    $this->container_tag = '';
    $this->container_class = '';
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  public function render_field(cs_form $form) {
    return '';
  }

  public function valid() {
    return TRUE;
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_markup extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  public function render_field(cs_form $form) {
    $output = $this->value;
    return $output;
  }

  public function valid() {
    return TRUE;
  }

  public function is_a_value(){
    return FALSE;
  }
}

class cs_hidden extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    $this->container_tag = '';
    $this->container_class = '';
    parent::__construct($options,$name);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();
    return "<input type=\"hidden\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_textfield extends cs_field {
  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"{$this->value}\"{$attributes} />\n";
    return $output;
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_autocomplete extends cs_textfield{
  protected $autocomplete_path = FALSE;
  protected $options = array();
  protected $min_length = 3;

  public function __construct($options, $name = NULL){
    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' autocomplete';

    parent::__construct($options, $name);
  }

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();

    $form->add_js("
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

class cs_maskedfield extends cs_textfield{
  protected $mask;

  /* jQuery Mask Plugin patterns */
  private $translation = array(
    '0'  =>  "\d",
    '9'  =>  "\d?",
    '#'  =>  "\d+",
    'A'  =>  "[a-zA-Z0-9]",
    'S'  =>  "[a-zA-Z]",
  );

  public function __construct($options, $name = NULL){
    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' maskedfield';

    parent::__construct($options, $name);
  }

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    $form->add_js("\$('#{$id}','#{$form->get_id()}').mask('{$this->mask}');");
    parent::pre_render($form);
  }

  public function valid() {
    $mask = $this->mask;
    $mask = preg_replace("(\[|\]|\(|\))","\\\1",$mask);
    foreach($this->translation as $search => $replace){
      $mask = str_replace($search, $replace, $mask);
    }
    $mask = '/^'.$mask.'$/';
    if(!preg_match($mask,$this->value)){
      $this->add_error("Value does not conform to mask",__FUNCTION__);

      if($this->stop_on_first_error)
        return FALSE;
    }

    return parent::valid();
  }
}

class cs_textarea extends cs_field {
  protected $rows = 5;
  protected $resizable = FALSE;

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    if($this->resizable == TRUE){
      $form->add_js("\$('#{$id}','#{$form->get_id()}').resizable({handles:\"se\"});");
    }
    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('name','id','value','rows','cols'));
    $output = "<textarea id=\"{$id}\" name=\"{$this->name}\" cols=\"{$this->size}\" rows=\"{$this->rows}\"{$attributes}>\n".$this->value."</textarea>";
    return $output;
  }

  public function is_a_value(){
    return TRUE;
  }
}


class cs_password extends cs_field {
  protected $with_confirm = FALSE;
  protected $confirm_string = "Confirm password";

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = "<input type=\"password\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"\"{$attributes} />\n";
    if($this->with_confirm == TRUE){
      $output .= "<label for=\"{$id}-confirm\">{$this->confirm_string}</label>";
      $output .= "<input type=\"password\" id=\"{$id}-confirm\" name=\"{$this->name}_confirm\" size=\"{$this->size}\" value=\"\"{$attributes} />\n";
    }
    return $output;
  }

  public function valid(){
    if($this->with_confirm == TRUE){
      if(!isset($_REQUEST["{$this->name}_confirm"]) || $_REQUEST["{$this->name}_confirm"] != $this->value ) {
        $this->add_error("The passwords do not match",__FUNCTION__);

        if($this->stop_on_first_error)
          return FALSE;
      }
    }
    return parent::valid();
  }

  public function is_a_value(){
    return TRUE;
  }
}

abstract class cs_field_multivalues extends cs_field {
  protected $options = array();

  public function &get_options(){
    return $this->options;
  }


  // private static function has_key($needle, $haystack) {
  //   foreach ($haystack as $key => $value) {
  //     if ($needle == $key) {
  //       return TRUE;
  //     } else if(is_array($value)) {
  //       if( cs_field_multivalues::has_key($needle, $value) == TRUE ){
  //         return TRUE;
  //       }
  //     }
  //   }
  //   return FALSE;
  // }

  public static function has_key($needle, $haystack) {
    foreach ($haystack as $key => $value) {
      if($value instanceof cs_option){
        if($value->get_key() == $needle) return TRUE;
      }else if($value instanceof cs_optgroup){
        if($value->options_has_key($needle) == TRUE) return TRUE;
      }else if ($needle == $key) {
        return TRUE;
      } else if(is_array($value)) {
        if( cs_field_multivalues::has_key($needle, $value) == TRUE ){
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  public function options_has_key($needle){
    return cs_field_multivalues::has_key($needle,$this->options);
  }

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
        $this->add_error("{$titlestr}: Invalid choice",__FUNCTION__);

        if($this->stop_on_first_error)
          return FALSE;
      }
    }
    return parent::valid();
  }

  public function is_a_value(){
    return TRUE;
  }
}


class cs_option extends cs_element{
  protected $label;
  protected $key;

  function __construct($key, $label, $options = array()) {
    $this->key = $key;
    $this->label = $label;

    foreach ($options as $key => $value) {
      if( property_exists(get_class($this), $key) )
        $this->$key = $value;
    }
  }

  public function render(cs_select $form_field){
    $selected = ($this->key == $form_field->get_value()) ? ' selected="selected"' : '';
    $attributes = $this->get_attributes(array('value','selected'));
    $output = "<option value=\"{$this->key}\"{$selected}{$attributes}>{$this->label}</option>\n";
    return $output;
  }

  public function get_key(){
    return $this->key;
  }
}

class cs_optgroup extends cs_element{
  protected $options;
  protected $label;

  function __construct($label, $options) {
    $this->label = $label;

    if(isset($options['options'])){
      foreach ($options['options'] as $key => $value) {
        if($value instanceof cs_option) {
          $this->add_option($value);
        } else {
          $this->add_option( new cs_option($key , $value) );
        }
      }
      unset($options['options']);
    }

    foreach ($options as $key => $value) {
      if( property_exists(get_class($this), $key) )
        $this->$key = $value;
    }
  }

  public function options_has_key($needle){
    return cs_field_multivalues::has_key($needle,$this->options);
  }

  public function add_option(cs_option $option){
    $this->options[] = $option;
  }

  public function render(cs_select $form_field){
    $attributes = $this->get_attributes(array('label'));
    $output = "<optgroup label=\"{$this->label}\"{$attributes}>\n";
    foreach ($this->options as $option) {
      $output .= $option->render($form_field);
    }
    $output .= "</optgroup>\n";
    return $output;
  }
}

class cs_select extends cs_field_multivalues {
  protected $multiple = FALSE;

  public function __construct($options,$name) {

    if(isset($options['options'])){
      foreach($options['options'] as $k => $o){
        if( $o instanceof cs_option || $o instanceof cs_optgroup ){
          $this->options[] = $o;
        }else if(is_array($o)){
          $this->options[] = new cs_optgroup( $k , array('options' => $o) );
        }else{
          $this->options[] = new cs_option( $k , $o );
        }
      }
      unset($options['options']);
    }

    parent::__construct($options,$name);
  }

  public function get_value(){
    return $this->value;
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $extra = ($this->multiple) ? ' multiple="multiple" size="'.$this->size.'" ' : '';
    $field_name = ($this->multiple) ? "{$this->name}[]" : $this->name;
    $output .= "<select name=\"{$field_name}\" id=\"{$id}\"{$extra}{$attributes}>\n";
    foreach ($this->options as $key => $value) {
      $output .= $value->render($this);
    }
    $output .= "</select>\n";
    return $output;
  }
}

class cs_selectmenu extends cs_select{
  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    $form->add_js("\$('#{$id}','#{$form->get_id()}').selectmenu({width: 'auto' });");

    parent::pre_render($form);
  }
}

class cs_slider extends cs_select{
  public function __construct($options, $name = NULL){
    // get the "default_value" index value
    $values = cs_form::array_get_values($this->default_value,$this->options);
    $oldkey_value = end($values);

    // flatten the options array ang get a numeric keyset
    // $this->options = cs_form::array_flatten($this->options);
    $options['options'] = cs_form::array_flatten($options['options']);

    // search the new index
    $this->value = $this->default_value = array_search($oldkey_value,$this->options);

    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' slider';

    parent::__construct($options, $name);
  }

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    $form->add_js("
      \$('#{$id}-slider','#{$form->get_id()}').slider({
        min: 1,
        max: ".count($this->options).",
        value: \$( '#{$id}' )[ 0 ].selectedIndex + 1,
        slide: function( event, ui ) {
          \$( '#{$id}' )[ 0 ].selectedIndex = ui.value - 1;
        }
      });
    \$( '#{$id}' ).change(function() {
      \$('#{$id}-slider').slider('value', this.selectedIndex + 1 );
    }).hide();");

    parent::pre_render($form);
  }

  public function render_field(cs_form $form){
    $id = $this->get_html_id();
    $this->suffix = "<div id=\"{$id}-slider\"></div>".$this->suffix;
    return parent::render_field($form);
  }
}

class cs_radios extends cs_field_multivalues {
  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $output = '';
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
      $output .= "<label for=\"{$id}-{$key}\"><input type=\"radio\" id=\"{$id}-{$key}\" name=\"{$this->name}\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
    }
    return $output;
  }

}

class cs_checkboxes extends cs_field_multivalues {
  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    if(!is_array($this->default_value)) {
      $this->default_value = array($this->default_value);
    }

    $output = '';
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';

    foreach ($this->options as $key => $value) {
      $attributes = $this->get_attributes();
      if(is_array($value) && isset($value['attributes'])){
        $attributes = $this->get_attributes_string($value['attributes'],array('type','name','id','value'));
      }
      if(is_array($value)){
        $value = $value['value'];
      }

      $checked = (is_array($this->default_value) && in_array($key, $this->default_value)) ? ' checked="checked"' : '';
      $output .= "<label for=\"{$id}-{$key}\"><input type=\"checkbox\" id=\"{$id}-{$key}\" name=\"{$this->name}".(count($this->options)>1 ? "[]" : "")."\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
    }
    return $output;
  }

}


class cs_checkbox extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options,$name);
    $this->value = NULL;
    if(isset($options['value'])){
      $this->value = $options['value'];
    }
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $checked = ($this->value == $this->default_value) ? ' checked="checked"' : '';
    $output = "<label for=\"{$id}\"><input type=\"checkbox\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->default_value}\"{$checked}{$attributes} /> {$this->title}</label>\n";
    return $output;
  }

  public function is_a_value(){
    return TRUE;
  }
}


class cs_file extends cs_field {
  protected $uploaded = FALSE;
  protected $destination;

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $output = '';

    $form->set_attribute('enctype', 'multipart/form-data');

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','size'));

    $output .= "<input type=\"hidden\" name=\"{$this->name}\" value=\"{$this->name}\" />";
    $output .= "<input type=\"file\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\"{$attributes} />";
    return $output;
  }

  public function process($value, $name) {
    $this->value = array(
      'filepath' => $this->destination .'/'. basename($_FILES[$name]['name']),
      'filename' => basename($_FILES[$name]['name']),
      'filesize' => $_FILES[$name]['size'],
      'mimetype' => $_FILES[$name]['type'],
    );
    if ($this->valid()) {
      if( @move_uploaded_file($_FILES[$name]['tmp_name'], $this->value['filepath']) == TRUE ){
        $this->uploaded = TRUE;
      }
    }
  }

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

  public function valid() {
    if ($this->uploaded) {
      return TRUE;
    }
    return parent::valid();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_date extends cs_field {
  protected $start_year;
  protected $end_year;
  protected $js_selects = FALSE;

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

  public function pre_render(cs_form $form){
    if($this->js_selects == TRUE){
      $id = $this->get_html_id();
      $form->add_js("\$('#{$id} select[name=\"{$this->name}[day]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
      $form->add_js("\$('#{$id} select[name=\"{$this->name}[month]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
      $form->add_js("\$('#{$id} select[name=\"{$this->name}[year]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
    }

    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','size','day','month','year'));

    $output .= "<div id=\"{$id}\"{$attributes}>";

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

  public function process($value, $name) {
    $this->value = array(
      'year' => $value['year'],
      'month' => $value['month'],
      'day' => $value['day'],
    );
  }

  public function valid() {
    if( !checkdate( $this->value['month'] , $this->value['day'] , $this->value['year'] ) ) {
      $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;
      $this->add_error("{$titlestr}: Invalid date", __FUNCTION__);

      if($this->stop_on_first_error)
        return FALSE;
    }
    return parent::valid();
  }

  public function is_a_value(){
    return TRUE;
  }

  public function ts_start(){
    return mktime(0,0,0,$this->value['month'],$this->value['day'],$this->value['year']);
  }
  public function ts_end(){
    return mktime(23,59,59,$this->value['month'],$this->value['day'],$this->value['year']);
  }
}

class cs_datepicker extends cs_field {
  protected $date_format = 'yy-mm-dd';

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    $form->add_js("\$('#{$id}','#{$form->get_id()}').datepicker({dateFormat: '{$this->date_format}'});");

    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $output = "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"{$this->value}\"{$attributes} />\n";

    return $output;
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_time extends cs_field {
  protected $granularity = 'seconds';
  protected $js_selects = FALSE;

  public function __construct($options = array(), $name = NULL) {

    $this->default_value = array(
      'hours'=>0,
      'minutes'=>0,
      'seconds'=>0,
    );

    parent::__construct($options, $name);
  }

  public function pre_render(cs_form $form){
    if($this->js_selects == TRUE){
      $id = $this->get_html_id();

      $form->add_js("\$('#{$id} select[name=\"{$this->name}[hours]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
      if($this->granularity != 'hours'){
        $form->add_js("\$('#{$id} select[name=\"{$this->name}[minutes]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");

        if($this->granularity != 'minutes'){
          $form->add_js("\$('#{$id} select[name=\"{$this->name}[seconds]\"]','#{$form->get_id()}').selectmenu({width: 'auto' });");
        }
      }
    }

    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $output = '';

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
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

  public function process($value, $name) {
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
      $this->add_error("{$titlestr}: Invalid time", __FUNCTION__);

      if($this->stop_on_first_error)
        return FALSE;
    }
    return parent::valid();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_spinner extends cs_field {
  protected $min = NULL;
  protected $max = NULL;
  protected $step = 1;

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();

    $js_options = '';
    if( is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min ){
      $js_options = "{min: $this->min, max: $this->max, step: $this->step}";
    }

    $form->add_js("\$('#{$id}','#{$form->get_id()}').attr('type','text').spinner({$js_options});");

    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $output = '';

    $html_options = '';
    if( is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min ){
      $html_options = " min=\"{$this->min}\" max=\"{$this->max}\" step=\"{$this->step}\"";
    }

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if (!empty($this->get_errors())) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','value','min','max','step'));

    $output .= "<input type=\"number\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\" value=\"{$this->value}\"{$html_options}{$attributes} />\n";

    return $output;
  }

  public function is_a_value(){
    return TRUE;
  }
}


/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */


abstract class cs_fields_container extends cs_field {
  protected $insert_field_order = array();
  protected $fields = array();

  public function &get_fields(){
    return $this->fields;
  }

  public function get_fields_by_type($field_types){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof cs_fields_container){
        $out = array_merge($out, $field->get_fields_by_type($field_types));
      }else{
        if($field instanceof cs_field && in_array($field->get_type(), $field_types)) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  public function get_fields_by_type_and_name($field_types,$name){
    if(!is_array($field_types)) $field_types = array($field_types);
    $out = array();

    foreach($this->get_fields() as $field){
      if($field instanceof cs_fields_container){
        $out = array_merge($out, $field->get_fields_by_type_and_name($field_types,$name));
      }else{
        if($field instanceof cs_field && in_array($field->get_type(), $field_types) && $field->get_name() == $name) {
          $out[] = $field;
        }
      }
    }
    return $out;
  }

  public function get_field($field_name){
    return isset($this->fields[$field_name]) ? $this->fields[$field_name] : NULL;
  }

  public function add_field($name, $field) {
    if (!is_object($field)) {
      $field_type = isset($field['type']) ? "cs_{$field['type']}" : 'cs_textfield';
      if(!class_exists($field_type)){
        throw new Exception("Error adding field. Class $field_type not found", 1);
      }
      $field = new $field_type($field, $name);
    }else{
      $field->set_name($name);
    }
    $this->fields[$name] = $field;
    $this->insert_field_order[] = $name;

    if($field instanceof cs_fields_container)
      return $field;

    return $this;
  }

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

  public function preprocess($process_type = "preprocess") {
    foreach ($this->get_fields() as $field) {
      $field->preprocess($process_type);
    }
  }
  public function process($values) {
    foreach ($this->get_fields() as $name => $field) {
      if( $field instanceof cs_fields_container ) $this->get_field($name)->process($values);
      else if(isset($values[$name])){
        $this->get_field($name)->process($values[$name], $name);
      }
    }
  }

  public function pre_render(cs_form $form){
    foreach ($this->get_fields() as $name => $field) {
      if( is_object($field) && method_exists ( $field , 'pre_render' ) ){
        $field->pre_render($form);
      }
    }
    parent::pre_render($form);
  }

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
  public function show_errors() {
    $output = "";
    foreach ($this->get_fields() as $field) {
      $output .= $field->show_errors();
    }
    return $output;
  }

  public function reset() {
    foreach ($this->get_fields() as $field) {
      $field->reset();
    }
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_tag_container extends cs_fields_container {
  protected $tag = 'div';

  public function __construct($options = array(),$name = NULL){
    // $this->container_tag = NULL;
    // $this->container_class = NULL;

    parent::__construct($options,$name);

    if($this->attributes['class'] == 'tag_container'){ // if set to the default
      $this->attributes['class'] = $this->tag.'_container';
    }
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $insertorder = array_flip($this->insert_field_order);
    $weights = array();
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());
    foreach ($this->get_fields() as $name => $field) {
      $output .= $field->render($form);
    }
    $output .= "</{$this->tag}>\n";

    return $output;
  }
}

class cs_fieldset extends cs_fields_container {
  protected $collapsible = FALSE;
  protected $collapsed = FALSE;

  public function pre_render(cs_form $form){
    static $js_collapsible_added = FALSE;
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
        $form->add_js("
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

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();
    $output = '';

    $attributes = $this->get_attributes();
    $output .= "<fieldset id=\"{$id}\"{$attributes}>\n";
    if (!empty($this->title)) {
      $output .= "<legend>{$this->title}</legend>\n";
    }

    $insertorder = array_flip($this->insert_field_order);
    $weights = array();
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());

    $output .= "<div class=\"fieldset-inner\">\n";
    foreach ($this->get_fields() as $name => $field) {
      $output .= $field->render($form);
    }
    $output .= "</div></fieldset>\n";
    return $output;
  }
}

abstract class cs_fields_container_multiple extends cs_fields_container{
  protected $tabs = array();

  public function &get_tabs(){
    return $this->tabs;
  }

  public function add_tab($title){
    $this->tabs[] = array('title'=>$title,'fieldnames'=>array());

    return $this;
  }

  public function add_field($name, $field, $tabindex = 0) {
    if (!is_object($field)) {
      $field_type = isset($field['type']) ? "cs_{$field['type']}" : 'cs_textfield';
      if(!class_exists($field_type)){
        throw new Exception("Error adding field. Class $field_type not found", 1);
      }
      $field = new $field_type($field, $name);
    }else{
      $field->set_name($name);
    }
    $this->fields[$name] = $field;
    $this->insert_field_order[$tabindex][] = $name;
    $this->tabs[$tabindex]['fieldnames'][] = $name;

    if($field instanceof cs_fields_container){
      return $field;
    }

    return $this;
  }

  public function get_tab_fields($tabindex){
    $out = array();
    $fieldsnames = $this->tabs[$tabindex]['fieldnames'];
    foreach($fieldsnames as $name){
      $out[$name] = $this->get_field($name);
    }
    return $out;
  }

  public function get_tabindex($field_name){
    foreach($this->tabs as $tabindex => $tab){
      if(in_array($field_name, $tab['fieldnames'])) return $tabindex;
    }
    return -1;
  }

}

class cs_tabs extends cs_fields_container_multiple {

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    $form->add_js("\$('#{$id}','#{$form->get_id()}').tabs();");

    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    $tabs_html = array();
    $tab_links = array();
    foreach($this->tabs as $tabindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$tabindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_tab_fields($tabindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_tab_fields($tabindex));

      $tab_links[$tabindex] = "<li><a href=\"#{$id}-tab-inner-{$tabindex}\">".$this->tabs[$tabindex]['title']."</a></li>";
      $tabs_html[$tabindex] = "<div id=\"{$id}-tab-inner-{$tabindex}\" class=\"tab-inner\">\n";
      foreach ($this->get_tab_fields($tabindex) as $name => $field) {
        $tabs_html[$tabindex] .= $field->render($form);
      }
      $tabs_html[$tabindex] .= "</div>\n";
    }
    $output .= "<ul>".implode("",$tab_links)."</ul>".implode("",$tabs_html). "</div>\n";

    return $output;
  }
}

class cs_accordion extends cs_fields_container_multiple {

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    $form->add_js("\$('#{$id}','#{$form->get_id()}').accordion();");

    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    foreach($this->tabs as $tabindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$tabindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_tab_fields($tabindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_tab_fields($tabindex));

      $output .= "<h3>".$this->tabs[$tabindex]['title']."</h3>";
      $output .= "<div id=\"{$id}-tab-inner-{$tabindex}\" class=\"tab-inner\">\n";
      foreach ($this->get_tab_fields($tabindex) as $name => $field) {
        $output .= $field->render($form);
      }
      $output .= "</div>\n";
    }
    $output .= "</div>\n";

    return $output;
  }
}


class cs_sortable extends cs_fields_container_multiple{

  private $deltas = array();

  public function add_field($name, $field) {
    //force every field to have its own tab.
    $this->deltas[$name] = count($this->get_fields());
    return parent::add_field($name, $field, $this->deltas[$name]);
  }

  public function pre_render(cs_form $form){
    $id = $this->get_html_id();
    $form->add_js("\$('#{$id}','#{$form->get_id()}').sortable({
      placeholder: \"ui-state-highlight\",
      stop: function( event, ui ) {
      $(this).find('input[type=hidden][name*=\"sortable-delta-\"]').each(function(index,elem){
        $(elem).val(index);
      });
      }
    });");

    parent::pre_render($form);
  }

  public function render_field(cs_form $form) {
    $id = $this->get_html_id();

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    foreach($this->tabs as $tabindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$tabindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_tab_fields($tabindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_tab_fields($tabindex));

      // $output .= "<h3>".$this->tabs[$tabindex]['title']."</h3>";
      $output .= "<div id=\"{$id}-sortable-{$tabindex}\"  class=\"tab-inner ui-state-default\">\n<span class=\"ui-icon ui-icon-arrowthick-2-n-s\" style=\"display: inline-block;\"></span><div style=\"display: inline-block;\">\n";
      foreach ($this->get_tab_fields($tabindex) as $name => $field) {
        $output .= $field->render($form);
      }
      $output .= "<input type=\"hidden\" name=\"{$id}-delta-{$tabindex}\" value=\"{$tabindex}\" />\n";
      $output .= "</div></div>\n";
    }
    $output .= "</div>\n";

    return $output;
  }

  public function values() {
    $output = array();

    $fields_with_delta = $this->get_fields_with_delta();
    usort($fields_with_delta, 'cs_sortable::orderby_delta');

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

  public function process($values) {
    foreach ($this->get_fields() as $name => $field) {
      $tabindex = $this->get_tabindex($field->get_name());

      if( $field instanceof cs_fields_container ) $this->get_field($name)->process($values);
      else if(isset($values[$name])){
        $this->get_field($name)->process($values[$name], $name);
      }

      $this->deltas[$name]=isset($values[$this->get_html_id().'-delta-'.$tabindex]) ? $values[$this->get_html_id().'-delta-'.$tabindex] : 0;
    }
  }

  private function get_fields_with_delta(){
    $out = array();
    foreach($this->get_fields() as $key => $field){
      $out[$key]=array('field'=> $field,'delta'=>$this->deltas[$key]);
    }
    return $out;
  }

  private static function orderby_delta($a,$b){
    if($a['delta']==$b['delta']) return 0;
    return ($a['delta']>$b['delta']) ? 1:-1;
  }

}


/* #########################################################
   ####                 ACCESSORIES                     ####
   ######################################################### */


class cs_ordered_functions implements Iterator{
  private $position = 0;
  private $array = array();
  private $sort_callback = NULL;

  public function __construct(array $array, $type, $sort_callback = NULL) {
      $this->position = 0;
      $this->array = $array;
      $this->type = $type;
      $this->sort_callback = $sort_callback;
      $this->sort();
  }

  function sort(){
    // $this->array = array_filter( array_map('trim', $this->array) );
    // $this->array = array_unique( array_map('strtolower', $this->array) );

    foreach ($this->array as &$value) {
      if(is_string($value)){
        $value = strtolower(trim($value));
      }else if(is_array($value) && isset($value[$this->type])){
        $value[$this->type] = strtolower(trim($value[$this->type]));
      }
    }
    $this->array = array_unique($this->array);

    if(!empty($this->sort_callback) && is_callable($this->sort_callback)){
      usort($this->array, $this->sort_callback);
    }
  }

  function rewind() {
    $this->position = 0;
    $this->sort();
  }

  function current() {
    return $this->array[$this->position];
  }

  function key() {
    return $this->position;
  }

  function next() {
    ++$this->position;
  }

  function valid() {
    return isset($this->array[$this->position]);
  }

  public function has_value($value){
    // return in_array($value, $this->array);
    return in_array($value, $this->values());
  }

  public function has_key($key){
    return in_array($key, array_keys($this->array));
  }

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

  public function keys(){
    return array_keys($this->array);
  }

  public function add_element($value){
    $this->array[] = $value;
    $this->sort();
  }

  public function remove_element($value){
    $this->array = array_diff($this->array, array($value));
    $this->sort();
  }
}
