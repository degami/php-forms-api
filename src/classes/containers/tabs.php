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
 * a "tabbed" field container
 */
class tabs extends fields_container_multiple {

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("\$('#{$id}','#{$form->get_id()}').tabs();");

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

    $tab_links = new tag_element([
      'tag' => 'ul',
    ]);

    $tag->add_child($tab_links);
    
    foreach($this->partitions as $tabindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$tabindex]);
      $weights = [];
      $order = [];

      $partition_fields = $this->get_partition_fields($tabindex);

      foreach ($partition_fields as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($tabindex) ) > 0 ){
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
      }

      $addclass_tab = ' class="tabel '.( $this->partition_has_errors($tabindex, $form) ? 'has-errors' : '' ).'"';
      $tab_links->add_child("<li{$addclass_tab}><a href=\"#{$id}-tab-inner-{$tabindex}\">".$this->get_text($this->partitions[$tabindex]['title'])."</a></li>");

      $inner = new tag_element([
        'tag' => 'div',
        'id' => $id.'-tab-inner-'.$tabindex,
        'attributes' => ['class' => 'tab-inner'.( $this->partition_has_errors($tabindex, $form) ? ' has-errors' : '' )],
      ]);

      foreach ($partition_fields as $name => $field) {
        $inner->add_child($field->render($form));
      }
      $tag->add_child($inner);
    }
    
    return $tag;
  }

  public function add_tab($title){
    return $this->add_partition($title);
  }
}
