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
use Degami\PHPFormsApi\Abstracts\Base\Field;

/**
 * the markup field class.
 * this is not a value
 */
class Markup extends Field
{
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
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string        the element value
     */
    public function renderField(Form $form)
    {
        $output = $this->value;
        return $output;
    }

    /**
     * validate function
     *
     * @return boolean this field is always valid
     */
    public function valid()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is not a value
     */
    public function isAValue()
    {
        return false;
    }
}
