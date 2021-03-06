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

use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;

/**
 * an abstract sortable field container
 *
 * @abstract
 */
abstract class SortableContainer extends FieldsContainerMultiple
{

    /**
     * sort handle position (left/right)
     *
     * @var string
     */
    protected $handle_position = 'left';

    /**
     * deltas array ( used for sorting )
     *
     * @var array
     */
    protected $deltas = [];

    /**
     * Get handle position (left/right)
     *
     * @return string handle position
     */
    public function getHandlePosition(): string
    {
        return $this->handle_position;
    }

    /**
     * Return form elements values into this element
     *
     * @return mixed form values
     */
    public function getValues()
    {
        $output = [];

        $fields_with_delta = $this->getFieldsWithDelta();
        usort($fields_with_delta, [__CLASS__, 'orderbyDelta']);

        foreach ($fields_with_delta as $name => $info) {
            $field = $info['field'];
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
     * Process (set) the fields value
     *
     * @param mixed $values value to set
     */
    public function processValue($values)
    {
        foreach ($this->getFields() as $name => $field) {
            /** @var Field $field */
            $partitionindex = $this->getPartitionIndex($field->getName());

            if ($field instanceof FieldsContainer) {
                $this->getField($name)->processValue($values);
            } elseif (($requestValue = static::traverseArray($values, $field->getName())) != null) {
                $this->getField($name)->processValue($requestValue);
            }

            $this->deltas[$name] = isset($values[$this->getHtmlId().'-delta-'.$partitionindex]) ?
                                   $values[$this->getHtmlId().'-delta-'.$partitionindex] :
                                   0;
        }
    }

    /**
     * Get an array of fields with the relative delta (ordering) information
     *
     * @return array fields with delta
     */
    private function getFieldsWithDelta(): array
    {
        $out = [];
        foreach ($this->getFields() as $key => $field) {
            $out[$key]=['field'=> $field,'delta'=>$this->deltas[$key]];
        }
        return $out;
    }

    /**
     * order elements by delta property
     *
     * @param  array $a first element
     * @param  array $b second element
     * @return integer  order
     */
    private static function orderbyDelta($a, $b): int
    {
        if ($a['delta']==$b['delta']) {
            return 0;
        }
        return ($a['delta']>$b['delta']) ? 1:-1;
    }
}
