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
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Base;

use Degami\PHPFormsApi\Exceptions\FormException;
use Degami\PHPFormsApi\Interfaces\FieldsContainerInterface;
use Degami\PHPFormsApi\Traits\Containers;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Fields\Checkbox;
use Degami\PHPFormsApi\Fields\Select;
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * a field that contains other fields class
 *
 * @abstract
 */
abstract class FieldsContainer extends Field implements FieldsContainerInterface
{
    use Containers;

    /**
     * Get the form fields by type
     *
     * @param mixed $field_types field types
     * @return array              fields in the element
     */
    public function getFieldsByType($field_types): array
    {
        if (!is_array($field_types)) {
            $field_types = [$field_types];
        }
        $out = [];

        foreach ($this->getFields() as $field) {
            if ($field instanceof FieldsContainer) {
                $out = array_merge($out, $field->getFieldsByType($field_types));
            } else {
                if ($field instanceof Field && in_array($field->getType(), $field_types)) {
                    $out[] = $field;
                }
            }
        }
        return $out;
    }

    /**
     * Get fields by type and name
     *
     * @param mixed $field_types field types
     * @param string $name field name
     * @return array              fields in the element matching the search criteria
     */
    public function getFieldsByTypeAndName($field_types, string $name): array
    {
        if (!is_array($field_types)) {
            $field_types = [$field_types];
        }
        $out = [];

        foreach ($this->getFields() as $field) {
            if ($field instanceof FieldsContainer) {
                $out = array_merge($out, $field->getFieldsByTypeAndName($field_types, $name));
            } else {
                if ($field instanceof Field
                    && in_array($field->getType(), $field_types)
                    && $field->getName() == $name
                ) {
                    $out[] = $field;
                }
            }
        }
        return $out;
    }

    /**
     * Get field by name
     *
     * @param string $field_name field name
     * @return Element|null subclass field object
     */
    public function getField(string $field_name): ?Element
    {
        return isset($this->fields[$field_name]) ? $this->fields[$field_name] : null;
    }

    /**
     * Set field
     *
     * @param string $field_name field name
     * @param Element $field subclass field object
     *
     * @return FieldsContainer
     */
    public function setField(string $field_name, $field): FieldsContainer
    {
        $field->setName($field_name);
        $this->fields[$field_name] = $field;
        return $this;
    }

    /**
     * Add field to form
     *
     * @param string $name field name
     * @param mixed $field field to add, can be an array or a field subclass
     * @return Element
     * @throws FormException
     */
    public function addField(string $name, $field): Element
    {
        /** @var Field $field */
        $field = $this->getFieldObj($name, $field);
        $field->setParent($this);

        $this->setField($name, $field);
        $this->insert_field_order[] = $name;

        if (!method_exists($field, 'onAddReturn')) {
            if ($this->isFieldContainer($field)) {
                return $field;
            }
            return $this;
        }
        if ($field->onAddReturn() == 'this') {
            return $field;
        }
        return $this;
    }

    /**
     * remove field from form
     *
     * @param string $name field name
     * @return FieldsContainer
     */
    public function removeField(string $name): FieldsContainer
    {
        unset($this->fields[$name]);
        if (($key = array_search($name, $this->insert_field_order)) !== false) {
            unset($this->insert_field_order[$key]);
        }
        return $this;
    }

    /**
     * Return form elements values into this element
     *
     * @return mixed form values
     */
    public function getValues()
    {
        $output = [];
        foreach ($this->getFields() as $name => $field) {
            /** @var Field $field */
            if ($field->isAValue() == true) {
                $output[$name] = $field->getValues();
                if (is_array($output[$name]) && empty($output[$name])) {
                    unset($output[$name]);
                }
            }
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $process_type preprocess type
     */
    public function preProcess($process_type = "preprocess")
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $field->preProcess($process_type);
        }
    }

    /**
     * Process (set) the fields value
     *
     * @param mixed $values value to set
     */
    public function processValue($values)
    {
        foreach ($this->getFields() as $name => $field) {
            /** @var Field $field */
            if ($field instanceof FieldsContainer) {
                $this->getField($name)->processValue($values);
            } elseif (($requestValue = static::traverseArray($values, $field->getName())) != null) {
                $this->getField($name)->processValue($requestValue);
            } elseif ($field instanceof Checkbox) {
                // no value on request[name] && field is a checkbox - process anyway with an empty value
                $this->getField($name)->processValue(null);
            } elseif ($field instanceof Select) {
                if ($field->isMultiple()) {
                    $this->getField($name)->processValue([]);
                } else {
                    $this->getField($name)->processValue(null);
                }
            } elseif ($field instanceof FieldMultivalues) {
                // no value on request[name] && field is a multivalue
                // (eg. checkboxes ?) - process anyway with an empty value
                $this->getField($name)->processValue([]);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        foreach ($this->getFields() as $name => $field) {
            if (is_object($field) && method_exists($field, 'preRender')) {
                $field->preRender($form);
            }
        }
        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid(): bool
    {
        $valid = true;
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            if (!$field->isValid()) {
                // not returning FALSE to let all the fields to be validated
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * renders form errors
     *
     * @return string errors as an html <li> list
     */
    public function showErrors(): string
    {
        $output = "";
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $output .= $field->showErrors();
        }
        return $output;
    }

    /**
     * resets the fields
     */
    public function resetField(): Field
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $field->resetField();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $request request array
     */
    public function alterRequest(array &$request)
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $field->alterRequest($request);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function afterValidate(Form $form)
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $field->afterValidate($form);
        }
    }

    /**
     * on_add_return overload
     *
     * @return string 'this'
     */
    public function onAddReturn(): string
    {
        return 'this';
    }
}
