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
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;
use Degami\PHPFormsApi\Abstracts\Fields\Optionable;

/**
 * The select field class
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
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options, $name)
    {
        if (isset($options['options'])) {
            foreach ($options['options'] as $k => $o) {
                if ($o instanceof Optionable) {
                    $o->setParent($this);
                    $this->addOption($o);
                } elseif (is_array($o)) {
                    $option = new Optgroup($k, ['options' => $o]);
                    $option->setParent($this);
                    $this->addOption($option);
                } else {
                    $option = new Option($k, $o);
                    $option->setParent($this);
                    $this->addOption($option);
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
     * Return field multiple attribute
     *
     * @return boolean field is multiple
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set field multiple attribute
     *
     * @param  boolean $multiple multiple attribute
     * @return Select
     */
    public function setMultiple($multiple = true)
    {
        $this->multiple = ($multiple == true);
        return $this;
    }

    /**
     * Return field value
     *
     * @return mixed field value
     */
    public function getValue()
    {
        return $this->value;
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
            $output .= $value->renderHTML($this);
        }
        $output .= "</select>\n";
        return $output;
    }
}
