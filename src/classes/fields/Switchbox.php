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
 * the switch selection field class
 */
class Switchbox extends Radios
{

    /**
     * @var string $no_label
     */
    protected $no_value;

    /**
     * @var string $no_label
     */
    protected $no_label;

    /**
     * @var string $yes_label
     */
    protected $yes_value;

    /**
     * @var string $yes_label
     */
    protected $yes_label;

    public function __construct(array $options = [], $name = null)
    {
        $this->no_value = 0;
        $this->no_label = $this->getText('No');
        $this->yes_value = 1;
        $this->yes_label = $this->getText('Yes');

        // labels and values can be overwritten
        parent::__construct($options, $name);

        // "options" is overwritten
        $this->options = [
            $this->no_value => $this->no_label,
            $this->yes_value => $this->yes_label,
        ];
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


        foreach ($this->options as $key => $value) {
            $this->addJs("\$('#{$id}-{$key}','#{$form->getId()}')
                .click(function(evt){
                  \$(this).closest('label').addClass('ui-state-active');
                  \$('#{$id} input[type=\"radio\"]').not(this).closest('label').removeClass('ui-state-active');
                 });");
        }

        $this->addCss("#{$id} .label-switch{ 
                                text-align: center; 
                                display: inline-block; 
                                width: 50%; 
                                padding-top: 10px; 
                                padding-bottom: 10px; 
                                box-sizing: border-box;
                            }");
        $this->addJs("\$('#{$id}','#{$form->getId()}').find('input[type=\"radio\"]:checked')
                         .closest('label').addClass('ui-state-active');");
        //$this->add_css("#{$id} .label-switch input{ display: none; }");
        $this->addJs("\$('#{$id} input[type=\"radio\"]','#{$form->getId()}').hide();");
        parent::preRender($form);
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
        $output = "<div class=\"options ui-widget-content ui-corner-all\" id=\"{$id}\">";
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        foreach ($this->options as $key => $value) {
            $attributes = $this->getAttributes();
            if (is_array($value) && isset($value['attributes'])) {
                $attributes = $this->getAttributesString($value['attributes'], ['type','name','id','value']);
            }
            if (is_array($value)) {
                $value = $value['value'];
            }

            $checked = ($this->value == $key) ? ' checked="checked"' : '';
            $output .= "<label class=\"label-switch ui-widget ui-state-default\" 
                          id=\"{$id}-{$key}-button\" for=\"{$id}-{$key}\">
                        <input type=\"radio\" id=\"{$id}-{$key}\" name=\"{$this->name}\" 
                          value=\"{$key}\" {$checked} {$attributes} />{$value}</label>";
        }
        $output .= "</div>";
        return $output;
    }
}
