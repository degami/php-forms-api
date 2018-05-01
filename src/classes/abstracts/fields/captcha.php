<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Abstracts\Base\field;

/**
 * the captcha field class
 */
 abstract class captcha extends field{

   /**
    * "already validated" flag
    * @var boolean
    */
   protected $already_validated = FALSE;

   /**
    * process hook
    * @param  mixed $values value to set
    */
   public function process($values){
     parent::process($values);
     if(isset($values['already_validated'])) $this->already_validated = $values['already_validated'];
   }

   /**
    * check if element is already validated
    * @return boolean TRUE if element has already been validated
    */
   public function is_already_validated(){
     return $this->already_validated;
   }

   /**
    * is_a_value hook
    * @return boolean this is not a value
    */
   public function is_a_value(){
     return FALSE;
   }

   /**
    * after_validate hook
    * @param  form $form form object
    */
   public function after_validate(form $form){
     $_SESSION[$form->get_id()]['steps'][$form->get_current_step()][$this->get_name()] = $this->values();
     $_SESSION[$form->get_id()]['steps'][$form->get_current_step()][$this->get_name()]['already_validated'] = $this->is_already_validated();
   }
 }
