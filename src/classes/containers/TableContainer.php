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

namespace Degami\PHPFormsApi\Containers;

use Degami\PHPFormsApi\Exceptions\FormException;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Containers\FieldsContainerMultiple;
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Fields\Hidden;

/**
 * a table field container
 */
class TableContainer extends FieldsContainerMultiple
{

    /**
     * table header
     *
     * @var array
     */
    protected $table_header = [];

    /**
     * attributes for TRs or TDs
     *
     * @var array
     */
    protected $col_row_attributes = [];

    /**
     * table header attributes
     *
     * @var array
     */
    protected $thead_attributes = [];

    /**
     * table body attributes
     *
     * @var array
     */
    protected $tbody_attributes = [];


    /**
     * Set table header array
     *
     * @param  array $table_header table header elements array
     * @return self
     */
    public function setTableHeader(array $table_header): TableContainer
    {
        $this->table_header = $table_header;
        return $this;
    }

    /**
     * Get table header array
     *
     * @return array table header array
     */
    public function getTableHeader(): array
    {
        return $this->table_header;
    }

    /**
     * Set rows / cols attributes array
     *
     * @param  array $col_row_attributes attributes array
     * @return self
     */
    public function setColRowAttributes(array $col_row_attributes): TableContainer
    {
        $this->col_row_attributes = $col_row_attributes;
        return $this;
    }

    /**
     * Get rows / cols attributes array
     *
     * @return array attributes array
     */
    public function getColRowAttributes(): array
    {
        return $this->col_row_attributes;
    }

    /**
     * Add a new table row
     *
     * @return self
     */
    public function addRow(): TableContainer
    {
        $this->addPartition('table_row_'.$this->numPartitions());
        return $this;
    }

    /**
     * Return number of table rows
     *
     * @return int number of table rows
     */
    public function numRows(): int
    {
        return $this->numPartitions();
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

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string        the element html
     * @throws FormException
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $table_matrix = [];
        $rows = 0;

        foreach ($this->partitions as $trindex => $tr) {
            $table_matrix[$rows] = [];
            $cols = 0;
            foreach ($this->getPartitionFields($trindex) as $name => $field) {
                $table_matrix[$rows][$cols] = '';
                if (isset($this->col_row_attributes[$rows][$cols])) {
                    if (is_array($this->col_row_attributes[$rows][$cols])) {
                        $this->col_row_attributes[$rows][$cols] = $this->getAttributesString(
                            $this->col_row_attributes[$rows][$cols]
                        );
                    }
                    $table_matrix[$rows][$cols] = $this->col_row_attributes[$rows][$cols];
                }
                $cols++;
            }
            $rows++;
        }

        $tag = new TagElement([
            'tag' => 'table',
            'id' => $id,
            'attributes' => $this->attributes,
        ]);

        if (!empty($this->table_header)) {
            if (!is_array($this->table_header)) {
                $this->table_header = [$this->table_header];
            }

            $thead = new TagElement([
                'tag' => 'thead',
                'attributes' => $this->thead_attributes,
            ]);
            $tag->addChild($thead);

            foreach ($this->table_header as $th) {
                if (is_array($th)) {
                    $thead->addChild(new TagElement([
                        'tag' => 'th',
                        'text' => $this->getText($th['value']),
                        'attributes' => $th['attributes'],
                    ]));
                } else {
                    $thead->addChild(new TagElement([
                        'tag' => 'th',
                        'text' => $this->getText($th),
                    ]));
                }
            }
        }

        $tbody = new TagElement([
            'tag' => 'tbody',
            'attributes' => $this->tbody_attributes,
        ]);
        $tag->addChild($tbody);

        $rows = 0;
        foreach ($this->partitions as $trindex => $tr) {
            $insertorder = array_flip($this->insert_field_order[$trindex]);
            $weights = [];
            $order = [];
            foreach ($this->getPartitionFields($trindex) as $key => $elem) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
                $weights[$key]  = $elem->getWeight();
                $order[$key] = isset($insertorder[$key]) ? $insertorder[$key] : PHP_INT_MAX;
            }
            if (count($this->getPartitionFields($trindex)) > 0) {
                $partition_fields = $this->getPartitionFields($trindex);
                array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
                $this->setPartitionFields($partition_fields, $trindex);
            }

            $trow = new TagElement([
                'tag' => 'tr',
                'id' => $id.'-row-'.$trindex,
            ]);
            $tbody->addChild($trow);

            // hidden fields are always first
            usort($this->getPartitionFields($trindex), function($fieldA, $fieldB){
                if (is_object($fieldA) && is_a($fieldA, Hidden::class)) {
                    return -1;
                }
                if (is_object($fieldB) && is_a($fieldB, Hidden::class)) {
                    return 1;
                }
                return 0;
            });

            $cols = 0;
            foreach ($this->getPartitionFields($trindex) as $name => $field) {
                /**
                 * @var \Degami\PHPFormsApi\Abstracts\Base\Field $field
                 */
                $fieldhtml = $field->renderHTML($form);
                if (trim($fieldhtml) != '') {
                    $td_attributes = '';
                    if (!empty($table_matrix[$rows][$cols])) {
                        $td_attributes = $table_matrix[$rows][$cols];
                    }
                    $trow->addChild("<td{$td_attributes}>".$fieldhtml."</td>\n");
                }
                $cols++;
            }
            $rows++;
        }

        return $tag;
    }
}
