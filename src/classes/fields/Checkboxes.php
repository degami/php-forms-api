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

use Degami\Basics\Html\BaseElement;
use Degami\PHPFormsApi\Form;
use Degami\Basics\Html\TagElement;
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;

/**
 * The checkboxes group field class
 */
class Checkboxes extends FieldMultivalues
{
    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     *
     * @return string|BaseElement        the element html
     */
    public function renderField(Form $form)
    {
        $id = $this->getHtmlId();
        if (!is_array($this->default_value)) {
            $this->default_value = [ $this->default_value ];
        }

        $tag = new TagElement([
            'tag' => 'div', 'attributes' => ['class' => 'options'],
        ]);

        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        foreach ($this->options as $key => $value) {
            if ($value instanceof Checkbox) {
                $value->setName("{$this->name}".(count($this->options)>1 ? "[]":""));
                $value->setId("{$this->name}-{$key}");
                $tag->addChild($value->renderHTML($form));
            } else {
                if (is_array($value) && isset($value['attributes'])) {
                    $attributes = $value['attributes'];
                } else {
                    $attributes = [];
                }
                if (is_array($value)) {
                    $value = $value['value'];
                }

                $tag_label = new TagElement([
                    'tag' => 'label',
                    'attributes' => ['for' => "{$id}-{$key}", 'class' => "label-checkbox"],
                ]);
                $tag_label->addChild(new TagElement([
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
                ]));
                $tag->addChild($tag_label);
            }
        }

        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed field value
     */
    public function getValues()
    {
        return $this->getValue();
    }
}
