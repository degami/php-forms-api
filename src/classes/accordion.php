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

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}>\n";

    foreach($this->partitions as $accordionindex => $accordion){
      $insertorder = array_flip($this->insert_field_order[$accordionindex]);
      $weights = [];
      $order = [];
      foreach ($this->get_partition_fields($accordionindex) as $key => $elem) {
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($accordionindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($accordionindex));


      $addclass_tab = ' class="tabel '.( $this->partition_has_errors($accordionindex, $form) ? 'has-errors' : '' ).'"';
      $output .= "<h3{$addclass_tab}>".$this->get_text($this->partitions[$accordionindex]['title'])."</h3>";
      $output .= "<div id=\"{$id}-tab-inner-{$accordionindex}\" class=\"tab-inner".( $this->partition_has_errors($accordionindex, $form) ? ' has-errors' : '' )."\">\n";
      foreach ($this->get_partition_fields($accordionindex) as $name => $field) {
        $output .= $field->render($form);
      }
      $output .= "</div>\n";
    }
    $output .= "</div>\n";

    return $output;
  }

  public function add_accordion($title){
    return $this->add_partition($title);
  }
}
