<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Fields;

/**
 * the "clickable" field element (a button or a submit )
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
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        if (isset($options['value'])) {
            $this->value = $options['value'];
        }
        $this->clicked = false;
    }

    /**
     * check if this button was clicked
     *
     * @return boolean if this element was clicked
     */
    public function getClicked()
    {
        return $this->clicked;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value value to set
     */
    public function process($value)
    {
        parent::process($value);
        $this->clicked = true;
    }

    /**
     * reset this element
     */
    public function reset()
    {
        $this->clicked = false;
        parent::reset();
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}
