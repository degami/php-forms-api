<?php
/**
 * PHP FORMS API
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

use Degami\PHPFormsApi\Abstracts\Base\Element;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Form;

/**
 * fields container interface
 */
interface FieldsContainerInterface extends FieldInterface
{

    /**
     * Add field to form
     *
     * @param string $name field name
     * @param mixed $field field to add, can be an array or a field subclass
     * @return FieldsContainer
     */
    public function addField(string $name, $field) : Element;

    /**
     * remove field from form
     *
     * @param string $name field name
     * @return FieldsContainer
     */
    public function removeField(string $name) : FieldsContainer;

    /**
     * on_add_return overload
     *
     * @return string 'this'
     */
    public function onAddReturn(): string;
}
