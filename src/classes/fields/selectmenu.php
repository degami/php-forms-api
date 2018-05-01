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
 * the "selectmenu" select field class
 */
class selectmenu extends select{

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').selectmenu({width: 'auto' });");

    parent::pre_render($form);
  }
}
