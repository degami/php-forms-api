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
 * a table field container
 */
class table_container extends fields_container_multiple{

  /**
   * table header
   * @var array
   */
  protected $table_header = [];

  /**
   * attributes for TRs or TDs
   * @var array
   */
  protected $col_row_attributes = [];

  /**
   * set table header array
   * @param array $table_header table header elements array
   */
  public function set_table_header(array $table_header){
    $this->table_header = $table_header;
    return $this;
  }

  /**
   * get table header array
   * @return array table header array
   */
  public function get_table_header(){
    return $this->table_header;
  }

  /**
   * set rows / cols attributes array
   * @param array $col_row_attributes attributes array
   */
  public function set_col_row_attributes(array $col_row_attributes){
    $this->col_row_attributes = $col_row_attributes;
    return $this;
  }

  /**
   * get rows / cols attributes array
   * @return array attributes array
   */
  public function get_col_row_attributes(){
    return $this->col_row_attributes;
  }

  /**
   * add a new table row
   */
  public function add_row(){
    $this->add_partition('table_row_'.$this->num_partitions());
    return $this;
  }

  /**
   * return number of table rows
   * @return integer number of table rows
   */
  public function num_rows(){
    return $this->num_partitions();
  }


  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $table_matrix = [];
    $rows = 0;

    foreach($this->partitions as $trindex => $tr){
      $table_matrix[$rows] = [];
      $cols = 0;
      foreach ($this->get_partition_fields($trindex) as $name => $field) {
        $table_matrix[$rows][$cols] = '';
        if(isset($this->col_row_attributes[$rows][$cols])){
          if( is_array($this->col_row_attributes[$rows][$cols]) ){
            $this->col_row_attributes[$rows][$cols] = $this->get_attributes_string( $this->col_row_attributes[$rows][$cols] );
          }
          $table_matrix[$rows][$cols] = $this->col_row_attributes[$rows][$cols];
        }
        $cols++;
      }
      $rows++;
    }

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<table id=\"{$id}\"{$attributes}>\n";

    if(!empty($this->table_header) ){
      if(!is_array($this->table_header)) {
        $this->table_header = [$this->table_header];
      }

      $output .= "<thead>\n";
      foreach($this->table_header as $th){
        if(is_array($th)){
          $th_attributes = '';
          if(!empty($th['attributes'])){
            $th_attributes = $this->get_attributes_string($th['attributes']);
          }
          $output .= "<th{$th_attributes}>".$this->get_text($th['value'])."</th>";
        }else{
          $output .= "<th>".$this->get_text($th)."</th>";
        }
      }
      $output .= "</thead>\n";
    }

    $output .= "<tbody>\n";
    $rows = 0;
    foreach($this->partitions as $trindex => $tr){
      $insertorder = array_flip($this->insert_field_order[$trindex]);
      $weights = [];
      $order = [];
      foreach ($this->get_partition_fields($trindex) as $key => $elem) {
        /** @var field $elem */
        $weights[$key]  = $elem->get_weight();
        $order[$key] = $insertorder[$key];
      }
      if( count( $this->get_partition_fields($trindex) ) > 0 )
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_partition_fields($trindex));

      $output .= "<tr id=\"{$id}-row-{$trindex}\">\n";
      $cols = 0;
      foreach ($this->get_partition_fields($trindex) as $name => $field) {
        /** @var field $field */
        $fieldhtml = $field->render($form);
        if( trim($fieldhtml) != '' ){
          $td_attributes = '';
          if(!empty($table_matrix[$rows][$cols])){
            $td_attributes = $table_matrix[$rows][$cols];
          }
          $output .= "<td{$td_attributes}>".$fieldhtml."</td>\n";
        }
        $cols++;
      }
      $output .= "</tr>\n";
      $rows++;
    }
    $output .= "</tbody>\n</table>\n";

    return $output;
  }
}
