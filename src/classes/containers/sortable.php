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
use Degami\PHPFormsApi\Abstracts\Containers\sortable_container;
use Degami\PHPFormsApi\Accessories\tag_element;

/**
 * a sortable field container
 */
class sortable extends sortable_container{

  /**
   * add field to element
   * @param string  $name     field name
   * @param mixed   $field    field to add, can be an array or a field subclass
   */
  public function add_field($name, $field, $_p = NULL) {
    //force every field to have its own tab.
    $this->deltas[$name] = count($this->get_fields());
    return parent::add_field($name, $field, $this->deltas[$name]);
  }

  /**
   * remove field from form
   * @param  string $field field name
   */
  public function remove_field($name, $_p = NULL){
    parent::remove_field($name, $this->deltas['name']);
    unset($this->deltas[$name]);
    return $this;
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').sortable({
        placeholder: \"ui-state-highlight\",
        stop: function( event, ui ) {
          \$(this).find('input[type=hidden][name*=\"sortable-delta-\"]').each(function(index,elem){
            \$(elem).val(index);
          });
        }
      });");

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $handle_position = trim(strtolower($this->get_handle_position()));

    $tag = new tag_element([
      'tag' => 'div',
      'id' => $id,
      'attributes' => $this->attributes,
    ]);

    foreach($this->partitions as $partitionindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$partitionindex]);
      $weights = [];
      $order = [];

      $partition_fields = $this->get_partition_fields($partitionindex);
      foreach ($partition_fields as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $partition_fields ) > 0 ){
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
      }

      $inner = new tag_element([
        'tag' => 'div',
        'id' => $id.'-sortable-'.$partitionindex,
        'attributes' => ['class' => 'tab-inner ui-state-default'],
      ]);

      $tag->add_child($inner);

      if($handle_position != 'right'){
        $inner->add_child(new tag_element([
          'tag' => 'span',
          'attributes' => ['class' => 'ui-icon ui-icon-arrowthick-2-n-s','style' => 'display: inline-block;'],
        ]));
      }

      $inner_inline = new tag_element([
        'tag' => 'div',
        'attributes' => ['style' => 'display: inline-block;'],
      ]);
      $inner->add_child($inner_inline);

      foreach ($partition_fields as $name => $field) {
        $inner_inline->add_child($field->render($form));
      }
      $inner_inline->add_child(new tag_element([
        'tag' => 'input',
        'type' => 'hidden',
        'name' => $id.'-delta-'.$partitionindex,
        'value' => $partitionindex,
        'has_close' => FALSE,
      ]));
      if($handle_position == 'right'){
        $inner_inline->add_child(new tag_element([
          'tag' => 'span',
          'attributes' => ['class' => 'ui-icon ui-icon-arrowthick-2-n-s','style' => 'display: inline-block;float: right;'],
        ]));
      }
    }
    return $tag;
  }
}
