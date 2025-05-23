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
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Form;
use Degami\Basics\Html\TagElement;

/**
 * The "Multiselect select" field class
 */
class Multiselect extends Select
{
    /** @var array options on the left side */
    private $leftOptions;

    /** @var array options on the right side */
    private $rightOptions;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param ?string $name    field name
     */
    public function __construct(array $options, ?string $name = null)
    {
        if (!is_array($options)) {
            $options = [];
        }
        $options['multiple'] = true;
        parent::__construct($options, $name);

        $this->leftOptions = $this->options;
        $this->rightOptions = [];

        foreach ($this->getDefaultValue() as $value) {
            foreach ($this->leftOptions as $k => $v) {
                /** @var Option $v */
                if ($v->getKey() == $value) {
                    $this->rightOptions[] = clone $v;
                    unset($this->leftOptions[$k]);
                }
            }
        }

        $this->setAttribute('style', 'width: 100%;');
    }


    /**
     * {@inheritdocs}
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
            "\$('#{$id}_move_right, #{$id}_move_left','#{$form->getId()}')
            .click(function(evt){
              evt.preventDefault();
              var \$this = \$(this);
              var \$from = \$('#{$id}_from','#{$form->getId()}');
              var \$to = \$('#{$id}_to','#{$form->getId()}');

              if( /_move_right\$/i.test(\$this.attr('id')) ){
                \$from.find('option:selected').each(function(index,elem){
                    var \$elem = \$(elem); \$elem.appendTo(\$to);
                });
              }
              if( /_move_left\$/i.test(\$this.attr('id')) ){
                \$to.find('option:selected').each(function(index,elem){
                    var \$elem = \$(elem); \$elem.appendTo(\$from);
                });
              }
            });"
        );

        $this->addJs(
            "\$('#{$form->getId()}').submit(function(evt){
            var \$to = \$('#{$id}_to','#{$form->getId()}');
            \$to.find('option').each(function(index,elem){elem.selected=true;});
        });"
        );

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $value value to set
     */
    public function processValue($value = [])
    {
        parent::processValue($value);

        $this->leftOptions = $this->options;
        $this->rightOptions = [];

        $values = $this->getValue();
        foreach (array_values($values) as $keyval) {
            foreach ($this->leftOptions as $k => $v) {
                /** @var Option $v */
                if ($v->getKey() == $keyval) {
                    $this->rightOptions[] = clone $v;
                    unset($this->leftOptions[$k]);
                }
            }
        }
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

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $field_name = "{$this->name}[]";

        $table = new TagElement([
          'tag' => 'table',
          'id' => $id.'-table',
          'attributes' => [
            'boder' => 0,
            'colspan' => 0,
            'cellpadding' => 0,
          ],
        ]);

        $tr1 = new TagElement(['tag' => 'tr']);
        $table->addChild($tr1);

        $td1 = new TagElement(['tag' => 'td', 'attributes' => ['style' => 'width: 45%']]);
        $td2 = new TagElement(['tag' => 'td', 'attributes' => ['style' => 'width: 10%']]);
        $td3 = new TagElement(['tag' => 'td', 'attributes' => ['style' => 'width: 45%']]);

        $tr1->addChild($td1);
        $tr1->addChild($td2);
        $tr1->addChild($td3);

        $select_left = new TagElement([
          'tag' => 'select',
          'name' => $this->name.'_from',
          'id' => $id.'_from',
          'attributes' => $this->attributes + ['size' => $this->size, 'multiple' => 'multiple'],
        ]);

        if (isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])) {
            $select_left->addChild(new TagElement([
              'tag' => 'option',
              'attributes' => [
                'disabled' => 'disabled',
              ] + (isset($this->default_value) ? [] : ['selected' => 'selected']),
              'text' => $this->attributes['placeholder'],
            ]));
        }
        foreach ($this->leftOptions as $key => $value) {
            /** @var Option $value */
            $select_left->addChild($value->renderHTML($this));
        }
        $td1->addChild($select_left);

        $buttons = new TagElement([
          'tag' => 'div', 'attributes' => ['class' => 'buttons'],
        ]);
        $buttons->addChild(new TagElement([
          'tag' => 'button',
          'id' => $this->name.'_move_right',
          'text' => '&gt;&gt;',
        ]))
        ->addChild(new TagElement(['tag' => 'br']))
        ->addChild(new TagElement(['tag' => 'br']))
        ->addChild(new TagElement([
          'tag' => 'button',
          'id' => $this->name.'_move_left',
          'text' => '&lt;&lt;',
        ]));
        $td2->addChild($buttons);

        $select_right = new TagElement([
          'tag' => 'select',
          'name' => $field_name,
          'id' => $id.'_to',
          'attributes' => $this->attributes + ['size' => $this->size, 'multiple' => 'multiple'],
        ]);

        if (isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])) {
            $select_right->addChild(new TagElement([
              'tag' => 'option',
              'attributes' => [
                'disabled' => 'disabled',
              ] + (isset($this->default_value) ? [] : ['selected' => 'selected']),
              'text' => $this->attributes['placeholder'],
            ]));
        }
        foreach ($this->rightOptions as $key => $value) {
            /** @var Option $value */
            $select_right->addChild($value->renderHTML($this));
        }
        $td3->addChild($select_right);

        return $table;
    }
}
