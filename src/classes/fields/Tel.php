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
 * the tel input field class
 */
class Tel extends Field
{

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
                'type' => 'tel',
                'id' => $id,
                'name' => $this->name,
                'value' => htmlspecialchars($this->value),
                'attributes' => $this->attributes + ['size' => $this->size],
            ]
        );
        return $tag;
    }

    /**
     * is_a_value hook
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}
