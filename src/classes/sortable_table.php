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
 * a sortable table rows field container
 */
class sortable_table extends sortable_container{

  /**
   * table header
   * @var array
   */
  protected $table_header = [];

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_js("
      \$('#{$id} tbody','#{$form->get_id()}').sortable({
        helper: function(e, ui) {
          ui.children().each(function() {
            \$(this).width($(this).width());
          });
          return ui;
        },
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

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<table id=\"{$id}\"{$attributes}>\n";

    if(!empty($this->table_header) ){
      if(!is_array($this->table_header)) {
        $this->table_header = [$this->table_header];
      }

      $output .= "<thead>\n";
      if($handle_position != 'right') $output .= "<th>&nbsp;</th>";
      foreach($this->table_header as $th){
        $output .= "<th>".$this->get_text($th)."</th>";
      }
      if($handle_position == 'right') $output .= "<th>&nbsp;</th>";
      $output .= "</thead>\n";
    }

    $output .= "<tbody>\n";
    foreach($this->partitions as $trindex => $tr){
      $insertorder = array_flip($this->insert_field_order[$trindex]);
      $weights = [];
      $order = [];

      $partition_fields = $this->get_partition_fields($trindex);

      foreach ($partition_fields as $key => $elem) {
        /** @var field $elem */
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $partition_fields ) > 0 ){
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);        
      }

      $output .= "<tr id=\"{$id}-sortable-{$trindex}\"  class=\"tab-inner ui-state-default\">\n".(($handle_position == 'right') ? '' : "<td width=\"16\" style=\"width: 16px;\"><span class=\"ui-icon ui-icon-arrowthick-2-n-s\"></span></td>")."\n";
      foreach ($partition_fields as $name => $field) {
        /** @var field $field */
        $fieldhtml = $field->render($form);
        if( trim($fieldhtml) != '' )
          $output .= "<td>".$fieldhtml."</td>\n";
      }
      $output .= "<input type=\"hidden\" name=\"{$id}-delta-{$trindex}\" value=\"{$trindex}\" />\n";
      $output .= (($handle_position == 'right') ? "<td width=\"16\" style=\"width: 16px;\"><span class=\"ui-icon ui-icon-arrowthick-2-n-s\"></span></td>" : '')."</tr>\n";
    }
    $output .= "</tbody>\n</table>\n";

    return $output;
  }
}
