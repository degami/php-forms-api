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
use Degami\PHPFormsApi\Abstracts\Base\Field;

/**
 * The "actionable" field element class (a button, a submit or a reset)
 *
 * @abstract
 */
abstract class Action extends Field
{

    /**
     * "use jqueryui button method on this element" flag
     *
     * @var boolean
     */
    protected $js_button = false;

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
        if ($this->js_button == true) {
            $id = $this->getHtmlId();
            $this->addJs("\$('#{$id}','#{$form->getId()}').button();");
        }
        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is not a value
     */
    public function isAValue(): bool
    {
        return false;
    }

    /**
     * validate function
     *
     * @return boolean this field is always valid
     */
    public function isValid(): bool
    {
        return true;
    }
}
