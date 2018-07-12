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
use Degami\PHPFormsApi\Accessories\tag_element;

/**
 * a field container that can specify container's html tag
 */
class tag_container extends fields_container {
  /**
   * container html tag
   * @var string
   */
  protected $tag = 'div';

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [],$name = NULL){
    parent::__construct($options,$name);

    if($this->attributes['class'] == 'tag_container'){ // if set to the default
      $this->attributes['class'] = $this->tag.'_container';
    }
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $tag = new tag_element([
      'tag' => $this->tag,
      'id' => $id,
      'attributes' => $this->attributes,
      'has_close' => TRUE,
      'value_needed' => FALSE,
    ]);

    $insertorder = array_flip($this->insert_field_order);
    $weights = [];
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    if( count( $this->get_fields() ) > 0 )
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());
    foreach ($this->get_fields() as $name => $field) {
      $tag->add_child( $field->render($form) );
    }
    return $tag;
  }
}
