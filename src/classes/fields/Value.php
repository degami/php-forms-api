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
 * the value field class
 * this field is not rendered as part of the form, but the value is passed on form submission
 */
class Value extends Field
{

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        $this->container_tag = '';
        $this->container_class = '';
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
     * @return string        an empty string
     */
    public function renderField(Form $form)
    {
        return '';
    }

    /**
     * validate function
     *
     * @return boolean this field is always valid
     */
    public function isValid()
    {
        return true;
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