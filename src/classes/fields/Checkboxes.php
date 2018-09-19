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
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * the checkboxes group field class
 */
class Checkboxes extends FieldMultivalues
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
        if (!is_array($this->default_value)) {
            $this->default_value = [ $this->default_value ];
        }

        $output = '<div class="options">';
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        foreach ($this->options as $key => $value) {
            if ($value instanceof Checkbox) {
                $value->setName("{$this->name}".(count($this->options)>1 ? "[]":""));
                $value->setId("{$this->name}-{$key}");
                $output .= $value->render($form);
            } else {
                if (is_array($value) && isset($value['attributes'])) {
                    $attributes = $value['attributes'];
                } else {
                    $attributes = [];
                }
                if (is_array($value)) {
                    $value = $value['value'];
                }

                $output .= "<label class=\"label-checkbox\" for=\"{$id}-{$key}\">";
                $tag = new TagElement(
                    [
                        'tag' => 'input',
                        'type' => 'checkbox',
                        'id' => "{$id}-{$key}",
                        'name' => "{$this->name}".(count($this->options)>1 ? "[]" : ""),
                        'value' => $key,
                        'attributes' => array_merge(
                            $attributes,
                            (
                                is_array($this->default_value) &&
                                in_array($key, $this->default_value)
                            ) ?
                            ['checked' => 'checked'] : []
                        ),
                        'text' => $value,
                    ]
                );
                $output .= $tag->renderTag();
                $output .= "</label>\n";
            }
        }
        $output .= '</div>';
        return $output;
    }
}
