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
use Degami\PHPFormsApi\Accessories\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * The radios group field class
 */
class Radios extends FieldMultivalues
{

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
        $output = '<div class="options">';
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        foreach ($this->options as $key => $value) {
            if (is_array($value) && isset($value['attributes'])) {
                $attributes = $value['attributes'];
            } else {
                $attributes = [];
            }

            if (is_array($value)) {
                $value = $value['value'];
            }

            $output .= "<label class=\"label-radio\" for=\"{$id}-{$key}\">";
            $tag = new TagElement(
                [
                    'tag' => 'input',
                    'type' => 'radio',
                    'id' => "{$id}-{$key}",
                    'name' => $this->name,
                    'value' => $key,
                    'attributes' => array_merge($attributes, ($this->value == $key) ? ['checked' => 'checked'] : []),
                    'text' => $value,
                ]
            );
            $output .= $tag->renderTag();
            $output .= "</label>\n";
        }
        $output .= '</div>';
        return $output;
    }
}
