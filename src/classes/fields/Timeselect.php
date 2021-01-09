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
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\Basics\Html\TagElement;

/**
 * The time select group field class
 */
class Timeselect extends Field
{

    /**
     * granularity (seconds / minutes / hours)
     *
     * @var string
     */
    protected $granularity = 'seconds';

    /**
     * "use js selects" flag
     *
     * @var boolean
     */
    protected $js_selects = false;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param ?string $name    field name
     */
    public function __construct(array $options = [], string $name = null)
    {
        $this->default_value = [
            'hours'=>0,
            'minutes'=>0,
            'seconds'=>0,
        ];

        parent::__construct($options, $name);
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
        if ($this->js_selects == true) {
            $id = $this->getHtmlId();

            $this->addJs(
                "\$('#{$id} select[name=\"{$this->name}[hours]\"]','#{$form->getId()}')
                            .selectmenu({width: 'auto' });"
            );
            if ($this->granularity != 'hours') {
                $this->addJs(
                    "\$('#{$id} select[name=\"{$this->name}[minutes]\"]','#{$form->getId()}')
                                .selectmenu({width: 'auto' });"
                );

                if ($this->granularity != 'minutes') {
                    $this->addJs(
                        "\$('#{$id} select[name=\"{$this->name}[seconds]\"]','#{$form->getId()}')
                                    .selectmenu({width: 'auto' });"
                    );
                }
            }
        }

        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
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
        $attributes = $this->getAttributes(['type','name','id','size','hours','minutes','seconds']);

        $tag = new TagElement([
            'tag' => 'div',
            'id' => $id,
            'attributes' => $this->attributes,
        ]);

        if (!(isset($this->attributes['hours']) && is_array($this->attributes['hours']))) {
            $this->attributes['hours'] = [];
        }
        if ($this->disabled == true) {
            $this->attributes['hours']['disabled']='disabled';
        }
        $select_hours = new TagElement([
            'tag' => 'select',
            'name' => $this->name.'[hours]',
            'attributes' => $this->attributes['hours'],
        ]);
        for ($i=0; $i<=23; $i++) {
            $select_hours->addChild(new TagElement([
                'tag' => 'option',
                'value' => $i,
                'attributes' => [] + (($i == $this->value['hours']) ? ['selected' => 'selected'] : []),
                'text' => str_pad($i, 2, "0", STR_PAD_LEFT),
            ]));
        }
        $tag->addChild($select_hours);
        if ($this->granularity != 'hours') {
            if (!(isset($this->attributes['minutes']) && is_array($this->attributes['minutes']))) {
                $this->attributes['minutes'] = [];
            }
            if ($this->disabled == true) {
                $this->attributes['minutes']['disabled']='disabled';
            }
            $select_minutes = new TagElement([
                'tag' => 'select',
                'name' => $this->name.'[minutes]',
                'attributes' => $this->attributes['minutes'],
            ]);
            for ($i=0; $i<=59; $i++) {
                $select_minutes->addChild(new TagElement([
                    'tag' => 'option',
                    'value' => $i,
                    'attributes' => [] + (($i == $this->value['minutes']) ? ['selected' => 'selected'] : []),
                    'text' => str_pad($i, 2, "0", STR_PAD_LEFT),
                ]));
            }
            $tag->addChild($select_minutes);
            if ($this->granularity != 'minutes') {
                if (!(isset($this->attributes['seconds']) && is_array($this->attributes['seconds']))) {
                    $this->attributes['seconds'] = [];
                }
                if ($this->disabled == true) {
                    $this->attributes['seconds']['disabled']='disabled';
                }
                $select_seconds = new TagElement([
                    'tag' => 'select',
                    'name' => $this->name.'[seconds]',
                    'attributes' => $this->attributes['seconds'],
                ]);
                for ($i=0; $i<=59; $i++) {
                    $select_seconds->addChild(new TagElement([
                        'tag' => 'option',
                        'value' => $i,
                        'attributes' => [] + (($i == $this->value['seconds']) ? ['selected' => 'selected'] : []),
                        'text' => str_pad($i, 2, "0", STR_PAD_LEFT),
                    ]));
                }
                $tag->addChild($select_seconds);
            }
        }
        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value value to set
     */
    public function processValue($value)
    {
        $this->value = [
        'hours' => $value['hours'],
        ];
        if ($this->granularity!='hours') {
            $this->value['minutes'] = $value['minutes'];
            if ($this->granularity!='minutes') {
                $this->value['seconds'] = $value['seconds'];
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid() : bool
    {
        $check = true;
        $check &= ($this->value['hours']>=0 && $this->value['hours']<=23);

        if ($this->granularity != 'hours') {
            $check &= ($this->value['minutes']>=0 && $this->value['minutes']<=59);

            if ($this->granularity != 'minutes') {
                $check &= ($this->value['seconds']>=0 && $this->value['seconds']<=59);
            }
        }

        if (! $check) {
            $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;
            $this->addError(str_replace("%t", $titlestr, $this->getText("%t: Invalid time")), __FUNCTION__);

            if ($this->stop_on_first_error) {
                return false;
            }
        }
        return parent::isValid();
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue() : bool
    {
        return true;
    }

    /**
     * Get value as a date string
     *
     * @return string date value
     */
    public function valueString(): string
    {
        $value = $this->getValues();
        $out = (($value['hours'] < 10) ? '0':'').((int) $value['hours']);

        if ($this->granularity!='hours') {
            $out .= ':'.(($value['minutes'] < 10) ? '0':'').((int) $value['minutes']);
            if ($this->granularity!='minutes') {
                $out .= ':'.(($value['seconds'] < 10) ? '0':'').((int) $value['seconds']);
            }
        }

        return $out;
    }
}
