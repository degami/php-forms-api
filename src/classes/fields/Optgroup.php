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

use Degami\PHPFormsApi\Abstracts\Base\Element;
use Degami\PHPFormsApi\Accessories\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * the optgroup element class
 */
class Optgroup extends Element
{

    /**
     * options array
     *
     * @var array
     */
    protected $options;

    /**
     * element label
     *
     * @var string
     */
    protected $label;

    /**
     * class constructor
     *
     * @param string $label   label
     * @param array  $options options array
     */
    public function __construct($label, $options)
    {
        $this->label = $label;

        if (isset($options['options'])) {
            foreach ($options['options'] as $key => $value) {
                if ($value instanceof Option) {
                    $this->addOption($value);
                    $value->setParent($this);
                } else {
                    $this->addOption(new Option($key, $value));
                }
            }
            unset($options['options']);
        }

        foreach ($options as $key => $value) {
            $key = trim($key);
            if (property_exists(get_class($this), $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * check if key is present into element options array
     *
     * @param  mixed $needle element to find
     * @return boolean         TRUE if element is present
     */
    public function optionsHasKey($needle)
    {
        return FieldMultivalues::hasKey($needle, $this->options);
    }

    /**
     * add option
     *
     * @param Option $option option to add
     */
    public function addOption(Option $option)
    {
        $option->setParent($this);
        $this->options[] = $option;
    }

    /**
     * render the optgroup
     *
     * @param Select $form_field select field
     *
     * @return string        the optgroup html
     */
    public function renderHTML(Select $form_field)
    {
        $this->no_translation = $form_field->no_translation;
        $tag = new TagElement(
            [
                'tag' => 'optgroup',
                'type' => null,
                'id' => null,
                'attributes' => $this->attributes + [ 'label' => $this->label ],
                'value_needed' => false,
                'has_close' => true,
            ]
        );
        foreach ($this->options as $option) {
            $tag->addChild($option->renderHTML($form_field));
        }
        return $tag;
    }
}