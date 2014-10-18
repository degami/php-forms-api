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

define('FORMS_DEFAULT_PREFIX', '<div class="form-container">');
define('FORMS_DEFAULT_SUFFIX', '</div>');
define('FORMS_DEFAULT_FIELD_PREFIX', '<div class="form-item">');
define('FORMS_DEFAULT_FIELD_SUFFIX', '</div>');
define('FORMS_VALIDATE_EMAIL_DNS', TRUE);
define('FORMS_VALIDATE_EMAIL_BLOCKED_DOMAINS', 'mailinator.com|guerrillamail.com');
define('FORMS_BASE_PATH', '');
define('FORMS_XSS_ALLOWED_TAGS', 'a|em|strong|cite|code|ul|ol|li|dl|dt|dd');

// Here are some prioity things I'm working on:
// TODO: Support edit forms by allowing an array of values to be specified, not just taken from _REQUEST


class cs_form {

  protected $form_id = 'cs_form';
  protected $form_token = '';
  protected $action = '';
  protected $attributes = array();
  protected $method = 'post';
  protected $prefix = FORMS_DEFAULT_PREFIX;
  protected $suffix = FORMS_DEFAULT_SUFFIX;
  protected $validate = array();
  protected $processed = FALSE;
  protected $preprocessors = FALSE;
  protected $validated = FALSE;
  protected $submitted = FALSE;
  protected $valid = NULL;
  protected $submit = '';
  protected $error = '';
  protected $js = array();

  protected $insert_field_order = array();
  protected $fields = array();

