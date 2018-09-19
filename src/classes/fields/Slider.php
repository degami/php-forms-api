<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\Form;

/**
 * the "slider" select field class
 */
class Slider extends Select
{

    /**
     * show value on change
     *
     * @var boolean
     */
    protected $with_val = false;


    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options, $name = null)
    {
        // get the "default_value" index value
        $values = call_user_func_array([__CLASS__, 'arrayGetValues'], [ $this->default_value, $this->options ]);
        $oldkey_value = end($values);

        // flatten the options array ang get a numeric keyset
        $options['options'] = call_user_func_array([__CLASS__, 'arrayFlatten'], [ $options['options'] ]);

        // search the new index
        $this->value = $this->default_value = array_search($oldkey_value, $this->options);

        if (!isset($options['attributes']['class'])) {
            $options['attributes']['class'] = '';
        }
        $options['attributes']['class'].=' slider';

        if (isset($options['multiple'])) {
            $options['multiple'] = false;
        }

        parent::__construct($options, $name);
    }

    /**
     * pre_render hook
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        $add_js = '';
        if ($this->with_val == true) {
            $add_js .= "
      var text = \$( '#{$id}' )[ 0 ].options[ \$( '#{$id}' )[ 0 ].selectedIndex ].label;
      \$('#{$id}-show_val','#{$form->getId()}').text( text );";
        }
        $this->addJs(
            "
      \$('#{$id}-slider','#{$form->getId()}').slider({
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
    }).hide();"
        );

        parent::preRender($form);
    }

    /**
     * render_field hook
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $text =  isset($this->default_value) && $this->optionsHasKey($this->default_value) ?
                $this->options[ $this->default_value ]->getLabel() :
                '';
        if (trim($text) == '' && count($this->options) > 0) {
            /** @var \Degami\PHPFormsApi\Fields\Option $option */
            $option = reset($this->options);
            $text = $option->getLabel();
        }
        if (!preg_match("/<div id=\"{$id}-slider\"><\/div>/i", $this->suffix)) {
            $this->suffix = "<div id=\"{$id}-slider\"></div>" .
                            (($this->with_val == true) ? "<div id=\"{$id}-show_val\">{$text}</div>" : '') .
                            $this->suffix;
        }
        return parent::renderField($form);
    }
}
