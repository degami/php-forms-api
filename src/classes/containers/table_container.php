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

    $tag = new tag_element([
      'tag' => 'table',
      'id' => $id,
      'attributes' => $this->attributes,
    ]);

    if(!empty($this->table_header) ){
      if(!is_array($this->table_header)) {
        $this->table_header = [$this->table_header];
      }

      $thead = new tag_element([
        'tag' => 'thead',
      ]);
      $tag->add_child($thead);

      foreach($this->table_header as $th){
        if(is_array($th)){
          $thead->add_child(new tag_element([
            'tag' => 'th',
            'text' => $this->get_text($th['value']),
            'attributes' => $th['attributes'],
          ]));
        }else{
          $thead->add_child(new tag_element([
            'tag' => 'th',
            'text' => $this->get_text($th),
          ]));
        }
      }
    }

    $tbody = new tag_element([
      'tag' => 'tbody',
    ]);
    $tag->add_child($tbody);

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
      if( count( $this->get_partition_fields($trindex) ) > 0 ){
        $partition_fields = $this->get_partition_fields($trindex);
        array_multisort($weights, SORT_ASC, $order, SORT_ASC, $partition_fields);
        $this->set_partition_fields($partition_fields, $trindex);
      }

      $trow = new tag_element([
        'tag' => 'tr',
        'id' => $id.'-row-'.$trindex,
      ]);
      $tbody->add_child($trow);

      $cols = 0;
      foreach ($this->get_partition_fields($trindex) as $name => $field) {
        /** @var field $field */
        $fieldhtml = $field->render($form);
        if( trim($fieldhtml) != '' ){
          $td_attributes = '';
          if(!empty($table_matrix[$rows][$cols])){
            $td_attributes = $table_matrix[$rows][$cols];
          }
          $trow->add_child("<td{$td_attributes}>".$fieldhtml."</td>\n");
        }
        $cols++;
      }
      $rows++;
    }

    return $tag;
  }
}
