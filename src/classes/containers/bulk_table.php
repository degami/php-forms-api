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

/**
 * a table field container
 */
class bulk_table extends table_container{

  protected $operations = [];

  /**
   * get defined operations
   * @return array $operations array of callable
   */
  public function &get_operations(){
    return $this->operations;
  }

  /**
   * add operation to operations array
   */
  public function add_operation(string $key, string $label, $operation){
    $this->operations[$key] = ['key'=>$key,'label'=>$label,'op'=>$operation];

    return $this;
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if($this->pre_rendered) return;
    $id = $this->get_html_id();
    $this->set_table_header( array_merge(['&nbsp;'], $this->get_table_header()) );
    for( $i = 0;$i < $this->num_rows(); $i++ ){
      foreach ($this->get_partition_fields($i) as $key => $field) {
        $field->set_name( $this->get_name()."[rows][$i][{$field->get_name()}]" );
      }
      $this->add_field( $this->get_name()."[rows][$i][row_enabled]", array(
        'type' => 'checkbox',
        'value' => 0,
        'default_value' => 1,
        'attributes' => [
          'class' => 'checkbox-row',
        ],
        'weight' => -100,
      ), $i);
    }

    $this->add_js( "\$('.btn.selAll','#{$id}_actions').click(function(evt){evt.preventDefault(); \$('.checkbox-row','#{$id}').each(function(index,elem){ $(elem)[0].checked = true; }); });" );
    $this->add_js( "\$('.btn.deselAll','#{$id}_actions').click(function(evt){evt.preventDefault(); \$('.checkbox-row','#{$id}').each(function(index,elem){ $(elem)[0].checked = false; }); });" );
    $this->add_js( "\$('.btn.inverSel','#{$id}_actions').click(function(evt){evt.preventDefault(); \$('.checkbox-row','#{$id}').each(function(index,elem){ \$(elem)[0].checked = !\$(elem)[0].checked; }); });" );

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $prefix = "<div><select name=\"{$this->get_name()}[op]\">";
    foreach($this->get_operations() as $operation){
      $prefix .= "<option value=\"{$operation['key']}\">{$operation['label']}</option>";
    }
    $prefix .= "</select></div>";

    $suffix="<div class=\"bulk_actions\" id=\"{$id}_actions\">";
    $suffix.="<a href=\"#\" class=\"btn selAll\">".$this->get_text('Select all')."</a> - ";
    $suffix.="<a href=\"#\" class=\"btn deselAll\">".$this->get_text('Deselect all')."</a> -";
    $suffix.="<a href=\"#\" class=\"btn inverSel\">".$this->get_text('Invert selection')."</a>";
    $suffix.="</div>";

    $out = parent::render_field($form);
    return $prefix.$out.$suffix;
  }

  public function process($values){
    foreach($values[$this->get_name()]['rows'] as $k => $row){
      if(!isset($row['row_enabled']) || $row['row_enabled'] != 1 ){
        unset($values[$this->get_name()]['rows'][$k]);
      } else {
        unset($values[$this->get_name()]['rows'][$k]['row_enabled']);
      }
    }

    $operation_key = $values[$this->get_name()]['op'];
    $callable = $this->operations[ $operation_key ]['op'];
    foreach($values[$this->get_name()]['rows'] as $args){
      call_user_func_array($callable, $args);
    }

    parent::process( $values );
  }  
}
