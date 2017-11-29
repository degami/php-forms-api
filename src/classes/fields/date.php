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
  public function __construct($options = [], $name = NULL) {

    $this->start_year = date('Y')-100;
    $this->end_year = date('Y')+100;
    $this->default_value = [
      'year'=>date('Y'),
      'month'=>date('m'),
      'day'=>date('d'),
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
    $attributes = $this->get_attributes( ['type','name','id','size','day','month','year'] );

    $output .= "<div id=\"{$id}\"{$attributes}>";

    if($this->granularity!='year' && $this->granularity!='month'){
      $attributes = ''.($this->disabled == TRUE) ? ' disabled="disabled"':'';
      if(isset($this->attributes['day']) && is_array($this->attributes['day'])){
        if($this->disabled == TRUE) $this->attributes['day']['disabled']='disabled';
        $attributes = $this->get_attributes_string($this->attributes['day'], ['type','name','id','value'] );
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
        $attributes = $this->get_attributes_string($this->attributes['month'], ['type','name','id','value'] );
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
      $attributes = $this->get_attributes_string($this->attributes['year'], ['type','name','id','value'] );
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
    $this->value = [
      'year' => $value['year'],
    ];
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
