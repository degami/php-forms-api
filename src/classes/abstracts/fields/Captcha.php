<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\Field;

/**
 * the captcha field class
 */
abstract class Captcha extends Field
{

    /**
     * "already validated" flag
     *
     * @var boolean
     */
    protected $already_validated = false;

    /**
     * {@inheritdoc}
     *
     * @param mixed $values value to set
     */
    public function processValue($values)
    {
        parent::processValue($values);
        if (isset($values['already_validated'])) {
            $this->already_validated = $values['already_validated'];
        }
    }

    /**
     * check if element is already validated
     *
     * @return boolean TRUE if element has already been validated
     */
    public function isAlreadyValidated()
    {
        return $this->already_validated;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean this is not a value
     */
    public function isAValue()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function afterValidate(Form $form)
    {
        $session_value =$this->getValues();
        $session_value['already_validated'] = $this->isAlreadyValidated();

        $_SESSION[$form->getId()]['steps'][$form->getCurrentStep()][$this->getName()] = $session_value;
    }
}