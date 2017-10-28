<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####              FIELD CONTAINERS                   ####
   ######################################################### */

namespace Degami\PHPFormsApi;

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
  public function __construct($options = array(),$name = NULL){
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
    $attributes = $this->get_attributes();
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $insertorder = array_flip($this->insert_field_order);
    $weights = array();
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    if( count( $this->get_fields() ) > 0 )
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());
    foreach ($this->get_fields() as $name => $field) {
      $output .= $field->render($form);
    }
    $output .= "</{$this->tag}>\n";

    return $output;
  }
}
