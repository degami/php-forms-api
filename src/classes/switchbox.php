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
 * the switch selection field class
 */
class switchbox extends radios {

  /** @var string $no_label */
  protected $no_label;

  /** @var string $yes_label */
  protected $yes_label;

  public function __construct(array $options = [], $name = NULL) {
    $this->no_label = $this->get_text('No');
    $this->yes_label = $this->get_text('Yes');

    // labels can be overwrite
    parent::__construct($options, $name);

    // "options" is overwritten
    $this->options = array(
      0 => $this->no_label,
      1 => $this->yes_label,
    );
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();


    foreach($this->options as $key => $value){
      $this->add_js("\$('#{$id}-{$key}','#{$form->get_id()}')
        .click(function(evt){ 
          \$(this).closest('label').addClass('ui-state-active'); 
          \$('#{$id} input[type=\"radio\"]').not(this).closest('label').removeClass('ui-state-active'); 
         });");
    }

    $this->add_css("#{$id} .label-switch{ text-align: center; display: inline-block; width: 50%; padding-top: 10px; padding-bottom: 10px; box-sizing: border-box;}");
    $this->add_js("\$('#{$id}','#{$form->get_id()}').find('input[type=\"radio\"]:checked').closest('label').addClass('ui-state-active');");
    //$this->add_css("#{$id} .label-switch input{ display: none; }");
    $this->add_js("\$('#{$id} input[type=\"radio\"]','#{$form->get_id()}').hide();");
    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = "<div class=\"options ui-widget-content ui-corner-all\" id=\"{$id}\">";
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
      $output .= "<label class=\"label-switch ui-widget ui-state-default\" id=\"{$id}-{$key}-button\" for=\"{$id}-{$key}\"><input type=\"radio\" id=\"{$id}-{$key}\" name=\"{$this->name}\" value=\"{$key}\"{$checked}{$attributes} />{$value}</label>";
    }
    $output .= "</div>";
    return $output;
  }

}