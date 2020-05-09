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
use Degami\PHPFormsApi\Abstracts\Fields\ComposedField;

/**
 * The datetime select group field class
 */
class Datetimeselect extends ComposedField
{
    /**
     * date sub element
     *
     * @var Date
     */
    protected $date = null;

    /**
     * time sub_element
     *
     * @var Time
     */
    protected $time = null;

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
        parent::__construct($options, $name);

        unset($options['title']);
        $options['container_tag'] = '';

        $options['type'] = 'dateselect';
        $this->date = new Dateselect($options, $this->getSubfieldName('date'));

        $options['type'] = 'timeselect';
        $this->time = new Timeselect($options, $this->getSubfieldName('time'));
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
        $id = $this->getHtmlId();
        $this->addCss("#{$id} div.date,#{$id} div.time{display: inline-block;margin-right: 5px;}");

        $this->date->preRender($form);
        $this->time->preRender($form);

        foreach ($this->date->getJs() as $date_js_line) {
            if (!empty($date_js_line)) {
                $this->addJs($date_js_line);
            }
        }

        foreach ($this->time->getJs() as $time_js_line) {
            if (!empty($time_js_line)) {
                $this->addJs($time_js_line);
            }
        }

        parent::preRender($form);
    }

    /**
     * {@inheritdoc} . it simply calls the sub elements preprocess
     *
     * @param string $process_type preprocess type
     */
    public function preProcess($process_type = "preprocess")
    {
        $this->date->preProcess($process_type);
        $this->time->preProcess($process_type);
    }

    /**
     * {@inheritdoc} . it simply calls the sub elements process
     *
     * @param array $values value to set
     */
    public function processValue($values)
    {
        $this->processSubfieldsValues($values, $this->date, 'date');
        $this->processSubfieldsValues($values, $this->time, 'time');
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid()
    {
        return $this->date->isValid() && $this->time->isValid();
    }

    /**
     * renders form errors
     *
     * @return string errors as an html <li> list
     */
    public function showErrors()
    {
        $out = trim($this->date->showErrors() . $this->time->showErrors());
        return ($out == '') ? '' : $out;
    }

    /**
     * resets the sub elements
     */
    public function resetField()
    {
        $this->date->resetField();
        $this->time->resetField();
    }

    /**
     * Return field value
     *
     * @return array field value
     */
    public function getValues()
    {
        return [
            'date'=> $this->date->getValues(),
            'time'=> $this->time->getValues(),
            'datetime' => $this->date->valueString().' '.$this->time->valueString(),
        ];
    }
}
