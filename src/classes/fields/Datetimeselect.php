<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Fields\ComposedField;

/**
 * the datetime select group field class
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
     * class constructor
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
        $this->date = new Dateselect($options, $name.'_date');

        $options['type'] = 'timeselect';
        $this->time = new Timeselect($options, $name.'_time');
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
    public function preprocess($process_type = "preprocess")
    {
        $this->date->preprocess($process_type);
        $this->time->preprocess($process_type);
    }

    /**
     * {@inheritdoc} . it simply calls the sub elements process
     *
     * @param array $values value to set
     */
    public function process($values)
    {
        $this->date->process($values[$this->getName().'_date']);
        $this->time->process($values[$this->getName().'_time']);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function valid()
    {
        return $this->date->valid() && $this->time->valid();
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
    public function reset()
    {
        $this->date->reset();
        $this->time->reset();
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
        $attributes = $this->getAttributes();

        $this->tag = 'div';
        $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

        $required = ($this->validate->hasValue('required')) ? '<span class="required">*</span>' : '';
        $requiredafter = $requiredbefore = $required;
        if ($this->required_position == 'before') {
            $requiredafter = '';
            $requiredbefore = $requiredbefore.' ';
        } else {
            $requiredbefore = '';
            $requiredafter = ' '.$requiredafter;
        }

        if (!empty($this->title)) {
            if ($this->tooltip == false) {
                $this->label_class .= " label-" .$this->getElementClassName();
                $this->label_class = trim($this->label_class);
                $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
                $output .= "<label for=\"{$id}\" {$label_class}>{$requiredbefore}".
                            $this->getText($this->title).
                            "{$requiredafter}</label>\n";
            } else {
                if (!in_array('title', array_keys($this->attributes))) {
                    $this->attributes['title'] = strip_tags($this->getText($this->title).$required);
                }

                $id = $this->getHtmlId();
                $form->addJs("\$('#{$id}','#{$form->getId()}').tooltip();");
            }
        }
        $output .= $this->date->render($form);
        $output .= $this->time->render($form);
        $output .= "</{$this->tag}>\n";
        return $output;
    }

    /**
     * return field value
     *
     * @return array field value
     */
    public function values()
    {
        return [
            'date'=> $this->date->values(),
            'time'=> $this->time->values(),
            'datetime' => $this->date->valueString().' '.$this->time->valueString(),
        ];
    }
}
