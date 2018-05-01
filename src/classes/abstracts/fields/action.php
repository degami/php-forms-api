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
 * the "actionable" field element class (a button, a submit or a reset)
 * @abstract
 */
abstract class action extends field{

  /**
   * "use jqueryui button method on this element" flag
   * @var boolean
   */
  protected $js_button = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    if($this->js_button == TRUE){
      $id = $this->get_html_id();
      $this->add_js("\$('#{$id}','#{$form->get_id()}').button();");
    }
    parent::pre_render($form);
  }

  /**
   * is_a_value hook
   * @return boolean this is not a value
   */
  public function is_a_value(){
    return FALSE;
  }

  /**
   * validate function
   * @return boolean this field is always valid
   */
  public function valid() {
    return TRUE;
  }

}