  public function __construct($options = array()) {
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
    if (empty($this->submit)) {
      $this->submit = "{$this->form_id}_submit";
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
      }
    }
    return $output;
  }

  public function reset() {
    foreach ($this->get_fields() as $name => $field) {
      $field->reset();
      unset($_POST[$name]);
    }
    unset($_REQUEST['form_id']);
    $this->processed = FALSE;
    $this->validated = FALSE;
    $this->submitted = FALSE;
  }

  public function is_submitted() {
    return $this->submitted;
  }

  public function process() {
    if (!$this->processed) {
      $request = (strtolower($this->method) == 'post') ? $_POST : $_GET;
      if (isset($request['form_id']) && $request['form_id'] == $this->form_id) {
        // foreach ($request as $name => $value) {
        //   if ( $this->get_field($name) !== NULL ) {
        //     $this->get_field($name)->process($value, $name);
        //   }
        // }
        foreach ($this->get_fields() as $name => $field) {
          if( $field instanceof cs_fields_container ) $this->get_field($name)->process($request);
          else if ( !empty($request[$name]) ) {
            $this->get_field($name)->process($request[$name], $name);
          }
        }
        $this->processed = TRUE;
      }
    }
    if($this->processed == TRUE){
      if (!$this->preprocessors) {
        foreach ($this->get_fields() as $name => $field) {
          $field->preprocess();
        }
      }
      if ((!$this->submitted) && $this->valid()) {
        $this->submitted = TRUE;
        $submit_function = $this->submit;
        if (function_exists($submit_function)) {
          foreach ($this->get_fields() as $name => $field) {
            $field->postprocess();
          }
          $submit_function($this, (strtolower($this->method) == 'post') ? $_POST : $_GET);
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
      //$this->valid = FALSE;
    } else if ($_REQUEST['form_id'] == $this->form_id) {
      $sid = session_id();
      if (!empty($sid)) {
        $this->valid = FALSE;
        $this->error = 'Form is invalid or has expired';
        if (isset($_REQUEST['form_token']) && isset($_SESSION['form_token'][$_REQUEST['form_token']])) {
          if ($_SESSION['form_token'][$_REQUEST['form_token']] >= $_SERVER['REQUEST_TIME'] - 7200) {
            $this->valid = TRUE;
            $this->error = '';
            unset($_SESSION['form_token'][$_REQUEST['form_token']]);
          }
        }
      }
      foreach ($this->get_fields() as $field) {
        if (!$field->valid()) {
          $this->valid = FALSE;
        }
      }
      $this->validated = TRUE;
      return $this->valid;
    }
    return NULL;
  }

  public function set_attribute($name,$value){
    $this->attributes[$name] = $value;
  }

  public function add_field($name, $field) {
    if (!is_object($field)) {
      $field_type = isset($field['type']) ? "cs_{$field['type']}" : 'cs_textfield';
      $field = new $field_type($field, $name);
    }
    $this->fields[$name] = $field;
    $this->insert_field_order[] = $name;
  }

  public function &get_fields(){
    return $this->fields;
  }

  public function get_field($field_name){
    return isset($this->fields[$field_name]) ? $this->fields[$field_name] : NULL;
  }

  public function show_errors() {
    return empty($this->error) ? '' : "<li>{$this->error}</li>";
  }

  public function render() {
    $output = $this->prefix;
    if ( $this->valid() === FALSE) {
      $output .= "<div class=\"errors ui-state-error ui-corner-all\"><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span><ul>";
      $output .= $this->show_errors();
      foreach ($this->get_fields() as $field) {
        $output .= $field->show_errors();
      }
      $output .= "</div>";
    }

    // uasort($this->fields, 'cs_form::order_by_weight');

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

    $attributes = '';
    foreach ($this->attributes as $key => $value) {
      if($key == 'action' || $key == 'method') continue;
      $attributes .= " {$key}=\"{$value}\"";
    }

    $output .= "<form action=\"{$this->action}\" method=\"{$this->method}\"{$attributes}>\n";
    $output .= $fields_html;
    $output .= "<input type=\"hidden\" name=\"form_id\" value=\"{$this->form_id}\" />\n";
    $output .= "<input type=\"hidden\" name=\"form_token\" value=\"{$this->form_token}\" />\n";
    $output .= "</form>\n";

    $this->js = array_filter(array_map('trim',$this->js));
    if(!empty( $this->js )){
      foreach($this->js as &$js_string){
        if($js_string[strlen($js_string)-1] == ';'){
          $js_string = substr($js_string,0,strlen($js_string)-1);
        }
      }
      $output .= "
      <script type=\"text/javascript\">
        (function($){
          $(document).ready(function(){
            ".implode(";\n",$this->js).";
          });
        })(jQuery);
      </script>";
    }
    return $output . $this->suffix;
  }

  public function add_js($js){
    $this->js[] = $js;
  }

  public static function validate_required($value = NULL) {
    if (!empty($value)) {
      return TRUE;
    } else {
      return "<em>%t</em> is required";
    }
  }

  public static function validate_max_length($value, $options) {
    if (strlen($value) > $options) {
      return "Maximum length of <em>%t</em> is {$options}";
    }
    return TRUE;
  }
  public static function validate_min_length($value, $options) {
    if (strlen($value) < $options) {
      return "<em>%t</em> must be longer than {$options}";
    }
    return TRUE;
  }
  public static function validate_exact_length($value, $options) {
    if (strlen($value) != $options) {
      return "<em>%t</em> must be {$options} characters long.";
    }
    return TRUE;
  }
  public static function validate_alpha($value) {
    if (!preg_match( "/^([a-z])+$/i", $value)) {
      return "<em>%t</em> must contain alphabetic characters.";
    }
    return TRUE;
  }

  protected function validate_alpha_numeric($value) {
    if (!preg_match("/^([a-z0-9])+$/i", $value)) {
      return "<em>%t</em> must only contain alpha numeric characters.";
    }
    return TRUE;
  }

  protected function validate_alpha_dash($value) {
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
    $options = explode(',', $options);
    $ext = substr(strrchr($value['filepath'], '.'), 1);
    if (!in_array($ext, $options)) {
      return "File upload <em>%t</em> is not of required type";
    }
    return TRUE;
  }
  public static function validate_file_not_exists($value) {
    if (file_exists($value['filepath'])) {
      return "The file <em>%t</em> has already been uploaded";
    }
    return TRUE;
  }
  public static function validate_max_file_size($value, $options) {
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
    if (empty($email)) return TRUE;
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
    $attr2 = implode(' ', _filter_xss_attributes($attrlist));
    $attr2 = preg_replace('/[<>]/', '', $attr2);
    $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

    return "<$elem$attr2$xhtml_slash>";
  }

  public static function process_plain($text) {
    // if using PHP < 5.2.5 add extra check of strings for valid UTF-8
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  public static function attributes($attributes) {
    if (is_array($attributes)) {
      $t = '';
      foreach ($attributes as $key => $value) {
        $t .= " $key=" . '"' . cs_form::process_plain($value) . '"';
      }
      return $t;
    }
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
         if (is_array($value)){ $return = array_merge($return, cs_form::array_flatten($value));}
         else {$return[$key] = $value;}
     }
     return $return;
  }

  public static function array_get_values($search_key, $array) {
     $return = array();
     foreach ($array as $key => $value) {
         if (is_array($value)){ $return = array_merge($return, cs_form::array_get_values($search_key, $value));}
         else if($key == $search_key){$return[] = $value;}
     }
     return $return;
  }

  public static function order_by_weight($a, $b){
      if ($a->get_weight() == $b->get_weight()) {
        return 0;
      }
      return ($a->get_weight() < $b->get_weight()) ? -1 : 1;
  }
}

abstract class cs_field {

  protected $ajax = FALSE;
  protected $error = NULL;
  protected $validate = array();
  protected $preprocess = array();
  protected $postprocess = array();
  protected $prefix = FORMS_DEFAULT_FIELD_PREFIX;
  protected $suffix = FORMS_DEFAULT_FIELD_SUFFIX;
  protected $size = 60;
  protected $weight = 0;
  protected $name = NULL;
  protected $id = NULL;
  protected $title = NULL;
  protected $description = NULL;
  protected $attributes = array();
  protected $disabled = FALSE;
  protected $default_value = NULL;
  protected $value = NULL;

  public function __construct($options = array(), $name = NULL) {
    $this->name = $name;
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
    $this->value = $this->default_value;
  }

  public function values() {
    return $this->value;
  }

  public function reset() {
    $this->value = $this->default_value;
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
      $processor = "process_{$processor}";
      if (function_exists($processor)) {
        $this->value = $processor($this->value);
      } else {
        $this->value = cs_form::$processor($this->value);
      }
    }
  }
  public function postprocess() {
    $this->preprocess("postprocess");
  }

  public function valid() {
    foreach ($this->validate as $validator) {
      $matches = array();
      if(is_array($validator)){
        $validator_func = $validator['validator'];
      }else{
        $validator_func = $validator;
      }
      preg_match('/^([A-Za-z0-9_]+)(\[(.+)\])?$/', $validator_func, $matches);
      $validator_func = "validate_{$matches[1]}";
      $options = isset($matches[3]) ? $matches[3] : NULL;
      if (function_exists($validator_func)) {
        $error = $validator_func($this->value, $options);
      } else {
        $error = cs_form::$validator_func($this->value, $options);
      }
      if ($error !== TRUE) {
        $this->error = str_replace('%t', $this->title, $error);
        if(is_array($validator) && !empty($validator['error_message'])){
          $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;
          $this->error = str_replace('%t', $titlestr, $validator['error_message']);
        }
        return FALSE;
      }
    }
    return TRUE;
  }

  public function show_errors() {
    return empty($this->error) ? '' : "<li>{$this->error}</li>";
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
        $attributes .= " {$key}=\"{$value}\"";
      }
    }
    $attributes = trim($attributes);
    return empty($attributes) ? '' : ' ' . $attributes;
  }

  public function get_prefix(){
    if (!empty($this->error)) {
      if(preg_match("/class=\".*?\"/i", $this->prefix)){
        return preg_replace("/class=\"(.*?)\"/i", "class=\"\${1} error\"", $this->prefix);
      }
    }
    return $this->prefix;
  }
  public function get_suffix(){
    return $this->suffix;
  }

  abstract public function render(cs_form $form); // renders html
  abstract public function is_a_value();          // tells if component value is passed on the parent values() function call
}

