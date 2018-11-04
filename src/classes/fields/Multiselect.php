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

/**
 * The "Multiselect select" field class
 */
class Multiselect extends Select
{
    /** @var array options on the left side */
    private $leftOptions;

    /** @var array options on the right side */
    private $rightOptions;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options, $name)
    {
        if (!is_array($options)) {
            $options = [];
        }
        $options['multiple'] = true;
        parent::__construct($options, $name);

        $this->leftOptions = $this->options;
        $this->rightOptions = [];

        foreach ($this->getDefaultValue() as $value) {
            foreach ($this->leftOptions as $k => $v) {
                /** @var \Degami\PHPFormsApi\Fields\Option $v */
                if ($v->getKey() == $value) {
                    $this->rightOptions[] = clone $v;
                    unset($this->leftOptions[$k]);
                }
            }
        }

        $this->setAttribute('style', 'width: 100%;');
    }


    /**
     * {@inheritdocs}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        $this->addJs(
            "\$('#{$id}_move_right, #{$id}_move_left','#{$form->getId()}')
            .click(function(evt){
              evt.preventDefault();
              var \$this = \$(this);
              var \$from = \$('#{$id}_from','#{$form->getId()}');
              var \$to = \$('#{$id}_to','#{$form->getId()}');
        
              if( /_move_right\$/i.test(\$this.attr('id')) ){
                \$from.find('option:selected').each(function(index,elem){ 
                    var \$elem = \$(elem); \$elem.appendTo(\$to); 
                });
              }
              if( /_move_left\$/i.test(\$this.attr('id')) ){
                \$to.find('option:selected').each(function(index,elem){
                    var \$elem = \$(elem); \$elem.appendTo(\$from);
                });
              }
            });"
        );

        $this->addJs(
            "\$('#{$form->getId()}').submit(function(evt){
            var \$to = \$('#{$id}_to','#{$form->getId()}');
            \$to.find('option').each(function(index,elem){elem.selected=true;});
        });"
        );

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $value value to set
     */
    public function processValue($value = [])
    {
        parent::processValue($value);

        $this->leftOptions = $this->options;
        $this->rightOptions = [];

        $values = $this->getValue();
        foreach (array_values($values) as $keyval) {
            foreach ($this->leftOptions as $k => $v) {
                /** @var \Degami\PHPFormsApi\Fields\Option $v */
                if ($v->getKey() == $keyval) {
                    $this->rightOptions[] = clone $v;
                    unset($this->leftOptions[$k]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();
        $output = '';

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }
        $attributes = $this->getAttributes();

        $extra = ' multiple="multiple" size="'.$this->size.'" ';
        $field_name = "{$this->name}[]";

        $output .= "<table id=\"{$id}-table\" border=0 colspan=0 cellpadding=0><tr><td style=\"width: 45%\">\n";
        $output .= "<select name=\"{$this->name}_from\" id=\"{$id}_from\" {$extra}{$attributes}>\n";
        if (isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])) {
            $output .= '<option disabled '.(isset($this->default_value) ? '' : 'selected').'>'.
                        $this->attributes['placeholder'].
                        '</option>';
        }
        foreach ($this->leftOptions as $key => $value) {
            /** @var \Degami\PHPFormsApi\Fields\Option $value */
            $output .= $value->renderHTML($this);
        }
        $output .= "</select>\n</td><td style=\"width: 10%\" align=\"center\">";

        $output .= '<div class="buttons">';
        $output .= "<button id=\"{$this->name}_move_right\">&gt;&gt;</button><br /><br />";
        $output .= "<button id=\"{$this->name}_move_left\">&lt;&lt;</button>";
        $output .= "</div>\n";

        $output .= "</td><td style=\"width: 45%\">
                          <select name=\"{$field_name}\" id=\"{$id}_to\" {$extra}{$attributes}>\n";
        if (isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])) {
            $output .= '<option disabled '.(isset($this->default_value) ? '' : 'selected').'>'.
                        $this->attributes['placeholder'].
                        '</option>';
        }
        foreach ($this->rightOptions as $key => $value) {
            $output .= $value->renderHTML($this);
        }
        $output .= "</select>\n</td></tr></table>";
        return $output;
    }
}
