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

/**
 * the progressbar field class
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
        if ($this->indeterminate == true || !is_numeric($this->value)) {
            $this->addJs("\$('#{$id}','#{$form->getId()}').progressbar({ value: false });");
        } elseif ($this->show_label == true) {
            $this->addJs(
                "
        \$('#{$id}','#{$form->getId()}').progressbar({ value: parseInt({$this->value}) });
        \$('#{$id} .progress-label','#{$form->getId()}').text('{$this->value}%');
      "
            );
        } else {
            $this->addJs("\$('#{$id}','#{$form->getId()}').progressbar({ value: parseInt({$this->value}) });");
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
        $attributes = $this->getAttributes();

        if ($this->show_label == true) {
            $this->addCss("#{$form->getId()} #{$id}.ui-progressbar {position: relative;}");
            $this->addCss("#{$form->getId()} #{$id} .progress-label {position: absolute;left: 50%;top: 4px;}");
        }

        return "<div id=\"{$id}\" {$attributes}>".
                (($this->show_label == true) ?
                    "<div class=\"progress-label\"></div>":
                    ""
                ).
                "</div>\n";
    }
}
