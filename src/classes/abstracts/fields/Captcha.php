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
 * The captcha field class
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
     * Check if element is already validated
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

        $this->getSessionBag()->ensurePath("/{$form->getId()}/steps/{$form->getCurrentStep()}");
        $this->getSessionBag()->{$form->getId()}->steps->{$form->getCurrentStep()}->{$this->getName()} = $session_value;
    }
}
