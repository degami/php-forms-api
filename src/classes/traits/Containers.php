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
   ####                     TRAITS                      ####
   ######################################################### */

namespace Degami\PHPFormsApi\Traits;

use Degami\PHPFormsApi\Abstracts\Base\Element;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Abstracts\Fields\ComposedField;
use Degami\PHPFormsApi\Exceptions\FormException;

/**
 * containers specific functions
 */
trait Containers
{

    /**
     * keeps fields insert order
     *
     * @var array
     */
    protected $insert_field_order = [];

    /**
     * Element fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Get the fields array by reference
     *
     * @return array        the array of field elements
     */
    public function &getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get parent namespace
     *
     * @return string  parent namespace
     */
    private function parentNameSpace(): string
    {
        $namespaceParts = explode('\\', __NAMESPACE__);
        return implode("\\", array_slice($namespaceParts, 0, -1));
    }

    /**
     * Returns a field object instance
     *
     * @param string $name field name
     * @param mixed $field field to add, can be an array or a field subclass
     * @return Field instance
     * @throws FormException
     */
    public function getFieldObj(string $name, $field): Field
    {
        if (is_array($field)) {
            $parentNS = $this->parentNameSpace();
            $element_type = isset($field['type']) ?
                                $this->snakeCaseToPascalCase($field['type']) :
                              'textfield';

            $field_type = $parentNS . "\\Fields\\" . $element_type;
            $container_type = $parentNS . "\\Containers\\" . $element_type;
            $root_type = $parentNS . "\\" . $element_type;

            if (!class_exists($field_type) && !class_exists($container_type) && !class_exists($root_type)) {
                throw new FormException(
                    "Error adding field. Class \"$field_type\", \"$container_type\", \"$root_type\" not found",
                    1
                );
            }

            if (class_exists($field_type)) {
                $type = $field_type;
            } elseif (class_exists($container_type)) {
                $type = $container_type;
            } else {
                $type = $root_type;
            }

            if (is_subclass_of($type, 'Degami\PHPFormsApi\Abstracts\Base\Field')) {
                /** @var Field $type */
                $field = $type::getInstance($field, $name);
            } else {
                $field = new $type($field, $name);
            }
        } elseif ($field instanceof Field) {
            $field->setName($name);
        } else {
            throw new FormException("Error adding field. Array or field subclass expected, ".gettype($field)." given", 1);
        }

        return $field;
    }

    /**
     * Check if field is a field container
     *
     * @param Field $field field instance
     * @return boolean true if field is a field container
     */
    public function isFieldContainer(Field $field): bool
    {
        return $field instanceof FieldsContainer && !($field instanceof ComposedField);
    }

    /**
     * add markup helper
     *
     * @param string $markup markup to add
     * @param array $options
     * @return Element
     */
    public function addMarkup(string $markup, array $options = []): Element
    {
        static $lastMarkupIndex = 0;
        return $this->addField('_markup_'.time().'_'.$lastMarkupIndex++, [
            'type' => 'markup',
            'container_tag' => null,
            'value' => $markup,
        ] + $options);
    }
}
