<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

/**
 * the recaptcha field class
 */
class recaptcha extends field {

  /**
   * public key
   * @var string
   */
  protected $publickey = '';

  /**
   * private key
   * @var string
   */
  protected $privatekey = '';

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
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    if(!function_exists('recaptcha_get_html')) return '';
    return recaptcha_get_html($this->publickey);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    if($this->already_validated == TRUE) return TRUE;
    if(isset($this->value['already_validated']) && $this->value['already_validated'] == TRUE) return TRUE;
    if(!function_exists('recaptcha_check_answer')){
      $this->already_validated = TRUE;
      return TRUE;
    }

    if(!is_array($this->value)) $this->value = array();

    // if something is missing...
    $this->value += array(
      'challenge_field' => '',
      'response_field' => '',
    );

    $resp = recaptcha_check_answer ($this->privatekey,
      $_SERVER["REMOTE_ADDR"],
      $this->value["challenge_field"],
      $this->value["response_field"]);
    if(!$resp->is_valid){
      $this->add_error($this->get_text("Recaptcha response is not valid"), __FUNCTION__);
    }else{
      $this->already_validated = TRUE;
      $this->value['already_validated'] = TRUE;
    }

    return $resp->is_valid;
  }

  /**
   * is_a_value hook
   * @return boolean this is not a value
   */
  public function is_a_value(){
    return FALSE;
  }

  /**
   * alter_request hook
   * @param array $request request array
   */
  public function alter_request(&$request){
    foreach($request as $key => $val){
      //RECAPTCHA HANDLE
      if( preg_match('/^recaptcha\_(challenge|response)\_field$/',$key,$matches) ){
        $fieldname = $this->get_name();
        if(!empty($request["recaptcha_challenge_field"])){
          $request[$fieldname]["challenge_field"] = $request["recaptcha_challenge_field"];
          unset($request["recaptcha_challenge_field"]);
        }
        if(!empty($request["recaptcha_response_field"])){
          $request[$fieldname]["response_field"] = $request["recaptcha_response_field"];
          unset($request["recaptcha_response_field"]);
        }
      }
    }
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
