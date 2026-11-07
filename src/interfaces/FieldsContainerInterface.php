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
     * @param Element|array $field field to add, can be an array or a field subclass
     * @param mixed ...$args additional arguments
     * @return FieldsContainer
     */
    public function addField(string $name, Element|array $field, mixed ...$args) : Element;

    /**
     * remove field from form
     *
     * @param string $name field name
     * @param mixed ...$args additional arguments - remove field from partition if partition index is passed
     * @return Element
     */
    public function removeField(string $name, mixed ...$args) : Element;

    /**
     * on_add_return overload
     *
     * @return string 'this'
     */
    public function onAddReturn(): string;
}
