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
use Degami\PHPFormsApi\Accessories\tag_element;

/**
* the colorpicker input field class
*/
class colorpicker extends field {
  /**
  * class constructor
  * @param array  $options build options
  * @param string $name    field name
  */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options, $name);
    if( !empty($this->default_value) && !$this->is_RGB($this->default_value)){
      $this->value = $this->default_value = '#000000';
    }
  }

  private function is_RGB($str){
    return preg_match("/^#?([a-f\d]{3}([a-f\d]{3})?)$/i", $str) ;
  }

  /**
  * render_field hook
  * @param  form $form form object
  * @return string        the element html
  */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    $this->attributes['class'] .= ' ui-state-disabled';
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    if( is_array($this->value) ) $this->value = '';

    $output = "
    <div id=\"{$id}\">
      <div class=\"clearfix\">
        <div id=\"{$id}-red\"></div>
        <div id=\"{$id}-green\"></div>
        <div id=\"{$id}-blue\"></div>
        <div id=\"{$id}-swatch\" class=\"ui-widget-content ui-corner-all\"></div>
      </div>";

      $tag = new tag_element([
        'tag' => 'input',
        'type' => 'text',
        'name' => $this->name,
        'value' => htmlspecialchars($this->value),
        'attributes' => $this->attributes + ['size' => $this->size, 'onFocus' => "blur();" ],
      ]);
      $output .= $tag->render_tag();
    $output .= "</div>\n";
    return $output;
  }

  /**
  * pre_render hook
  * @param  form $form form object
  */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    $js_func_hexFromRGB = "(function(r, g, b){
      var hex = [
        r.toString( 16 ),
        g.toString( 16 ),
        b.toString( 16 )
      ];
      \$.each( hex, function( nr, val ) {
        if ( val.length === 1 ) {
          hex[ nr ] = \"0\" + val;
        }
      });
      return hex.join( \"\" ).toUpperCase();
    })";

    $js_func_refreshSwatch = "function refreshSwatch() {
      var red = \$( \"#{$id}-red\" ).slider( \"value\" ),
      green = \$( \"#{$id}-green\" ).slider( \"value\" ),
      blue = \$( \"#{$id}-blue\" ).slider( \"value\" ),
      hex = $js_func_hexFromRGB( red, green, blue );
      \$( \"#{$id}-swatch\" ).css( \"background-color\", \"#\" + hex );
      \$( \"input[name='{$this->name}']\", \"#{$id}\" ).val(\"#\" + hex );
    }";

    $this->add_js("
      \$('#{$id}-red,#{$id}-green,#{$id}-blue','#{$form->get_id()}').slider({
        orientation: \"horizontal\",
        range: \"min\",
        max: 255,
        slide: {$js_func_refreshSwatch},
        change: {$js_func_refreshSwatch}
      });");

    $this->add_css("
      #{$id} {padding-top: 20px;}
      #{$id}-red, #{$id}-green, #{$id}-blue {
        float: left;
        clear: left;
        width: 300px;
        margin: 5px;
      }
      #{$id}-swatch {
        width: 120px;
        height: 100px;
        margin-top: -15px;
        margin-left: 350px;
        background-image: none;
      }
      #{$id}-red .ui-slider-range { background: #ef2929; }
      #{$id}-red .ui-slider-handle { border-color: #ef2929; }
      #{$id}-green .ui-slider-range { background: #8ae234; }
      #{$id}-green .ui-slider-handle { border-color: #8ae234; }
      #{$id}-blue .ui-slider-range { background: #729fcf; }
      #{$id}-blue .ui-slider-handle { border-color: #729fcf; }
      #{$id} .clearfix{display: table;width:100%;clear: both;float: none;padding-bottom: 15px;}
    ");

    if(!empty($this->value)){
      $this->add_js("(function(hex){
        var result = /^#?([a-f\d]{3}([a-f\d]{3})?)$/i.exec(hex);
        if( result ){
          if(undefined == result[2]) {
            result[1] = (result[1].split(\"\")).map(function(elem){ return elem.repeat(2); }).join(\"\");
          }
          result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec('#'+result[1]);

          \$( \"#{$id}-red\" ).slider( \"value\", parseInt(result[1], 16) );
          \$( \"#{$id}-green\" ).slider( \"value\", parseInt(result[2], 16) );
          \$( \"#{$id}-blue\" ).slider( \"value\", parseInt(result[3], 16) );
        }
      })( \$(\"input[name='{$this->name}']\", \"#{$id}\").val() )");
      $this->add_css("#{$id}-swatch { background-color: {$this->value};}");
    }

    parent::pre_render($form);
  }

  /**
  * is_a_value hook
  * @return boolean this is a value
  */
  public function is_a_value(){
    return TRUE;
  }
}
