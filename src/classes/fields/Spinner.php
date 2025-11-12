<?php
/**
 * PHP FORMS API
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

/**
 * The spinner number input field class
 */
class Spinner extends Number
{
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

        $js_options = '';
        if (is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min) {
            $js_options = "{min: $this->min, max: $this->max, step: $this->step}";
        }

        $this->addJs("\$('#{$id}','#{$form->getId()}').attr('type','text').spinner({$js_options});");

        parent::preRender($form);
    }
}
