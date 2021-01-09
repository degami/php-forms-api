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
 * The radios group field class
 */
class Radios extends FieldMultivalues
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
        $tag = new TagElement([
            'tag' => 'div',
            'attributes' => ['class' => 'options'],
        ]);

        if ($this->disabled == true) {
            $this->attributes['disabled'] = 'disabled';
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

            $tag_label = new TagElement([
                'tag' => 'label',
                'attributes' => ['for' => "{$id}-{$key}", 'class' => "label-radio"],
            ]);
            $tag_label->addChild(new TagElement([
                'tag' => 'input',
                'type' => 'radio',
                'id' => "{$id}-{$key}",
                'name' => $this->name,
                'value' => $key,
                'attributes' => array_merge($attributes, ($this->getValues() == $key) ? ['checked' => 'checked'] : []),
                'text' => $value,
            ]));
            $tag->addChild($tag_label);
        }
        return $tag;
    }
}
