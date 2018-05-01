<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Abstracts\Base\field;

/**
 * the spinner number input field class
 */
class spinner extends number {
  
  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    $js_options = '';
    if( is_numeric($this->min) && is_numeric($this->max) && $this->max >= $this->min ){
      $js_options = "{min: $this->min, max: $this->max, step: $this->step}";
    }

    $this->add_js("\$('#{$id}','#{$form->get_id()}').attr('type','text').spinner({$js_options});");

    parent::pre_render($form);
  }

}
