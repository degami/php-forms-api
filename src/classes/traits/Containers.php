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

use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Abstracts\Fields\ComposedField;
use \Exception;

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
     * element fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Get the fields array by reference
     *
     * @return array        the array of field elements
     */
    public function &getFields()
    {
        return $this->fields;
    }

    /**
     * Get parent namespace
     *
     * @return string  parent namespace
     */
    private function parentNameSpace()
    {
        $namespaceParts = explode('\\', __NAMESPACE__);
        return implode("\\", array_slice($namespaceParts, 0, -1));
    }

    /**
     * Returns a field object instance
     *
     * @param string $name  field name
     * @param mixed  $field field to add, can be an array or a field subclass
     *
     * @return Field instance
     * @throws \Exception
     */
    public function getFieldObj($name, $field)
    {
        if (is_array($field)) {
            $field_type = $this->parentNameSpace() .
                            "\\Fields\\" .
                            (isset($field['type']) ?
                                $this->snakeCaseToPascalCase($field['type']) :
                              'textfield'
                            );
            $container_type = $this->parentNameSpace() .
                                "\\Containers\\" .
                                (isset($field['type']) ?
                                    $this->snakeCaseToPascalCase($field['type']) :
                                  'textfield'
                                );
            $root_type = $this->parentNameSpace() .
                            "\\" .
                            (isset($field['type']) ?
                                $this->snakeCaseToPascalCase($field['type']) :
                              'textfield'
                            );
            if (!class_exists($field_type) && !class_exists($container_type) && !class_exists($root_type)) {
                throw new Exception(
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
            throw new Exception("Error adding field. Array or field subclass expected, ".gettype($field)." given", 1);
        }

        return $field;
    }

    /**
     * check if field is a field container
     *
     * @param Field $field field instance
     *
     * @return boolean true if field is a field container
     */
    public function isFieldContainer(Field $field)
    {
        return $field instanceof FieldsContainer && !($field instanceof ComposedField);
    }
}
