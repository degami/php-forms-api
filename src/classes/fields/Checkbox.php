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
 * the single checkbox input field class
 */
class Checkbox extends Field
{
    protected $text_position = 'after';

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        $this->value = null;
        if (isset($options['value'])) {
            $this->value = $options['value'];
        }
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

        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $this->label_class .= " label-" . $this->getElementClassName();
        $this->label_class = trim($this->label_class);
        $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";

        $output = "<label for=\"{$id}\" {$label_class}>".
                    (($this->text_position == 'before') ? $this->getText($this->title) : '');

        if ($this->value == $this->default_value) {
            $this->attributes['checked'] = 'checked';
        }

        $tag = new TagElement(
            [
                'tag' => 'input',
                'type' => 'checkbox',
                'id' => $id,
                'name' => $this->name,
                'value' => $this->default_value,
                'attributes' => $this->attributes,
            ]
        );
        $output .= $tag->renderTag();

        $output .= (($this->text_position != 'before') ? $this->getText($this->title) : '')."</label>\n";
        return $output;
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
