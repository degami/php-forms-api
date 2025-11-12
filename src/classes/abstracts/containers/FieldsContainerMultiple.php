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

namespace Degami\PHPFormsApi\Abstracts\Containers;

use Degami\PHPFormsApi\Abstracts\Base\Element;
use Degami\PHPFormsApi\Exceptions\FormException;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Traits\Containers;

/**
 * a field container subdivided in groups
 *
 * @abstract
 */
abstract class FieldsContainerMultiple extends FieldsContainer
{
    use Containers;

    /**
     * Element sub-elements
     *
     * @var array
     */
    protected $partitions = [];

    /**
     * Get element partitions
     *
     * @return array partitions
     */
    public function &getPartitions(): array
    {
        return $this->partitions;
    }

    /**
     * Get number of defined partitions
     *
     * @return integer partitions number
     */
    public function numPartitions(): int
    {
        return count($this->partitions);
    }

    /**
     * Add a new partition
     *
     * @param  string $title partition title
     * @return self
     */
    public function addPartition($title): FieldsContainerMultiple
    {
        $this->partitions[] = ['title'=>$title,'fieldnames'=>[]];

        return $this;
    }

    /**
     * Add field to element
     *
     * @param string $name field name
     * @param mixed $field field to add, can be an array or a field subclass
     * @return Element
     * @throws FormException
     */
    public function addField(string $name, $field): Element
    {
        $field = $this->getFieldObj($name, $field);
        $field->setParent($this);

        $partitions_index  = null;
        if (func_num_args() == 3) {
            $partitions_index = func_get_arg(2);
        }
        if (!is_numeric($partitions_index) || !array_key_exists($partitions_index, $this->partitions)) {
            $partitions_index = $this->numPartitions() - 1;
        }

        $this->fields[$name] = $field;
        $this->insert_field_order[$partitions_index][] = $name;
        if (!isset($this->partitions[$partitions_index])) {
            $this->partitions[$partitions_index] = ['title'=>'','fieldnames'=>[]];
        }
        $this->partitions[$partitions_index]['fieldnames'][] = $name;

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
     * @return FieldsContainerMultiple
     */
    public function removeField(string $name) : FieldsContainer
    {
        $partitions_index = func_get_arg(1);
        if (!is_numeric($partitions_index)) {
            $partitions_index = $this->getPartitionIndex($name);
        }

        unset($this->fields[$name]);
        if (isset($this->insert_field_order[$partitions_index])
            && ($key = array_search($name, $this->insert_field_order[$partitions_index])) !== false
        ) {
            unset($this->insert_field_order[$partitions_index][$key]);
        }
        if (isset($this->partitions[$partitions_index]['fieldnames'])
            && ($key = array_search($name, $this->partitions[$partitions_index]['fieldnames'])) !== false
        ) {
            unset($this->partitions[$partitions_index]['fieldnames'][$key]);
        }
        return $this;
    }

    /**
     * Get partition fields array
     *
     * @param  int $partitions_index partition index
     * @return array             partition fields array
     */
    public function &getPartitionFields(int $partitions_index): array
    {
        $out = [];
        $field_names = $this->partitions[$partitions_index]['fieldnames'];
        foreach ($field_names as $name) {
            $out[$name] = $this->getField($name);
        }
        return $out;
    }

    /**
     * Set partition fields array
     *
     * @param  array   $fields          array of new fields to set for partition
     * @param  integer $partition_index partition index
     * @return self
     * @throws FormException
     */
    public function setPartitionFields(array $fields, $partition_index = 0): FieldsContainerMultiple
    {
        $fields_names = $this->partitions[$partition_index]['fieldnames'];
        foreach ($fields_names as $name) {
            $this->removeField($name, $partition_index);
        }
        unset($this->partitions[$partition_index]['fieldnames']);
        $this->partitions[$partition_index]['fieldnames'] = [];
        foreach ($fields as $name => $field) {
            if ($field instanceof Field) {
                $name = $field->getName();
            }
            $field = $this->getFieldObj($name, $field);
            $this->addField($field->getName(), $field, $partition_index);
        }
        return $this;
    }

    /**
     * Check if partition has errors
     *
     * @param  int $partitions_index partition index
     * @param  Form    $form             form object
     * @return boolean           partition has errors
     */
    public function partitionHasErrors(int $partitions_index, Form $form): bool
    {
        if (!$form->isProcessed()) {
            return false;
        }
        $out = false;
        foreach ($this->getPartitionFields($partitions_index) as $name => $field) {
            if ($out == true) {
                continue;
            }
            $out |= !$field->isValid();
        }
        return $out;
    }

    /**
     * Get partition index containint specified field name
     *
     * @param  string $field_name field name
     * @return integer            partition index, -1 on failure
     */
    public function getPartitionIndex($field_name): int
    {
        foreach ($this->partitions as $partitions_index => $partition) {
            if (in_array($field_name, $partition['fieldnames'])) {
                return $partitions_index;
            }
        }
        return -1;
    }
}
