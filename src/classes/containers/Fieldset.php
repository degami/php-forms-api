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

use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Fields\Hidden;

/**
 * a fieldset field container
 */
class Fieldset extends FieldsContainer
{

    /**
     * collapsible flag
     *
     * @var boolean
     */
    protected $collapsible = false;

    /**
     * collapsed flag
     *
     * @var boolean
     */
    protected $collapsed = false;

    /**
     * inner div attributes
     *
     * @var array
     */
    protected $inner_attributes = [];

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        static $js_collapsible_added = false;
        if ($this->pre_rendered == true) {
            return;
        }

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->collapsible) {
            $this->attributes['class'] .= ' collapsible';
            if ($this->collapsed) {
                $this->attributes['class'] .= ' collapsed';
            } else {
                $this->attributes['class'] .= ' expanded';
            }

            if (!$js_collapsible_added) {
                $this->addJs(
                    "
          \$('fieldset.collapsible').find('legend:not(\".collapsible-attached\")').css({'cursor':'pointer'}).click(function(evt){
            evt.preventDefault();
            var \$this = \$(this);
            \$this.parent().find('.fieldset-inner').toggle( 'blind', {}, 500, function(){
              if(\$this.parent().hasClass('expanded')){
                \$this.parent().removeClass('expanded').addClass('collapsed');
              }else{
                \$this.parent().removeClass('collapsed').addClass('expanded');
              }
            });
          }).addClass('collapsible-attached');
          \$('fieldset.collapsible.collapsed .fieldset-inner').hide();"
                );
                $js_collapsible_added = true;
            }
        }

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return TagElement        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $insertorder = array_flip($this->insert_field_order);
        $weights = [];
        $order = [];
        foreach ($this->getFields() as $key => $elem) {
            /** @var Field $elem */
            $weights[$key]  = $elem->getWeight();
            $order[$key] = $insertorder[$key] ?? PHP_INT_MAX;
        }
        if (count($this->getFields()) > 0) {
            array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->getFields());
        }

        $tag = new TagElement([
            'tag' => 'fieldset',
            'id' => $id,
            'attributes' => $this->attributes,
        ]);
        if (!empty($this->title)) {
            $tag->addChild(new TagElement([
                'tag' => 'legend',
                'text' => $this->getText($this->title),
            ]));
        }

        $inner_attributes = $this->inner_attributes;
        if (!isset($inner_attributes['class'])) {
            $inner_attributes['class'] = '';
        }
        $inner_attributes['class'] .= ' fieldset-inner';
        $inner_attributes['class'] = trim($inner_attributes['class']);

        $inner = new TagElement([
            'tag' => 'div',
            'attributes' => $inner_attributes,
        ]);

        // hidden fields are always first
        usort($this->getFields(), function($fieldA, $fieldB){
            if (is_object($fieldA) && is_a($fieldA, Hidden::class)) {
                return -1;
            }
            if (is_object($fieldB) && is_a($fieldB, Hidden::class)) {
                return 1;
            }
            return 0;
        });

        foreach ($this->getFields() as $name => $field) {
            /** @var Field $field */
            $inner->addChild($field->renderHTML($form));
        }

        $tag->addChild($inner);
        return $tag;
    }
}
