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

/**
 * the "masked" text input field class
 */
class Maskedfield extends Textfield
{
    /**
     * input mask string
     *
     * @var string
     */
    protected $mask;

    /**
     * jQuery Mask Plugin patterns
     *
     * @var array
     */
    private $translation = [
        '0'  =>  "\d",
        '9'  =>  "\d?",
        '#'  =>  "\d+",
        'A'  =>  "[a-zA-Z0-9]",
        'S'  =>  "[a-zA-Z]",
    ];

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options, $name = null)
    {
        if (!isset($options['attributes']['class'])) {
            $options['attributes']['class'] = '';
        }
        $options['attributes']['class'].=' maskedfield';

        parent::__construct($options, $name);
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
        $this->addJs("\$('#{$id}','#{$form->getId()}').mask('{$this->mask}');");
        parent::preRender($form);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this TRUE if this element conforms to mask
     */
    public function valid()
    {
        $mask = $this->mask;
        $mask = preg_replace("(\[|\]|\(|\))", "\\\1", $mask);
        foreach ($this->translation as $search => $replace) {
            $mask = str_replace($search, $replace, $mask);
        }
        $mask = '/^'.$mask.'$/';
        if (!preg_match($mask, $this->value)) {
            $this->addError($this->getText("Value does not conform to mask"), __FUNCTION__);

            if ($this->stop_on_first_error) {
                return false;
            }
        }

        return parent::valid();
    }
}
