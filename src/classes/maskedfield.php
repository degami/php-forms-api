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
 * the "masked" text input field class
 */
class maskedfield extends textfield{

  /**
   * input mask string
   * @var string
   */
  protected $mask;

  /**
   * jQuery Mask Plugin patterns
   * @var array
   */
  private $translation = array(
    '0'  =>  "\d",
    '9'  =>  "\d?",
    '#'  =>  "\d+",
    'A'  =>  "[a-zA-Z0-9]",
    'S'  =>  "[a-zA-Z]",
  );

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options, $name = NULL){
    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' maskedfield';

    parent::__construct($options, $name);
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').mask('{$this->mask}');");
    parent::pre_render($form);
  }

  /**
   * validate hook
   * @return boolean this TRUE if this element conforms to mask
   */
  public function valid() {
    $mask = $this->mask;
    $mask = preg_replace("(\[|\]|\(|\))","\\\1",$mask);
    foreach($this->translation as $search => $replace){
      $mask = str_replace($search, $replace, $mask);
    }
    $mask = '/^'.$mask.'$/';
    if(!preg_match($mask,$this->value)){
      $this->add_error($this->get_text("Value does not conform to mask"),__FUNCTION__);

      if($this->stop_on_first_error)
        return FALSE;
    }

    return parent::valid();
  }
}