class cs_submit extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    $this->name = $name;
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
  }

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Submit';
    }
    $this->attributes['class'] = trim('submit '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = $this->get_prefix();
    $output .= "<input type=\"submit\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }

  public function valid() {
    return TRUE;
  }
}

class cs_reset extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    $this->name = $name;
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
  }

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    if (empty($this->value)) {
      $this->value = 'Reset';
    }
    $this->attributes['class'] = trim('reset '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = $this->get_prefix();
    $output .= "<input type=\"reset\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return FALSE;
  }

  public function valid() {
    return TRUE;
  }
}

class cs_button extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    $this->name = $name;
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
  }

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $this->attributes['class'] = trim('button '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $output = $this->get_prefix();
    $output .= "<button id=\"{$id}\" name=\"{$this->name}\"{$attributes}>{$this->value}</button>\n";
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }

  public function valid() {
    return TRUE;
  }
}

class cs_value extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    $this->name = $name;
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
  }

  public function render(cs_form $form) {
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
    $this->name = $name;
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
  }

  public function render(cs_form $form) {
    $output = $this->get_prefix();
    $output .= $this->value;
    return $output . $this->get_suffix();
  }

  public function valid() {
    return TRUE;
  }

  public function is_a_value(){
    return FALSE;
  }
}

