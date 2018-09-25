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
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Accessories\TagElement;

/**
 * the color input field class
 */
class Color extends Field
{
    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        if (!empty($this->default_value) && !$this->isRGB($this->default_value)) {
            $this->value = $this->default_value = '#000000';
        }
    }

    private function isRGB($str)
    {
        return preg_match("/^#?([a-f\d]{3}([a-f\d]{3})?)$/i", $str);
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

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        if ($this->hasErrors()) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }
        if (is_array($this->value)) {
            $this->value = '';
        }

        $tag = new TagElement(
            [
                'tag' => 'input',
                'type' => 'color',
                'id' => $id,
                'name' => $this->name,
                'value' => htmlspecialchars($this->value),
                'attributes' => $this->attributes + ['size' => $this->size],
            ]
        );
        return $tag;
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
}
