<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Base;

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
     * get the form fields by type
     *
     * @param  array $field_types field types
     * @return array              fields in the element
     */
    public function getFieldsByType($field_types)
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
     * get the step fields by type and name
     *
     * @param  array  $field_types field types
     * @param  string $name        field name
     * @return array              fields in the element matching the search criteria
     */
    public function getFieldsByTypeAndName($field_types, $name)
    {
        if (!is_array($field_types)) {
            $field_types = [$field_types];
        }
        $out = [];

        foreach ($this->getFields() as $field) {
            if ($field instanceof FieldsContainer) {
                $out = array_merge($out, $field->getFieldsByTypeAndName($field_types, $name));
            } else {
                if ($field instanceof Field &&
                    in_array($field->getType(), $field_types) &&
                    $field->getName() == $name) {
                    $out[] = $field;
                }
            }
        }
        return $out;
    }

    /**
     * get field by name
     *
     * @param  string $field_name field name
     * @return Element subclass field object
     */
    public function getField($field_name)
    {
        return isset($this->fields[$field_name]) ? $this->fields[$field_name] : null;
    }

    /**
     * add field to form
     *
     * @param string $name  field name
     * @param mixed  $field field to add, can be an array or a field subclass
     * @throws \Exception
     * @return Field
     */
    public function addField($name, $field)
    {
        $field = $this->getFieldObj($name, $field);
        $field->setParent($this);

        $this->fields[$name] = $field;
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
    public function removeField($name)
    {
        unset($this->fields[$name]);
        if (($key = array_search($name, $this->insert_field_order)) !== false) {
            unset($this->insert_field_order[$key]);
        }
        return $this;
    }

    /**
     * return form elements values into this element
     *
     * @return array form values
     */
    public function values()
    {
        $output = [];
        foreach ($this->getFields() as $name => $field) {
            /** @var Field $field */
            if ($field->isAValue() == true) {
                $output[$name] = $field->values();
                if (is_array($output[$name]) && empty($output[$name])) {
                    unset($output[$name]);
                }
            }
        }
        return $output;
    }

    /**
     * preprocess hook
     *
     * @param string $process_type preprocess type
     */
    public function preprocess($process_type = "preprocess")
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $field->preprocess($process_type);
        }
    }

    /**
     * process (set) the fields value
     *
     * @param mixed $values value to set
     */
    public function process($values)
    {
        foreach ($this->getFields() as $name => $field) {
            /** @var Field $field */
            if ($field instanceof FieldsContainer) {
                $this->getField($name)->process($values);
            } elseif (preg_match_all('/(.*?)(\[(.*?)\])+/i', $name, $matches, PREG_SET_ORDER)) {
                if (isset($values[ $matches[0][1] ])) {
                    $value = $values[ $matches[0][1] ];
                    foreach ($matches as $match) {
                        if (isset($value[ $match[3] ])) {
                            $value = $value[ $match[3] ];
                        }
                    }
                }
                $field->process($value);
            } elseif (isset($values[$name])) {
                $this->getField($name)->process($values[$name]);
            } elseif ($field instanceof Checkbox) {
                // no value on request[name] && field is a checkbox - process anyway with an empty value
                $this->getField($name)->process(null);
            } elseif ($field instanceof Select) {
                if ($field->isMultiple()) {
                    $this->getField($name)->process([]);
                } else {
                    $this->getField($name)->process(null);
                }
            } elseif ($field instanceof FieldMultivalues) {
                // no value on request[name] && field is a multivalue
                // (eg. checkboxes ?) - process anyway with an empty value
                $this->getField($name)->process([]);
            }
        }
    }

    /**
     * pre_render hook
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
     * validate hook
     *
     * @return boolean TRUE if element is valid
     */
    public function valid()
    {
        $valid = true;
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            if (!$field->valid()) {
                // not returnig FALSE to let all the fields to be validated
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
    public function showErrors()
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
    public function reset()
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $field->reset();
        }
    }

    /**
     * is_a_value hook
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }

    /**
     * alter_request hook
     *
     * @param array $request request array
     */
    public function alterRequest(&$request)
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $field->alterRequest($request);
        }
    }

    /**
     * after_validate hook
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
    public function onAddReturn()
    {
        return 'this';
    }
}
