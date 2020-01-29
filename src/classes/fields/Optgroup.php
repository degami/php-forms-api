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
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * The optgroup element class
 */
class Optgroup extends Optionable
{
    /**
     * options array
     *
     * @var array
     */
    protected $options;

    /**
     * Class constructor
     *
     * @param string $label   label
     * @param array  $options options array
     */
    public function __construct($label, $options)
    {
        if (isset($options['options'])) {
            foreach ($options['options'] as $key => $value) {
                if ($value instanceof Option) {
                    $this->addOption($value);
                    $value->setParent($this);
                } elseif (is_scalar($key) && is_scalar($value)) {
                    $this->addOption(new Option($key, $value));
                }
            }
            unset($options['options']);
        }
        parent::__construct($label, $options);
    }

    /**
     * Check if key is present into element options array
     *
     * @param  mixed $needle element to find
     * @return boolean         TRUE if element is present
     */
    public function optionsHasKey($needle)
    {
        return FieldMultivalues::hasKey($needle, $this->options);
    }

    /**
     * Add option
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
        $tag = new TagElement([
            'tag' => 'optgroup',
            'type' => null,
            'id' => null,
            'attributes' => $this->attributes + [ 'label' => $this->label ],
            'value_needed' => false,
            'has_close' => true,
        ]);
        foreach ($this->options as $option) {
            $tag->addChild($option->renderHTML($form_field));
        }
        return $tag;
    }
}
