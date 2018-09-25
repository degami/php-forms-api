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
use Degami\PHPFormsApi\Accessories\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * the "autocomplete" text input field class
 */
class Datalist extends FieldMultivalues
{
    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options, $name)
    {
        if (isset($options['options'])) {
            foreach ($options['options'] as $k => $o) {
                if ($o instanceof Option) {
                    $o->setParent($this);
                    $this->options[] = $o;
                } else {
                    $option = new Option($o, $o);
                    $option->setParent($this);
                    $this->options[] = $option;
                }
            }
            unset($options['options']);
        }

        if (isset($options['default_value'])) {
            if (is_array($options['default_value'])) {
                $options['default_value'] = reset($options['default_value']);
            }
            $options['default_value'] = "".$options['default_value'];
        }

        parent::__construct($options, $name);
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

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }
        if (is_array($this->value)) {
            $this->value = '';
        }

        $output = "";
        $tag = new TagElement(
            [
                'tag' => 'input',
                'type' => 'text',
                'id' => $id,
                'name' => $this->name,
                'value' => htmlspecialchars($this->value),
                'attributes' => $this->attributes + ['size' => $this->size, 'list' => $this->name."-data"],
            ]
        );
        $output .= $tag->renderTag();

        $tag = new TagElement(
            [
                'tag' => 'datalist',
                'type' => null,
                'id' => $this->name.'-data',
                'value_needed' => false,
                'has_close' => true,
            ]
        );
        foreach ($this->options as $key => $opt) {
            /** @var \Degami\PHPFormsApi\Fields\Option $opt */
            $tag->addChild(
                new TagElement(
                    [
                        'tag' => 'option',
                        'type' => null,
                        'value' => $opt->getKey(),
                        'text' => $this->getText($opt->getLabel()),
                        'has_close' => true,
                    ]
                )
            );
        }
        $output .= $tag->renderTag();

        return $output;
    }
}
