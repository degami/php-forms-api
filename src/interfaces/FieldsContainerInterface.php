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
 * fields container interface
 */
interface FieldsContainerInterface
{

    /**
     * add field to form
     *
     * @param string $name  field name
     * @param mixed  $field field to add, can be an array or a field subclass
     */
    public function addField($name, $field);

    /**
     * remove field from form
     *
     * @param string $field field name
     */
    public function removeField($name);

    /**
     * on_add_return overload
     *
     * @return string 'this'
     */
    public function onAddReturn();
}
