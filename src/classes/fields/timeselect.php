<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Base\field;

/**
 * the time select group field class
 */
class timeselect extends field {

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
  public function __construct($options = [], $name = NULL) {

    $this->default_value = [
      'hours'=>0,
      'minutes'=>0,
      'seconds'=>0,
    ];

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
    $attributes = $this->get_attributes( ['type','name','id','size','hours','minutes','seconds'] );

    $output .= "<div id=\"{$id}\"{$attributes}>";

    $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
    if(isset($this->attributes['hours']) && is_array($this->attributes['hours'])){
      if($this->disabled == TRUE) $this->attributes['hours']['disabled']='disabled';
      $attributes = $this->get_attributes_string($this->attributes['hours'], ['type','name','id','value'] );
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
        $attributes = $this->get_attributes_string($this->attributes['minutes'], ['type','name','id','value'] );
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
          $attributes = $this->get_attributes_string($this->attributes['seconds'], ['type','name','id','value'] );
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
    $this->value = [
      'hours' => $value['hours'],
    ];
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
