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
use Degami\PHPFormsApi\Abstracts\Containers\FieldsContainerMultiple;
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * a "tabbed" field container
 */
class Tabs extends FieldsContainerMultiple
{

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
        $this->addJs("\$('#{$id}','#{$form->getId()}').tabs();");

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

        $tag = new TagElement(
            [
                'tag' => 'div',
                'id' => $id,
                'attributes' => $this->attributes,
            ]
        );

        $tab_links = new TagElement(
            [
                'tag' => 'ul',
            ]
        );

        $tag->addChild($tab_links);
    
        foreach ($this->partitions as $tabindex => $tab) {
            $insertorder = array_flip($this->insert_field_order[$tabindex]);
            $weights = [];
            $order = [];

            $partition_fields = $this->getPartitionFields($tabindex);

            foreach ($partition_fields as $key => $elem) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
                $weights[$key]  = $elem->getWeight();
                $order[$key] = $insertorder[$key];
            }
            if (count($this->getPartitionFields($tabindex)) > 0) {
                array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
            }

            $addclass_tab = ' class="tabel '.($this->partitionHasErrors($tabindex, $form) ? 'has-errors' : '').'"';
            $tab_links->addChild("<li{$addclass_tab}><a href=\"#{$id}-tab-inner-{$tabindex}\">".
                                        $this->getText($this->partitions[$tabindex]['title']).
                                        "</a></li>");

            $inner = new TagElement(
                [
                    'tag' => 'div',
                    'id' => $id.'-tab-inner-'.$tabindex,
                    'attributes' => [
                        'class' => 'tab-inner'.($this->partitionHasErrors($tabindex, $form) ? ' has-errors' : '')
                    ],
                ]
            );

            foreach ($partition_fields as $name => $field) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
                $inner->addChild($field->renderHTML($form));
            }
            $tag->addChild($inner);
        }
    
        return $tag;
    }

    /**
     * add a new tab
     *
     * @param string $title tab title
     */
    public function addTab($title)
    {
        return $this->addPartition($title);
    }
}