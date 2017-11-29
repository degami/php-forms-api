<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                  FIELD BASE                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Base;

use Degami\PHPFormsApi\Accessories\ordered_functions;
use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Base\fields_container;
use Degami\PHPFormsApi\Fields\checkbox;
/**
 * the field element class.
 * @abstract
 */
abstract class field extends element{

  /**
   * validate functions list
   * @var array
   */
  protected $validate = [];

  /**
   * preprocess functions list
   * @var array
   */
  protected $preprocess = [];

  /**
   * postprocess functions list
   * @var array
   */
  protected $postprocess = [];

  /**
   * element js events list
   * @var array
   */
  protected $event = [];

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
  public function __construct($options = [], $name = NULL) {
    if($options == NULL) $options = [];
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
      $this->type = substr(get_class($this), strrpos(get_class($this), '\\') + 1);
    }

    if(!$this->validate instanceof ordered_functions){
      $this->validate = new ordered_functions($this->validate,'validator', [ form::class,'order_validators' ] );
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
    $this->set_errors( [] );
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
        $this->value = call_user_func( [$this, $processor_func], $this->value );
      } else {
        if(method_exists(form::class, $processor_func)){
          $this->value = call_user_func( [form::class,$processor_func], $this->value );
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
    $this->set_errors( [] );

    foreach ($this->validate as $validator) {
      $matches = [];
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
        $error = call_user_func( [get_class($this), $validator_func], $this->value, $options );
      }else {
        if(method_exists(form::class, $validator_func)){
          $error = call_user_func( [form::class, $validator_func], $this->value, $options );
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
