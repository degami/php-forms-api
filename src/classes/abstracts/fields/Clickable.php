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

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\Abstracts\Base\Field;

/**
 * The "clickable" field element (a button or a submit )
 *
 * @abstract
 */
abstract class Clickable extends Action
{

    /**
     * "this element was clicked" flag
     *
     * @var boolean
     */
    protected $clicked = false;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param ?string $name    field name
     */
    public function __construct($options = [], ?string $name = null)
    {
        parent::__construct($options, $name);
        if (isset($options['value'])) {
            $this->value = $options['value'];
        }
        $this->clicked = false;
    }

    /**
     * Check if this button was clicked
     *
     * @return boolean if this element was clicked
     */
    public function getClicked(): bool
    {
        return $this->clicked;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value value to set
     */
    public function processValue($value)
    {
        parent::processValue($value);
        $this->clicked = true;
    }

    /**
     * reset this element
     */
    public function resetField() : Field
    {
        $this->clicked = false;
        return parent::resetField();
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue() : bool
    {
        return true;
    }
}
