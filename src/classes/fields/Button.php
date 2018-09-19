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
use Degami\PHPFormsApi\Accessories\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\Clickable;

/**
 * the button field class
 */
class Button extends Clickable
{

    /**
     * element label
     *
     * @var string
     */
    protected $label;

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct($options, $name);
        if (empty($this->label)) {
            $this->label = $this->value;
        }
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
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $tag = new TagElement(
            [
                'tag' => 'button',
                'id' => $id,
                'name' => $this->name,
                'value' => $this->value,
                'text' => $this->getText($this->label),
                'attributes' => $this->attributes,
                'has_close' => true,
            ]
        );
        return $tag;
    }
}
