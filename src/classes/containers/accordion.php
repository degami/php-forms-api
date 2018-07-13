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
use Degami\PHPFormsApi\Abstracts\Containers\fields_container_multiple;
use Degami\PHPFormsApi\Accessories\tag_element;

/**
 * an accordion field container
 */
class accordion extends fields_container_multiple {

  /**
   * height style
   * @var string
   */
  protected $height_style = 'auto';

  /**
   * active tab
   * @var numeric
   */
  protected $active = '0';


  /**
   * collapsible
   * @var boolean
   */
  protected $collapsible = FALSE;


  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $collapsible = ($this->collapsible) ? 'true':'false';
    $this->add_js("\$('#{$id}','#{$form->get_id()}').accordion({  heightStyle: \"{$this->height_style}\", active: {$this->active}, collapsible: {$collapsible} });");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $tag = new tag_element([
      'tag' => 'div',
      'id' => $id,
      'attributes' => $this->attributes,
    ]);

    foreach($this->partitions as $accordionindex => $accordion){
      $insertorder = array_flip($this->insert_field_order[$accordionindex]);
      $weights = [];
      $order = [];

      $partition_fields = $this->get_partition_fields($accordionindex);

      foreach ($partition_fields as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $partition_fields ) > 0 ){
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
      }

      $tag->add_child(new tag_element([
        'tag' => 'h3',
        'text' => $this->get_text($this->partitions[$accordionindex]['title']),
        'attributes' => ['class' => 'tabel '.( $this->partition_has_errors($accordionindex, $form) ? 'has-errors' : '' )],
      ]));

      $inner = new tag_element([
        'tag' => 'div',
        'id' => $id.'-tab-inner-'.$accordionindex,
        'attributes' => ['class' => 'tab-inner'.( $this->partition_has_errors($accordionindex, $form) ? ' has-errors' : '' )],
      ]);

      foreach ($partition_fields as $name => $field) {
        $inner->add_child( $field->render($form) );
      }
      $tag->add_child($inner);
    }

    return $tag;
  }

  public function add_accordion($title){
    return $this->add_partition($title);
  }
}
