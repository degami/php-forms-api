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
