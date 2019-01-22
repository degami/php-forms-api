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
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Accessories\TagElement;

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
          \$('fieldset.collapsible').find('legend').css({'cursor':'pointer'}).click(function(evt){
            evt.preventDefault();
            var \$this = \$(this);
            \$this.parent().find('.fieldset-inner').toggle( 'blind', {}, 500, function(){
              if(\$this.parent().hasClass('expanded')){
                \$this.parent().removeClass('expanded').addClass('collapsed');
              }else{
                \$this.parent().removeClass('collapsed').addClass('expanded');
              }
            });
          });
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
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();

        $insertorder = array_flip($this->insert_field_order);
        $weights = [];
        $order = [];
        foreach ($this->getFields() as $key => $elem) {
            /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $elem */
            $weights[$key]  = $elem->getWeight();
            $order[$key] = $insertorder[$key];
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
        $inner = new TagElement([
            'tag' => 'div',
            'attributes' => ['class' => 'fieldset-inner'],
        ]);
        foreach ($this->getFields() as $name => $field) {
            /** @var \Degami\PHPFormsApi\Abstracts\Base\Field $field */
            $inner->addChild($field->renderHTML($form));
        }

        $tag->addChild($inner);
        return $tag;
    }
}
