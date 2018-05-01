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
use Degami\PHPFormsApi\Abstracts\Base\field;

/*
 * the "Multiselect select" field class
 */
class multiselect extends select{

  private $leftOptions;
  private $rightOptions;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options,$name) {
    if(!is_array($options)) $options = [];
    $options['multiple'] = TRUE;
    parent::__construct($options,$name);

    $this->leftOptions = $this->options;
    $this->rightOptions = [];

    foreach ($this->get_default_value() as $value) {
      foreach( $this->leftOptions as $k => $v ){
        if( $v->get_key() == $value ){
          $this->rightOptions[] = clone $v;
          unset($this->leftOptions[$k]);
        }
      }
    }

    $this->set_attribute('style','width: 100%;');
  }

  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}_move_right, #{$id}_move_left','#{$form->get_id()}').click(function(evt){
      evt.preventDefault();
      var \$this = \$(this);
      var \$from = \$('#{$id}_from','#{$form->get_id()}');
      var \$to = \$('#{$id}_to','#{$form->get_id()}');

      if( /_move_right\$/i.test(\$this.attr('id')) ){
        \$from.find('option:selected').each(function(index,elem){ var \$elem = \$(elem); \$elem.appendTo(\$to); });
      }
      if( /_move_left\$/i.test(\$this.attr('id')) ){
        \$to.find('option:selected').each(function(index,elem){ var \$elem = \$(elem); \$elem.appendTo(\$from); });
      }
    });");

    $this->add_js("\$('#{$form->get_id()}').submit(function(evt){
      var \$to = \$('#{$id}_to','#{$form->get_id()}');
      \$to.find('option').each(function(index,elem){elem.selected=true;});
    });");

    parent::pre_render($form);
  }

  public function process($value = []){
    parent::process($value);

    $this->leftOptions = $this->options;
    $this->rightOptions = [];

    $values = $this->get_value();
    foreach( array_values($values) as $keyval){
      foreach( $this->leftOptions as $k => $v ){
        if( $v->get_key() == $keyval ){
          $this->rightOptions[] = clone $v;
          unset($this->leftOptions[$k]);
        }
      }
    }
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

    $extra = ' multiple="multiple" size="'.$this->size.'" ';
    $field_name = "{$this->name}[]";

    $output .= "<table id=\"{$id}-table\" border=0 colspan=0 cellpadding=0><tr><td style=\"width: 45%\">\n";
    $output .= "<select name=\"{$this->name}_from\" id=\"{$id}_from\"{$extra}{$attributes}>\n";
    if(isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])){
      $output .= '<option disabled '.( isset($this->default_value) ? '' : 'selected').'>'.$this->attributes['placeholder'].'</option>';
    }
    foreach ($this->leftOptions as $key => $value) {
      $output .= $value->render($this);
    }
    $output .= "</select>\n</td><td style=\"width: 10%\" align=\"center\">";

    $output .= '<div class="buttons">';
    $output .= "<button id=\"{$this->name}_move_right\">&gt;&gt;</button><br /><br />";
    $output .= "<button id=\"{$this->name}_move_left\">&lt;&lt;</button>";
    $output .= "</div>\n";

    $output .= "</td><td style=\"width: 45%\"><select name=\"{$field_name}\" id=\"{$id}_to\"{$extra}{$attributes}>\n";
    if(isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])){
      $output .= '<option disabled '.( isset($this->default_value) ? '' : 'selected').'>'.$this->attributes['placeholder'].'</option>';
    }
    foreach ($this->rightOptions as $key => $value) {
      $output .= $value->render($this);
    }
    $output .= "</select>\n</td></tr></table>";
    return $output;
  }
}
