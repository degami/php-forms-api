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
 * the submit input type field class
 */
class Submit extends Clickable
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
        if (empty($this->value)) {
            $this->value = 'Submit';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $tag = new TagElement(
            [
                'tag' => 'input',
                'type' => 'submit',
                'id' => $id,
                'name' => $this->name,
                'value' => $this->getText($this->value),
                'attributes' => $this->attributes,
            ]
        );
        return $tag;
    }
}
