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
use Degami\PHPFormsApi\Abstracts\Containers\FieldsContainerMultiple;
use Degami\Basics\Html\TagElement;

/**
 * an accordion field container
 */
class Accordion extends FieldsContainerMultiple
{

    /** @var string height style */
    protected $height_style = 'auto';

    /** @var integer active tab */
    protected $active = '0';

    /** @var boolean collapsible */
    protected $collapsible = false;


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
        $collapsible = ($this->collapsible) ? 'true':'false';
        $this->addJs(
            "\$('#{$id}','#{$form->getId()}').accordion({
            heightStyle: \"{$this->height_style}\",
            active: {$this->active},
            collapsible: {$collapsible}
        });"
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
        $tag = new TagElement([
            'tag' => 'div',
            'id' => $id,
            'attributes' => $this->attributes,
        ]);

        foreach ($this->partitions as $accordionindex => $accordion) {
            $insertorder = array_flip($this->insert_field_order[$accordionindex]);
            $weights = [];
            $order = [];

            $partition_fields = $this->getPartitionFields($accordionindex);

            foreach ($partition_fields as $key => $elem) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
                $weights[$key]  = $elem->getWeight();
                $order[$key] = $insertorder[$key];
            }
            if (count($partition_fields) > 0) {
                array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
            }
            $tag->addChild(new TagElement([
                'tag' => 'h3',
                'text' => $this->getText($this->partitions[$accordionindex]['title']),
                'attributes' => [
                    'class' => 'tabel '.(
                        $this->partitionHasErrors($accordionindex, $form) ?
                        'has-errors' : ''
                    )
                ],
            ]));
            $inner = new TagElement([
                'tag' => 'div',
                'id' => $id.'-tab-inner-'.$accordionindex,
                'attributes' => [
                    'class' => 'tab-inner'.(
                        $this->partitionHasErrors($accordionindex, $form) ?
                        ' has-errors' : ''
                    )
                ],
            ]);
            foreach ($partition_fields as $name => $field) {
                /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
                $inner->addChild($field->renderHTML($form));
            }
            $tag->addChild($inner);
        }

        return $tag;
    }

    /**
     * Adds a new accordion
     *
     * @param string $title accordion title
     */
    public function addAccordion($title)
    {
        return $this->addPartition($title);
    }
}
