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
 * the geolocation field class
 */
class Geolocation extends ComposedField
{

    /**
     * latitude
     *
     * @var float
     */
    protected $latitude;

    /**
     * longitude
     *
     * @var float
     */
    protected $longitude;

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);

        $defaults = isset($options['default_value']) ? $options['default_value'] : ['latitude' => 0, 'longitude' => 0];

        unset($options['title']);
        unset($options['prefix']);
        unset($options['suffix']);
        $options['container_tag'] = '';

        if (!isset($options['size'])) {
            $options['size'] = 5;
        }

        $options['type'] = 'textfield';
        $options['suffix'] = $this->getText('latitude').' ';
        $options['default_value'] = (is_array($defaults) && isset($defaults['latitude'])) ? $defaults['latitude'] : 0;
        $this->latitude = new Textfield($options, $name.'_latitude');

        $options['type'] = 'textfield';
        $options['suffix'] = $this->getText('longitude').' ';
        $options['default_value'] = (is_array($defaults) && isset($defaults['longitude'])) ? $defaults['longitude'] : 0;
        $this->longitude = new Textfield($options, $name.'_longitude');
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
        parent::preRender($form);

        $this->latitude->preRender($form);
        $this->longitude->preRender($form);
    }

    /**
     * {@inheritdoc} . it simply calls the sub elements preprocess
     *
     * @param string $process_type preprocess type
     */
    public function preProcess($process_type = "preprocess")
    {
        $this->latitude->preProcess($process_type);
        $this->longitude->preProcess($process_type);
    }

    /**
     * {@inheritdoc} . it simply calls the sub elements process
     *
     * @param array $values value to set
     */
    public function processValue($values)
    {
        $this->latitude->processValue($values[$this->getName().'_latitude']);
        $this->longitude->processValue($values[$this->getName().'_longitude']);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid()
    {
        return $this->latitude->isValid() && $this->longitude->isValid();
    }


    /**
     * renders form errors
     *
     * @return string errors as an html <li> list
     */
    public function showErrors()
    {
        $out = trim($this->latitude->showErrors() . $this->longitude->showErrors());
        return ($out == '') ? '' : $out;
    }


    /**
     * resets the sub elements
     */
    public function resetField()
    {
        $this->latitude->resetField();
        $this->longitude->resetField();
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
        $output .= $this->latitude->renderHTML($form);
        $output .= $this->longitude->renderHTML($form);
        $output .= "</{$this->tag}>\n";
        return $output;
    }

    /**
     * return field value
     *
     * @return array field value
     */
    public function getValues()
    {
        return [
            'latitude'=> $this->latitude->getValues(),
            'longitude'=> $this->longitude->getValues(),
        ];
    }
}
