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
 * The textarea field class
 */
class Textarea extends Field
{

    /**
     * rows
     *
     * @var integer
     */
    protected $rows = 5;

    /**
     * resizable flag
     *
     * @var boolean
     */
    protected $resizable = false;

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
        if ($this->resizable == true) {
            $this->addJs("\$('#{$id}','#{$form->getId()}').resizable({handles:\"se\"});");
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

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        $errors = $this->getErrors();
        if (!empty($errors)) {
            $this->attributes['class'] .= ' has-errors';
        }
        if ($this->disabled == true) {
            $this->attributes['disabled']='disabled';
        }

        $tag = new TagElement([
            'tag' => 'textarea',
            'id' => $id,
            'name' => $this->name,
            'text' => $this->getValues(),
            'attributes' => $this->attributes + ['cols' => $this->size, 'rows' => $this->rows],
            'has_close' => true,
        ]);
        return $tag;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is a value
     */
    public function isAValue()
    {
        return true;
    }
}
