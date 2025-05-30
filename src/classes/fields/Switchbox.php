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

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Form;
use Degami\Basics\Html\TagElement;

/**
 * The switch selection field class
 */
class Switchbox extends Radios
{

    /** @var mixed "no" value */
    protected $no_value;

    /** @var string "no" label */
    protected $no_label;

    /** @var mixed "yes" value */
    protected $yes_value;

    /** @var string "yes" label */
    protected $yes_label;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param ?string $name    field name
     */
    public function __construct(array $options = [], ?string $name = null)
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
            $this->addJs(
                "\$('#{$id}-{$key}','#{$form->getId()}')
                .click(function(evt){
                  \$(this).closest('label').addClass('ui-state-active');
                  \$('#{$id} input[type=\"radio\"]').not(this).closest('label').removeClass('ui-state-active');
                 });"
            );
        }

        $this->addCss(
            "#{$id} .label-switch{
                                text-align: center;
                                display: inline-block;
                                width: 50%;
                                padding-top: 10px;
                                padding-bottom: 10px;
                                box-sizing: border-box;
                            }"
        );
        $this->addJs(
            "\$('#{$id}','#{$form->getId()}').find('input[type=\"radio\"]:checked')
                         .closest('label').addClass('ui-state-active');"
        );
        //$this->add_css("#{$id} .label-switch input{ display: none; }");
        $this->addJs("\$('#{$id} input[type=\"radio\"]','#{$form->getId()}').hide();");
        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string|BaseElement        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();
        $tag = new TagElement([
            'tag' => 'div',
            'id' => $id,
            'attributes' => ['class' => 'options ui-widget-content ui-corner-all'],
        ]);

        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        foreach ($this->options as $key => $value) {
            $attributes = $this->attributes;
            if (is_array($value) && isset($value['attributes'])) {
                $attributes = $value['attributes'];
            }
            if (is_array($value)) {
                $value = $value['value'];
            }

            $tag_label = new TagElement([
                'tag' => 'label',
                'attributes' => [
                  'id' => "{$id}-{$key}-button",
                  'for' => "{$id}-{$key}",
                  'class' => "label-switch ui-widget ui-state-default"
                ],
            ]);
            $tag_label->addChild(new TagElement([
                'tag' => 'input',
                'type' => 'radio',
                'id' => "{$id}-{$key}",
                'name' => "{$this->name}",
                'value' => $key,
                'attributes' => array_merge(
                    $attributes,
                    (($this->getValues() == $key) ? ['checked' => 'checked'] : [])
                ),
                'text' => $this->getText($value),
            ]));
            $tag->addChild($tag_label);
        }
        return $tag;
    }
}
