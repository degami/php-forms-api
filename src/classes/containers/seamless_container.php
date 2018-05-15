<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi\Containers;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Abstracts\Base\fields_container;

/**
 * an hidden field container
 */
class seamless_container extends fields_container {

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $output = "";

    $insertorder = array_flip($this->insert_field_order);
    $weights = [];
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    if( count( $this->get_fields() ) > 0 )
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());
    foreach ($this->get_fields() as $name => $field) {
      $output .= $field->render($form);
    }

    return $output;
  }
}
