<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                  FIELD INTERFACE                ####
   ######################################################### */

namespace Degami\PHPFormsApi\Interfaces;

use Degami\PHPFormsApi\Form;

/**
 * field interface
 */
interface FieldInterface
{

    /**
     * this function tells to the form if this element is a value that needs to be
     * included into parent values() function call result
     *
     * @return boolean include_me
     */
    public function isAValue(); // tells if component value is passed on the parent values() function call

    /**
     * pre-render hook
     *
     * @param Form $form form object
     */
    public function preRender(Form $form);

    /**
     * the function that actually renders the html field
     *
     * @param Form $form form object
     *
     * @return string|tag_element the field html
     */
    public function renderField(Form $form); // renders html

    /**
     * process / set field value
     *
     * @param mixed $value value to set
     */
    public function processValue($value);

    /**
     * check element validity
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid();

    /**
     * return form elements values into this element
     *
     * @return array form values
     */
    public function getValues();

    /**
     * which element should return the add_field() function
     *
     * @return string one of 'parent' or 'this'
     */
    public function onAddReturn();
}