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

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    $tabs_html = array();
    $tab_links = array();
    foreach($this->partitions as $tabindex => $tab){
      $insertorder = array_flip($this->insert_field_order[$tabindex]);
      $weights = array();
      $order = array();
      foreach ($this->get_partition_fields($tabindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($tabindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($tabindex));

      $addclass_tab = ' class="tabel '.( $this->partition_has_errors($tabindex, $form) ? 'has-errors' : '' ).'"';
      $tab_links[$tabindex] = "<li{$addclass_tab}><a href=\"#{$id}-tab-inner-{$tabindex}\">".$this->get_text($this->partitions[$tabindex]['title'])."</a></li>";
      $tabs_html[$tabindex] = "<div id=\"{$id}-tab-inner-{$tabindex}\" class=\"tab-inner".( $this->partition_has_errors($tabindex, $form) ? ' has-errors' : '' )."\">\n";
      foreach ($this->get_partition_fields($tabindex) as $name => $field) {
        $tabs_html[$tabindex] .= $field->render($form);
      }
      $tabs_html[$tabindex] .= "</div>\n";
    }
    $output .= "<ul>".implode("",$tab_links)."</ul>".implode("",$tabs_html). "</div>\n";

    return $output;
  }

  public function add_tab($title){
    return $this->add_partition($title);
  }
}
