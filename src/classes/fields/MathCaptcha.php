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
use Degami\PHPFormsApi\Abstracts\Fields\Captcha;

/**
 * the image captcha field class
 */
class MathCaptcha extends Captcha
{
    /**
     * pre-fill code into textfield
     *
     * @var boolean
     */
    protected $pre_filled = false;

    /** @var string challenge code */
    private $code;

    /** @var integer first operator */
    private $a;

    /** @var integer second operator */
    private $b;

    /** @var string operation */
    private $op;

    /**
     * Get a math challege code
     *
     * @return string challenge string
     */
    private function getMathCode()
    {
        $this->code = '';
        $operators = ['+','-','*','/'];
        $this->a = mt_rand(0, 50);
        $this->op = $operators[ mt_rand(0, count($operators)-1) ];

        $ret = null;
        do {
            $this->b = mt_rand(1, 10);
            eval('$ret = '.$this->a.$this->op.$this->b.';');
        } while (!is_int($ret));

        $this->getSessionBag()->ensurePath("/math_captcha_code");
        $this->getSessionBag()->math_captcha_code->{$this->getName()} = $this->a.$this->op.$this->b;

        if (mt_rand(0, 1) == 0) {
            $this->code .= '<span class="nohm">'.mt_rand(1, 10).$operators[ mt_rand(0, count($operators)-1) ].'</span>';
        }
        $this->code .= $this->a;
        if (mt_rand(0, 1) == 0) {
            $this->code .= '<span class="nohm">'.$operators[ mt_rand(0, count($operators)-1) ].mt_rand(1, 10).'</span>';
        }
        $this->code .= $this->op;
        if (mt_rand(0, 1) == 0) {
            $this->code .= '<span class="nohm">'.mt_rand(1, 10).$operators[ mt_rand(0, count($operators)-1) ].'</span>';
        }
        $this->code .= $this->b;
        if (mt_rand(0, 1) == 0) {
            $this->code .= '<span class="nohm">'.$operators[ mt_rand(0, count($operators)-1) ].mt_rand(1, 10).'</span>';
        }

        return $this->code;
    }

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
        $this->addJs("\$('#{$id} .nohm','#{$form->getId()}').css({'display': 'none'});");

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
        $this->getMathCode();
        $codeval = '';
        if ($this->pre_filled == true) {
            eval('$codeval = '.$this->a.$this->op.$this->b.';');
        }
        $output = "<div id=\"{$id}\" {$attributes}>{$this->code}<br />";
        $tag = new TagElement(
            [
                'tag' => 'input',
                'type' => 'text',
                'name' => $this->name."[code]",
                'value' => $codeval,
            ]
        );
        $output .= $tag->renderTag();
        $output .= "</div>\n";
        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean TRUE if element is valid
     */
    public function isValid()
    {
        if ($this->already_validated == true) {
            return true;
        }
        if (isset($this->value['already_validated']) && $this->value['already_validated'] == true) {
            return true;
        }

        if (!isset($this->getSessionBag()->math_captcha_code->{$this->getName()})) {
            return true;
        }

        $_sessval = null;
        if (trim($this->getSessionBag()->math_captcha_code->{$this->getName()}) != '') {
            eval('$_sessval = '.$this->getSessionBag()->math_captcha_code->{$this->getName()}.';');
            if (isset($this->value['code']) && $this->value['code'] == $_sessval) {
                return true;
            }

            $this->addError($this->getText("Captcha response is not valid"), __FUNCTION__);
            return false;
        }
    }
}