class cs_hidden extends cs_field {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $this->attributes['class'] = trim('hidden '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    $attributes = $this->get_attributes();
    return "<input type=\"hidden\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_textfield extends cs_field {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $this->attributes['class'] = trim('textfield '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_autocomplete extends cs_field{
  protected $autocomplete_path = FALSE;
  protected $options = array();
  protected $min_length = 3;

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $this->attributes['class'] = trim('autocomplete '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }

    $form->add_js("
      \$( \"#{$id}\" )
      .bind( \"keydown\", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
        $( this ).autocomplete( \"instance\" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        source: ".((!empty($this->options)) ? json_encode($this->options) : "\"{$this->autocomplete_path}\"").",
        minLength: {$this->min_length},
        focus: function() {
          return false;
        }
      });
    ");

    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_textarea extends cs_field {
  protected $rows = 5;

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $this->attributes['class'] = trim('textarea '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('name','id','value','rows','cols'));
    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<textarea id=\"{$id}\" name=\"{$this->name}\" cols=\"{$this->size}\" rows=\"{$this->rows}\"{$attributes}>\n";
    $output .= $this->value;
    $output .= "</textarea>";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}


class cs_password extends cs_field {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $this->attributes['class'] = trim('password '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();
    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<input type=\"password\" id=\"{$id}\" name=\"{$this->name}\" value=\"\"{$attributes} />\n";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
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


  private static function has_key($needle, $haystack) {
    foreach ($haystack as $key => $value) {
        if ($needle == $key) {
            return TRUE;
        } else if(is_array($value)) {
            if( $this->has_key($needle, $value) == TRUE ){
              return TRUE;
            }
        }
    }
    return FALSE;
  }

  private function options_has_key($needle){
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
        $this->error = "{$titlestr}: Invalid choice";
        return FALSE;
      }
    }
    return parent::valid();
  }
}

class cs_select extends cs_field_multivalues {
  protected $multiple = FALSE;

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $this->attributes['class'] = trim('select '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $extra = ($this->multiple) ? ' multiple' : '';
    $field_name = ($this->multiple) ? "{$this->name}[]" : $this->name;
    $output .= "<select name=\"{$field_name}\" id=\"{$id}\"{$extra}{$attributes}>\n";
    foreach ($this->options as $key => $value) {
      $selected = ($key == $this->value) ? ' selected="selected"' : '';
      if(is_array($value)){
        $output .= "<optgroup label=\"{$key}\">";
        foreach($value as $optgroupkey=>$optgroupvalue){
          $selected = ($optgroupkey == $this->value) ? ' selected="selected"' : '';
          $output .= "<option value=\"{$optgroupkey}\"{$selected}>{$optgroupvalue}</option>\n";
        }
        $output .= "</optgroup>";
      }else {
        $output .= "<option value=\"{$key}\"{$selected}>{$value}</option>\n";
      }
    }
    $output .= "</select>\n";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_slider extends cs_select{
  public function __construct($options, $name = NULL){
    parent::__construct($options, $name);

    // get the "default_value" index value
    $values = cs_form::array_get_values($this->default_value,$this->options);
    $oldkey_value = end($values);

    // flatten the options array ang get a numeric keyset
    $this->options = cs_form::array_flatten($this->options);

    // search the new index
    $this->value = $this->default_value = array_search($oldkey_value,$this->options);
  }

  public function render(cs_form $form){
    $id = $this->get_html_id();
    $this->suffix = "<div id=\"{$id}-slider\"></div>".$this->suffix;
    $form->add_js("
      \$('#{$id}-slider').slider({
        min: 1,
        max: ".count($this->options).",
        value: \$( \"#{$id}\" )[ 0 ].selectedIndex + 1,
        slide: function( event, ui ) {
          \$( \"#{$id}\" )[ 0 ].selectedIndex = ui.value - 1;
        }
      });
      \$( \"#{$id}\" ).change(function() {
        \$('#{$id}-slider').slider(\"value\", this.selectedIndex + 1 );
      }).hide();");
    return parent::render($form);
  }
}

class cs_radios extends cs_field_multivalues {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();
    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
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
      $output .= "<label><input type=\"radio\" id=\"{$id}-{$key}\" name=\"{$this->name}\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
    }
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_checkboxes extends cs_field_multivalues {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    if(!is_array($this->default_value)) {
      $this->default_value = array($this->default_value);
    }

    $output = $this->get_prefix();
    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }

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
      $output .= "<label><input type=\"checkbox\" id=\"{$id}-{$key}\" name=\"{$this->name}".(count($this->options)>1 ? "[]" : "")."\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>\n";
    }
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}


class cs_checkbox extends cs_field {
  public function __construct($options = array(), $name = NULL) {
    $this->name = $name;
    foreach ($options as $name => $value) {
      $this->$name = $value;
    }
  }

  public function render(cs_form $form) {
    $id = $this->get_html_id();

    $output = $this->get_prefix();
    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }

    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $checked = ($this->value == $this->default_value) ? ' checked="checked"' : '';
    $output .= "<label for=\"{$id}\"><input type=\"checkbox\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->default_value}\"{$checked}{$attributes} />{$this->title}</label>\n";

    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}


class cs_file extends cs_field {
  protected $uploaded = FALSE;
  protected $destination;

  public function __construct($options = array(), $name = NULL) {
    parent::__construct($options, $name);
    if (!isset($options['size'])) {
      $this->size = 30;
    }
  }

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $form->set_attribute('enctype', 'multipart/form-data');

    $this->attributes['class'] = trim('file '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','size'));

    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<input type=\"hidden\" name=\"{$this->name}\" value=\"{$this->name}\" />";
    $output .= "<input type=\"file\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\"{$attributes} />";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
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
  private $start_year;
  private $end_year;

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

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $this->attributes['class'] = trim('date '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('type','name','id','size'));

    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<div id=\"{$id}\">";
    $output .= "<select name=\"{$this->name}[day]\">";
    for($i=1;$i<=31;$i++){
      $selected = ($i == $this->value['day']) ? ' selected="selected"' : '';
      $output .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
    }
    $output .= "</select>";
    $output .= "<select name=\"{$this->name}[month]\">";
    for($i=1;$i<=12;$i++){
      $selected = ($i == $this->value['month']) ? ' selected="selected"' : '';
      $output .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
    }
    $output .= "</select>";
    $output .= "<select name=\"{$this->name}[year]\">";
    for($i=$this->start_year;$i<=$this->end_year;$i++){
      $selected = ($i == $this->value['year']) ? ' selected="selected"' : '';
      $output .= "<option value=\"{$i}\"{$selected}>{$i}</option>";
    }
    $output .= "</select>";
    $output .= "</div>";

    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
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
      $this->error = "{$titlestr}: Invalid date";
      return FALSE;
    }
    return parent::valid();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_datepicker extends cs_field {
  protected $date_format = 'yy-mm-dd';

  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $this->attributes['class'] = trim('textfield '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }

    $form->add_js("\$('#{$id}').datepicker({dateFormat: \"{$this->date_format}\"});");

    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}

class cs_spinner extends cs_field {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->get_prefix();

    $form->add_js("\$('#{$id}').spinner();");

    $this->attributes['class'] = trim('spinner '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    if (!empty($this->error)) {
      $this->attributes['class'] .= ' error';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes();

    $required = (in_array('required', $this->validate)) ? ' <span class="required">*</span>' : '';
    if (!empty($this->title)) {
      $output .= "<label for=\"{$id}\">{$this->title}{$required}</label>\n";
    }
    $output .= "<input type=\"text\" id=\"{$id}\" name=\"{$this->name}\" value=\"{$this->value}\"{$attributes} />\n";
    if (!empty($this->description)) {
      $output .= "<div class=\"description\">{$this->description}</div>";
    }
    return $output . $this->get_suffix();
  }

  public function is_a_value(){
    return TRUE;
  }
}

abstract class cs_fields_container extends cs_field {
  protected $insert_field_order = array();
  protected $fields = array();

  public function &get_fields(){
    return $this->fields;
  }

  public function get_field($field_name){
    return isset($this->fields[$field_name]) ? $this->fields[$field_name] : NULL;
  }

  public function add_field($name, $field) {
    if (!is_object($field)) {
      $field_type = isset($field['type']) ? "cs_{$field['type']}" : 'cs_textfield';
      $field = new $field_type($field, $name);
    }
    $this->fields[$name] = $field;
    $this->insert_field_order[] = $name;
  }

  public function values() {
    $output = array();
    foreach ($this->get_fields() as $name => $field) {
      if($field->is_a_value() == TRUE){
        $output[$name] = $field->values();
      }
    }
    return $output;
  }

  public function preprocess() {
    foreach ($this->get_fields() as $field) {
      $field->preprocess();
    }
  }
  public function process($values) {
    // foreach ($values as $name => $value) {
    //   $this->get_field($name)->process($value, $name);
    // }
    foreach ($this->get_fields() as $name => $field) {
      if( $field instanceof cs_fields_container ) $this->get_field($name)->process($values);
      else if(!empty($values[$name])){
        $this->get_field($name)->process($values[$name], $name);
      }
    }
  }

  public function valid() {
    $valid = TRUE;
    foreach ($this->get_fields() as $field) {
      if (!$field->valid()) {
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

class cs_fieldset extends cs_fields_container {
  protected $collapsible = FALSE;
  protected $collapsed = FALSE;

  public function render(cs_form $form) {
    static $js_collapsible_added = FALSE;
    $id = $this->get_html_id();
    $output = $this->prefix;
    $this->attributes['class'] = trim('fieldset '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
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
        $('fieldset.collapsible.collapsed .fieldset-inner').hide();");
        $js_collapsible_added = TRUE;
      }
    }
    $attributes = $this->get_attributes();
    $output .= "<fieldset id=\"{$id}\"{$attributes}>\n";
    if (!empty($this->title)) {
      $output .= "<legend>{$this->title}</legend>\n";
    }

    // uasort($this->fields, 'cs_form::order_by_weight');
    $insertorder = array_flip($this->insert_field_order);
    $weights = array();
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());

    $output .= "<div class=\"fieldset-inner\">\n";
    foreach ($this->get_fields() as $name => $field) {
      // $output .= $field->render("{$parent_name}[{$name}]");
      $output .= $field->render($form);
    }
    return $output ."</div></fieldset>\n". $this->suffix;
  }
}

abstract class cs_fields_container_tabbed extends cs_fields_container{
  protected $tabs = array();

  public function add_tab($title){
    $this->tabs[] = array('title'=>$title,'fieldnames'=>array());
  }

  public function add_field($name, $field, $tabindex = 0) {
    if (!is_object($field)) {
      $field_type = isset($field['type']) ? "cs_{$field['type']}" : 'cs_textfield';
      $field = new $field_type($field, $name);
    }
    $this->fields[$name] = $field;
    $this->insert_field_order[$tabindex][] = $name;
    $this->tabs[$tabindex]['fieldnames'][] = $name;
  }

  public function get_tab_fields($tabindex){
    $out = array();
    $fieldsnames = $this->tabs[$tabindex]['fieldnames'];
    foreach($fieldsnames as $name){
      $out[$name] = $this->get_field($name);
    }
    return $out;
  }
}

class cs_tabs extends cs_fields_container_tabbed {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->prefix;
    $this->attributes['class'] = trim('tabs '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    $attributes = $this->get_attributes();

    $form->add_js("\$('#{$id}').tabs();");
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

    return $output . $this->suffix;
  }
}

class cs_accordion extends cs_fields_container_tabbed {
  public function render(cs_form $form) {
    $id = $this->get_html_id();
    $output = $this->prefix;
    $this->attributes['class'] = trim('tabs '.(isset($this->attributes['class']) ? $this->attributes['class'] : ''));
    $attributes = $this->get_attributes();

    $form->add_js("\$('#{$id}').accordion();");
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

    return $output . $this->suffix;
  }
}
