<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

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
