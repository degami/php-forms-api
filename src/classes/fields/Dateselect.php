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

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\Field;

/**
 * The date select group field class
 */
class Dateselect extends Field
{
    /**
     * granularity (day / month / year)
     *
     * @var string
     */
    protected $granularity = 'day';

    /**
     * start year
     *
     * @var integer
     */
    protected $start_year;

    /**
     * end year
     *
     * @var integer
     */
    protected $end_year;

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
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        $this->start_year = date('Y')-100;
        $this->end_year = date('Y')+100;
        $this->default_value = [
            'year'=>date('Y'),
            'month'=>date('m'),
            'day'=>date('d'),
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
                "\$('#{$id} select[name=\"{$this->name}[year]\"]','#{$form->getId()}')
                    .selectmenu({width: 'auto' });"
            );
            if ($this->granularity != 'year') {
                $this->addJs(
                    "\$('#{$id} select[name=\"{$this->name}[month]\"]','#{$form->getId()}')
                        .selectmenu({width: 'auto' });"
                );
                if ($this->granularity != 'month') {
                    $this->addJs(
                        "\$('#{$id} select[name=\"{$this->name}[day]\"]','#{$form->getId()}')
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
     * @return string        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();
        $output = '';

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }
        $attributes = $this->getAttributes(['type','name','id','size','day','month','year']);

        $output .= "<div id=\"{$id}\" {$attributes}>";

        if ($this->granularity!='year' && $this->granularity!='month') {
            $attributes = ''.($this->disabled == true) ? ' disabled="disabled"':'';
            if (isset($this->attributes['day']) && is_array($this->attributes['day'])) {
                if ($this->disabled == true) {
                    $this->attributes['day']['disabled']='disabled';
                }
                $attributes = $this->getAttributesString($this->attributes['day'], ['type','name','id','value']);
            }
            $output .= "<select name=\"{$this->name}[day]\" {$attributes}>";
            for ($i=1; $i<=31; $i++) {
                $selected = ($i == $this->value['day']) ? ' selected="selected"' : '';
                $output .= "<option value=\"{$i}\" {$selected}>{$i}</option>";
            }
            $output .= "</select>";
        }
        if ($this->granularity!='year') {
            $attributes = ''.($this->disabled == true) ? ' disabled="disabled"':'';
            if (isset($this->attributes['month']) && is_array($this->attributes['month'])) {
                if ($this->disabled == true) {
                    $this->attributes['month']['disabled']='disabled';
                }
                $attributes = $this->getAttributesString($this->attributes['month'], ['type','name','id','value']);
            }
            $output .= "<select name=\"{$this->name}[month]\" {$attributes}>";
            for ($i=1; $i<=12; $i++) {
                $selected = ($i == $this->value['month']) ? ' selected="selected"' : '';
                $output .= "<option value=\"{$i}\" {$selected}>{$i}</option>";
            }
            $output .= "</select>";
        }
        $attributes = ''.($this->disabled == true) ? ' disabled="disabled"':'';
        if (isset($this->attributes['year']) && is_array($this->attributes['year'])) {
            if ($this->disabled == true) {
                $this->attributes['year']['disabled']='disabled';
            }
            $attributes = $this->getAttributesString($this->attributes['year'], ['type','name','id','value']);
        }
        $output .= "<select name=\"{$this->name}[year]\" {$attributes}>";
        for ($i=$this->start_year; $i<=$this->end_year; $i++) {
            $selected = ($i == $this->value['year']) ? ' selected="selected"' : '';
            $output .= "<option value=\"{$i}\" {$selected}>{$i}</option>";
        }
        $output .= "</select>";
        $output .= "</div>";

        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $value value to set
     */
    public function processValue($value)
    {
        $this->value = [
        'year' => $value['year'],
        ];
        if ($this->granularity!='year') {
            $this->value['month'] = $value['month'];
            if ($this->granularity!='month') {
                $this->value['day'] = $value['day'];
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid()
    {
        $year = $this->value['year'];
        $month = isset($this->value['month']) ? $this->value['month'] : 1;
        $day = isset($this->value['day']) ? $this->value['day'] : 1;

        if (!checkdate($month, $day, $year)) {
            $titlestr = (!empty($this->title)) ? $this->title : !empty($this->name) ? $this->name : $this->id;
            $this->addError(str_replace("%t", $titlestr, $this->getText("%t: Invalid date")), __FUNCTION__);

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
    public function isAValue()
    {
        return true;
    }

    /**
     * Get start timestamp
     *
     * @return int start timestamp
     */
    public function tsStart()
    {
        $year = $this->value['year'];
        $month = isset($this->value['month']) ? $this->value['month'] : 1;
        $day = isset($this->value['day']) ? $this->value['day'] : 1;

        return mktime(0, 0, 0, $month, $day, $year);
    }

    /**
     * Get end timestamp
     *
     * @return int end timestamp
     */
    public function tsEnd()
    {
        $year = $this->value['year'];
        $month = isset($this->value['month']) ? $this->value['month'] : 1;
        $day = isset($this->value['day']) ? $this->value['day'] : 1;

        return mktime(23, 59, 59, $month, $day, $year);
    }

    /**
     * Get value as a date string
     *
     * @return string date value
     */
    public function valueString()
    {
        $value = $this->getValues();
        $out = (($value['year'] < 10) ? '0':'').((int) $value['year']);
        if ($this->granularity!='year') {
            $out .= '-'.(($value['month'] < 10) ? '0':'').((int) $value['month']);
            if ($this->granularity!='month') {
                $out .= '-'.(($value['day'] < 10) ? '0':'').((int) $value['day']);
            }
        }
        return $out;
    }
}
