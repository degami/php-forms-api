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

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Containers\TagContainer;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\Basics\Html\TagElement;

/**
 * The composed field class
 */
abstract class ComposedField extends TagContainer
{
    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }

    /**
     * on_add_return overload
     *
     * @return string 'parent'
     */
    public function onAddReturn()
    {
        return 'parent';
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

        $this->tag = 'div';

        $required = ($this->validate->hasValue('required')) ? '<span class="required">*</span>' : '';
        $requiredafter = $requiredbefore = $required;
        if ($this->required_position == 'before') {
            $requiredafter = '';
            $requiredbefore = $requiredbefore.' ';
        } else {
            $requiredbefore = '';
            $requiredafter = ' '.$requiredafter;
        }

        if (!empty($this->title) && $this->tooltip == true && !in_array('title', array_keys($this->attributes))) {
            $this->attributes['title'] = strip_tags($this->getText($this->title).$required);
        }

        $tag = new TagElement([
            'tag' => $this->tag,
            'id' => $id,
            'attributes' => $this->attributes,
        ]);

        if (!empty($this->title)) {
            if ($this->tooltip == false) {
                $this->label_class .= " label-" .$this->getElementClassName();
                $this->label_class = trim($this->label_class);

                $tag_label = new TagElement([
                    'tag' => 'label',
                    'attributes' => [
                      'for' => $id,
                      'class' => $this->label_class,
                      'text' => $requiredbefore
                    ],
                ]);
                $tag_label->addChild($this->getText($this->title));
                $tag_label->addChild($requiredafter);
                $tag->addChild($tag_label);
            } else {
                $id = $this->getHtmlId();
                $form->addJs("\$('#{$id}','#{$form->getId()}').tooltip();");
            }
        }

        foreach (get_object_vars($this) as $name => &$property) {
            if ($property instanceof Field) {
                if ($name == 'parent') {
                    continue;
                }
                $tag->addChild($property->renderHTML($form));
            }
        }

        return $tag;
    }
}
