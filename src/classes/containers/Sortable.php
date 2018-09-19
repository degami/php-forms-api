<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Containers;

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Containers\SortableContainer;
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * a sortable field container
 */
class Sortable extends SortableContainer
{

    /**
     * @inheritdoc
     *
     * @param string $name  field name
     * @param mixed  $field field to add, can be an array or a field subclass
     * @throws \Exception
     */
    public function addField($name, $field, $_p = null)
    {
        //force every field to have its own tab.
        $this->deltas[$name] = count($this->getFields());
        return parent::addField($name, $field, $this->deltas[$name]);
    }

    /**
     * @inheritdoc
     *
     * @param string $name field name
     * @param mixed $_p UNUSED
     */
    public function removeField($name, $_p = null)
    {
        parent::removeField($name, $this->deltas['name']);
        unset($this->deltas[$name]);
        return $this;
    }

    /**
     * @inheritdoc
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
            "\$('#{$id}','#{$form->getId()}').sortable({
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
     * @inheritdoc
     *
     * @param Form $form form object
     *
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $handle_position = trim(strtolower($this->getHandlePosition()));

        $tag = new TagElement(
            [
                'tag' => 'div',
                'id' => $id,
                'attributes' => $this->attributes,
            ]
        );

        foreach ($this->partitions as $partitionindex => $tab) {
            $insertorder = array_flip($this->insert_field_order[$partitionindex]);
            $weights = [];
            $order = [];

            $partition_fields = $this->getPartitionFields($partitionindex);
            foreach ($partition_fields as $key => $elem) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
                $weights[$key]  = $elem->getWeight();
                $order[$key] = $insertorder[$key];
            }
            if (count($partition_fields) > 0) {
                array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
            }

            $inner = new TagElement(
                [
                    'tag' => 'div',
                    'id' => $id.'-sortable-'.$partitionindex,
                    'attributes' => ['class' => 'tab-inner ui-state-default'],
                ]
            );

            $tag->addChild($inner);

            if ($handle_position != 'right') {
                $inner->addChild(
                    new TagElement(
                        [
                            'tag' => 'span',
                            'attributes' => [
                                'class' => 'ui-icon ui-icon-arrowthick-2-n-s',
                                'style' => 'display: inline-block;'
                            ],
                        ]
                    )
                );
            }

            $inner_inline = new TagElement(
                [
                    'tag' => 'div',
                    'attributes' => ['style' => 'display: inline-block;'],
                ]
            );
            $inner->addChild($inner_inline);

            foreach ($partition_fields as $name => $field) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
                $inner_inline->addChild($field->render($form));
            }
            $inner_inline->addChild(
                new TagElement(
                    [
                        'tag' => 'input',
                        'type' => 'hidden',
                        'name' => $id.'-delta-'.$partitionindex,
                        'value' => $partitionindex,
                        'has_close' => false,
                    ]
                )
            );
            if ($handle_position == 'right') {
                $inner_inline->addChild(
                    new TagElement(
                        [
                            'tag' => 'span',
                            'attributes' => [
                                'class' => 'ui-icon ui-icon-arrowthick-2-n-s',
                                'style' => 'display: inline-block;float: right;'
                            ],
                        ]
                    )
                );
            }
        }
        return $tag;
    }
}
