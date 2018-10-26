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
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Containers;

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
     * element subelements
     *
     * @var array
     */
    protected $partitions = [];

    /**
     * Get element partitions
     *
     * @return array partitions
     */
    public function &getPartitions()
    {
        return $this->partitions;
    }

    /**
     * Get number of defined partitions
     *
     * @return integer partitions number
     */
    public function numPartitions()
    {
        return count($this->partitions);
    }

    /**
     * add a new partition
     *
     * @param  string $title partition title
     * @return FieldsContainerMultiple
     */
    public function addPartition($title)
    {
        $this->partitions[] = ['title'=>$title,'fieldnames'=>[]];

        return $this;
    }

    /**
     * add field to element
     *
     * @param  string  $name             field name
     * @param  mixed   $field            field to add, can be an array or a field subclass
     * @param  integer $partitions_index index of partition to add field to
     * @return Field
     * @throws \Exception
     */
    public function addField($name, $field, $partitions_index = 0)
    {
        $field = $this->getFieldObj($name, $field);
        $field->setParent($this);

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
     * @param  string  $name             field name
     * @param  integer $partitions_index field partition
     * @return FieldsContainerMultiple
     */
    public function removeField($name, $partitions_index = 0)
    {
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
     * @param  integer $partitions_index partition index
     * @return array             partition fields array
     */
    public function &getPartitionFields($partitions_index)
    {
        $out = [];
        $fieldsnames = $this->partitions[$partitions_index]['fieldnames'];
        foreach ($fieldsnames as $name) {
            $out[$name] = $this->getField($name);
        }
        return $out;
    }

    /**
     * Set partition fields array
     *
     * @param  array   $fields          array of new fields to set for partition
     * @param  integer $partition_index partition index
     * @return FieldsContainerMultiple
     * @throws \Exception
     */
    public function setPartitionFields($fields, $partition_index = 0)
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
     * check if partition has errors
     *
     * @param  integer $partitions_index partition index
     * @param  Form    $form             form object
     * @return boolean           partition has errors
     */
    public function partitionHasErrors($partitions_index, Form $form)
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
    public function getPartitionIndex($field_name)
    {
        foreach ($this->partitions as $partitions_index => $partition) {
            if (in_array($field_name, $partition['fieldnames'])) {
                return $partitions_index;
            }
        }
        return -1;
    }
}
