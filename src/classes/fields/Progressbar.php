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
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\Basics\Html\TagElement;

/**
 * The progressbar field class
 */
class Progressbar extends Markup
{

    /**
     * "indeterminate progressbar" flag
     *
     * @var boolean
     */
    protected $indeterminate = false;

    /**
     * "show label" flag
     *
     * @var boolean
     */
    protected $show_label = false;

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();
        if ($this->indeterminate == true || !is_numeric($this->getValues())) {
            $this->addJs("\$('#{$id}','#{$form->getId()}').progressbar({ value: false });");
        } elseif ($this->show_label == true) {
            $this->addJs(
                "
        \$('#{$id}','#{$form->getId()}').progressbar({ value: parseInt(" . $this->getValues() . ") });
        \$('#{$id} .progress-label','#{$form->getId()}').text('" . $this->getValues() . "%');
      "
            );
        } else {
            $this->addJs("\$('#{$id}','#{$form->getId()}').progressbar({ value: parseInt(" . $this->getValues() . ") });");
        }

        parent::preRender($form);
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

        if ($this->show_label == true) {
            $this->addCss("#{$form->getId()} #{$id}.ui-progressbar {position: relative;}");
            $this->addCss("#{$form->getId()} #{$id} .progress-label {position: absolute;left: 50%;top: 4px;}");
        }

        $tag = new TagElement([
            'tag' => 'div',
            'type' => null,
            'id' => $id,
            'text' => null,
            'attributes' => $this->attributes,
            'has_close' => true,
        ]);

        if ($this->show_label == true) {
            $tag->addChild(new TagElement([
                'tag' => 'div',
                'type' => null,
                'id' => null,
                'text' => null,
                'attributes' => ['class' => 'progress-label'],
                'has_close' => true,
            ]));
        }
        return $tag;
    }
}
