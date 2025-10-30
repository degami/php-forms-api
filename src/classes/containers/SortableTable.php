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

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Containers\SortableContainer;
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Fields\Hidden;

/**
 * a sortable table rows field container
 */
class SortableTable extends SortableContainer
{

    /**
     * table header
     *
     * @var array
     */
    protected $table_header = [];

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
        $id = $this->getHtmlId();
        $this->addJs(
            "
      \$('#{$id} tbody','#{$form->getId()}').sortable({
        helper: function(e, ui) {
          ui.children().each(function() {
            \$(this).width($(this).width());
          });
          return ui;
        },
        placeholder: \"ui-state-highlight\",
        stop: function( event, ui ) {
          \$(this).find('input[type=hidden][name*=\"sortable-delta-\"]').each(function(index,elem){
            \$(elem).val(index);
          });
        }
      });"
        );

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     * @return string|BaseElement        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $handle_position = trim(strtolower($this->getHandlePosition()));

        $tag = new TagElement([
            'tag' => 'table',
            'id' => $id,
            'attributes' => $this->attributes,
        ]);

        if (!empty($this->table_header)) {
            if (!is_array($this->table_header)) {
                $this->table_header = [$this->table_header];
            }

            $thead = new TagElement(['tag' => 'thead']);
            $tag->addChild($thead);

            if ($handle_position != 'right') {
                $thead->addChild(new TagElement([
                    'tag' => 'th',
                    'text' => '&nbsp;',
                ]));
            }
            foreach ($this->table_header as $th) {
                $thead->addChild(new TagElement([
                    'tag' => 'th',
                    'text' => $this->getText($th),
                ]));
            }
            if ($handle_position == 'right') {
                $thead->addChild(new TagElement([
                    'tag' => 'th', 'text' => '&nbsp;',
                ]));
            }
        }

        $tbody = new TagElement(['tag' => 'tbody']);
        $tag->addChild($tbody);

        foreach ($this->partitions as $trindex => $tr) {
            $insertorder = array_flip($this->insert_field_order[$trindex]);
            $weights = [];
            $order = [];

            $partition_fields = $this->getPartitionFields($trindex);

            foreach ($partition_fields as $key => $elem) {
                /**
                 * @var Field $elem
                 */
                $weights[$key]  = $elem->getWeight();
                $order[$key] = isset($insertorder[$key]) ? $insertorder[$key] : PHP_INT_MAX;
            }
            if (count($partition_fields) > 0) {
                array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
            }

            $trow = new TagElement([
                'tag' => 'tr',
                'id' => $id.'-sortable-'.$trindex,
                'attributes' => [ 'class' => 'tab-inner ui-state-default'],
            ]);
            $tbody->addChild($trow);

            if ($handle_position != 'right') {
                $td = new TagElement([
                    'tag' => 'td',
                    'attributes' => [  'width' => 16, 'style' => 'width: 16px;'],
                    'children' => [
                        new TagElement([
                            'tag' => 'span',
                            'attributes' => [
                                'class' => 'ui-icon ui-icon-arrowthick-2-n-s',
                                'style' => 'display: inline-block;'
                            ],
                        ])
                    ],
                ]);
                $trow->addChild($td);
            }

            // hidden fields are always first
            usort($partition_fields, function($fieldA, $fieldB){
                if (is_object($fieldA) && is_a($fieldA, Hidden::class)) {
                    return -1;
                }
                if (is_object($fieldB) && is_a($fieldB, Hidden::class)) {
                    return 1;
                }
                return 0;
            });

            foreach ($partition_fields as $name => $field) {
                /**
                 * @var Field $field
                 */
                $fieldhtml = $field->renderHTML($form);
                if (trim($fieldhtml) != '') {
                    $trow->addChild(new TagElement([
                        'tag' => 'td',
                        'children' => [ $fieldhtml ],
                    ]));
                }
            }

            $trow->addChild(new TagElement([
                'tag' => 'input',
                'type' => 'hidden',
                'name' => $id.'-delta-'.$trindex,
                'value' => $trindex,
                'has_close' => false,
            ]));
            if ($handle_position == 'right') {
                $td = new TagElement([
                    'tag' => 'td',
                    'attributes' => [  'width' => 16, 'style' => 'width: 16px;'],
                    'children' => [
                        new TagElement([
                            'tag' => 'span',
                            'attributes' => [
                                'class' => 'ui-icon ui-icon-arrowthick-2-n-s',
                                'style' => 'display: inline-block;'
                            ],
                        ])
                    ],
                ]);
                $trow->addChild($td);
            }
        }

        return $tag;
    }
}
