<?php
/**
 * PHP FORMS API
 * PHP Version 5.5
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * The colorpicker input field class
 */
class Colorpicker extends Field
{
    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        if (!empty($this->default_value) && !$this->isRGB($this->default_value)) {
            $this->value = $this->default_value = '#000000';
        }
    }

    /**
     * Check if string is an RGB representation
     *
     * @param  string $str string to check
     * @return boolean     true if string is RGB
     */
    private function isRGB($str)
    {
        return preg_match("/^#?([a-f\d]{3}([a-f\d]{3})?)$/i", $str);
    }

    /**
     * {@inheritdoc}
     *
     * @param  Form $form form object
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        $this->attributes['class'] .= ' ui-state-disabled';
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }
        if (is_array($this->value)) {
            $this->value = '';
        }

        $tag = new TagElement([
          'tag' => 'div', 'id' => $id,
        ]);

        $tag->addChild("<div class=\"clearfix\">
                <div id=\"{$id}-red\"></div>
                <div id=\"{$id}-green\"></div>
                <div id=\"{$id}-blue\"></div>
                <div id=\"{$id}-swatch\" class=\"ui-widget-content ui-corner-all\"></div>
        </div>");

        $tag->addChild(new TagElement([
          'tag' => 'input',
          'type' => 'text',
          'name' => $this->name,
          'value' => htmlspecialchars($this->value),
          'attributes' => $this->attributes + ['size' => $this->size, 'onFocus' => "blur();" ],
        ]));

        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();

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

        $this->addJs(
            "
      \$('#{$id}-red,#{$id}-green,#{$id}-blue','#{$form->getId()}').slider({
        orientation: \"horizontal\",
        range: \"min\",
        max: 255,
        slide: {$js_func_refreshSwatch},
        change: {$js_func_refreshSwatch}
      });"
        );

        $this->addCss(
            "
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
    "
        );

        if (!empty($this->value)) {
            $this->addJs(
                "(function(hex){
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
      })( \$(\"input[name='{$this->name}']\", \"#{$id}\").val() )"
            );
            $this->addCss("#{$id}-swatch { background-color: {$this->value};}");
        }

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}
