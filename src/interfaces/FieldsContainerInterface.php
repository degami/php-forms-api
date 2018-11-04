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
     * Add field to form
     *
     * @param string $name  field name
     * @param mixed  $field field to add, can be an array or a field subclass
     */
    public function addField($name, $field);

    /**
     * remove field from form
     *
     * @param string $name field name
     */
    public function removeField($name);

    /**
     * on_add_return overload
     *
     * @return string 'this'
     */
    public function onAddReturn();
}
