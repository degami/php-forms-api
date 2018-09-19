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
use Degami\PHPFormsApi\Abstracts\Fields\Action;

/**
 * the reset button field class
 */
class Reset extends Action
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
        if (isset($options['value'])) {
            $this->value = $options['value'];
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
        if (empty($this->value)) {
            $this->value = 'Reset';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $tag = new TagElement(
            [
                'tag' => 'input',
                'type' => 'reset',
                'id' => $id,
                'name' => $this->name,
                'value' => $this->getText($this->value),
                'attributes' => $this->attributes,
            ]
        );
        return $tag;
    }
}
