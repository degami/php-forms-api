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

use Degami\PHPFormsApi\Form;

/**
 * a table field container
 */
class BulkTable extends TableContainer
{

    /** @var array available operations list */
    protected $operations = [];

    /**
     * Get defined operations
     *
     * @return array $operations array of callable
     */
    public function &getOperations()
    {
        return $this->operations;
    }

    /**
     * add operation to operations array
     *
     * @param  string $key       key
     * @param  string $label     label
     * @param  mixed  $operation operation
     * @return BulkTable
     */
    public function addOperation($key, $label, $operation)
    {
        $this->operations[$key] = ['key'=>$key,'label'=>$label,'op'=>$operation];

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  Form $form form object
     * @throws \Exception
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered) {
            return;
        }
        $id = $this->getHtmlId();
        $this->setTableHeader(array_merge(['&nbsp;'], $this->getTableHeader()));
        for ($i = 0; $i < $this->numRows(); $i++) {
            foreach ($this->getPartitionFields($i) as $key => $field) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
                $field->setName($this->getName()."[rows][$i][{$field->getName()}]");
            }
            $this->addField(
                $this->getName()."[rows][$i][row_enabled]",
                [
                    'type' => 'checkbox',
                    'value' => 0,
                    'default_value' => 1,
                    'attributes' => [
                        'class' => 'checkbox-row',
                    ],
                    'weight' => -100,
                ],
                $i
            );
        }

        $this->addJs(
            "\$('.btn.selAll','#{$id}_actions').click(function(evt){evt.preventDefault();
        \$('.checkbox-row','#{$id}').each(function(index,elem){ $(elem)[0].checked = true; }); });"
        );
        $this->addJs(
            "\$('.btn.deselAll','#{$id}_actions').click(function(evt){evt.preventDefault();
        \$('.checkbox-row','#{$id}').each(function(index,elem){ $(elem)[0].checked = false; }); });"
        );
        $this->addJs(
            "\$('.btn.inverSel','#{$id}_actions').click(function(evt){evt.preventDefault();
        \$('.checkbox-row','#{$id}').each(function(index,elem){ \$(elem)[0].checked = !\$(elem)[0].checked; }); });"
        );

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $prefix = "<div><select name=\"{$this->getName()}[op]\">";
        foreach ($this->getOperations() as $operation) {
            $prefix .= "<option value=\"{$operation['key']}\">{$operation['label']}</option>";
        }
        $prefix .= "</select></div>";

        $suffix="<div class=\"bulk_actions\" id=\"{$id}_actions\">";
        $suffix.="<a href=\"#\" class=\"btn selAll\">".$this->getText('Select all')."</a> - ";
        $suffix.="<a href=\"#\" class=\"btn deselAll\">".$this->getText('Deselect all')."</a> -";
        $suffix.="<a href=\"#\" class=\"btn inverSel\">".$this->getText('Invert selection')."</a>";
        $suffix.="</div>";

        $out = parent::renderField($form);
        return $prefix.$out.$suffix;
    }

    /**
     * {@inheritdocs}
     *
     * @param  mixed $values value to set
     * @return null
     */
    public function processValue($values)
    {
        foreach ($values[$this->getName()]['rows'] as $k => $row) {
            if (!isset($row['row_enabled']) || $row['row_enabled'] != 1) {
                unset($values[$this->getName()]['rows'][$k]);
            } else {
                unset($values[$this->getName()]['rows'][$k]['row_enabled']);
            }
        }

        $operation_key = $values[$this->getName()]['op'];
        $callable = $this->operations[ $operation_key ]['op'];
        foreach ($values[$this->getName()]['rows'] as $args) {
            call_user_func_array($callable, $args);
        }

        parent::processValue($values);
    }
}
