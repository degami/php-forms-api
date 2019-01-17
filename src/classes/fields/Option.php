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
use Degami\PHPFormsApi\Abstracts\Fields\Optionable;
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * The option element class
 */
class Option extends Optionable
{
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
        $this->setKey(trim($key));
        parent::__construct($label, $options);
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
}
