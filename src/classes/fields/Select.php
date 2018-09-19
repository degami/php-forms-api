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
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * the select field class
 */
class Select extends FieldMultivalues
{

    /**
     * multiple attribute
     *
     * @var boolean
     */
    protected $multiple = false;

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options, $name)
    {
        if (isset($options['options'])) {
            foreach ($options['options'] as $k => $o) {
                if ($o instanceof Option || $o instanceof Optgroup) {
                    $o->setParent($this);
                    $this->options[] = $o;
                } elseif (is_array($o)) {
                    $option = new Optgroup($k, ['options' => $o]);
                    $option->setParent($this);
                    $this->options[] = $option;
                } else {
                    $option = new Option($k, $o);
                    $option->setParent($this);
                    $this->options[] = $option;
                }
            }
            unset($options['options']);
        }

        if (isset($options['default_value'])) {
            if (!$this->isMultiple() && !(isset($options['multiple']) && $options['multiple']==true)) {
                if (is_array($options['default_value'])) {
                    $options['default_value'] = reset($options['default_value']);
                }
                $options['default_value'] = "".$options['default_value'];
            } else {
                if (!is_array($options['default_value'])) {
                    $options['default_value'] = [$options['default_value']];
                }
                foreach ($options['default_value'] as $k => $v) {
                    $options['default_value'][$k] = "".$v;
                }
            }
        }

        parent::__construct($options, $name);
    }

    /**
     * return field multiple attribute
     *
     * @return boolean field is multiple
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * set field multiple attribute
     *
     * @param boolean $multiple multiple attribute
     * @return Select
     */
    public function setMultiple($multiple = true)
    {
        $this->multiple = ($multiple == true);
        return $this;
    }

    /**
     * return field value
     *
     * @return mixed field value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * render_field hook
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
        $attributes = $this->getAttributes();

        $extra = ($this->multiple) ? ' multiple="multiple" size="'.$this->size.'" ' : '';
        $field_name = ($this->multiple) ? "{$this->name}[]" : $this->name;
        $output .= "<select name=\"{$field_name}\" id=\"{$id}\" {$extra} {$attributes}>\n";
        if (isset($this->attributes['placeholder']) && !empty($this->attributes['placeholder'])) {
            $output .= '<option disabled '.(isset($this->default_value) ? '' : 'selected').'>'.
                        $this->attributes['placeholder'].
                        '</option>';
        }
        foreach ($this->options as $key => $value) {
            /** @var \Degami\PHPFormsApi\Fields\Option $value */
            $output .= $value->render($this);
        }
        $output .= "</select>\n";
        return $output;
    }
}
