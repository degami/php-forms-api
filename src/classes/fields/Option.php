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

use Degami\PHPFormsApi\Abstracts\Base\Element;
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * The option element class
 */
class Option extends Element
{

    /**
     * option label
     *
     * @var string
     */
    protected $label;

    /**
     * option key
     *
     * @var string
     */
    protected $key;

    /**
     * Class constructor
     *
     * @param string $key     key
     * @param string $label   label
     * @param array  $options build options
     */
    public function __construct($key, $label, $options = [])
    {
        parent::__construct();

        $this->key = trim($key);
        $this->label = $label;

        foreach ($options as $key => $value) {
            $key = trim($key);
            if (property_exists(get_class($this), $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * render the option
     *
     * @param Select $form_field select field
     *
     * @return string        the option html
     */
    public function renderHTML(Select $form_field)
    {
        $this->no_translation = $form_field->no_translation;
        $field_value = $form_field->getValue();

        if (is_array($field_value) || $form_field->isMultiple() == true) {
            if (!is_array($field_value)) {
                $field_value = [$field_value];
            }
            if (in_array($this->key, array_values($field_value), true)) {
                $this->attributes['selected'] = 'selected';
            }
        } else {
            if ($this->key === $field_value) {
                $this->attributes['selected'] = 'selected';
            }
        }
        $tag = new TagElement(
            [
                'tag' => 'option',
                'type' => null,
                'value' => $this->key,
                'text' => $this->getText($this->label),
                'attributes' => $this->attributes + ['class' => false],
                'has_close' => true,
            ]
        );
        return $tag;
    }

    /**
     * Get the element key
     *
     * @return mixed the element key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the element key
     *
     * @param  mixed $key element key
     * @return Option
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the element label
     *
     * @return mixed the element label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the element label
     *
     * @param  mixed $label element label
     * @return Option
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
