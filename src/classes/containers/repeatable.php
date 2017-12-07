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
use Degami\PHPFormsApi\Base\fields_container;
use Degami\PHPFormsApi\Fields\datetime;
use Degami\PHPFormsApi\Fields\geolocation;
use \Exception;

/**
 * a field container with a repeatable group of fields
 */
class repeatable extends fields_container_multiple {

  protected $num_reps = 1;

  private $repetable_fields = [];
  private $repetable_insert_field_order = [];

  public function __construct(array $options = [], $name = NULL) {
    parent::__construct($options, $name);
  }


  /**
   * Override add_field
   * @param string $name
   * @param mixed $field
   *
   * @return $this|mixed
   */
  public function add_field($name, $field, $partitions_index = 0) {
    $field = $this->get_field_obj($name, $field);

    if( $this->is_field_container($field) ) {
      throw new Exception('Can\'t nest field_containers into repeteables');
    }

    $this->repetable_fields[$name] = $field;
    $this->repetable_insert_field_order[] = $name;

    if( !method_exists($field, 'on_add_return') ) {
      if(  $this->is_field_container($field) )
        return $field;
      return $this;
    }
    if($field->on_add_return() == 'this') return $field;
    return $this;
  }

  /**
   * Override remove_field
   * @param $name
   *
   * @return $this
   */
  public function remove_field($name, $partitions_index = 0) {
    unset($this->repetable_fields[$name]);
    if(($key = array_search($name, $this->repetable_insert_field_order)) !== false) {
      unset($this->repetable_insert_field_order[$key]);
    }
    return $this;
  }

  public function alter_request(&$request) {
    $id = $this->get_html_id();
    if( isset( $request[ $id.'-numreps' ] ) ){
      $this->num_reps = (int) $request[ $id.'-numreps' ];
      if( $this->num_reps <= 0 ) {
        $this->num_reps = 1;
      }
    }
    for( $i = 0 ; $i < $this->num_reps; $i++ ){
      foreach ($this->repetable_fields as $rfield){
        /** @var \Degami\PHPFormsApi\Base\field $field */
        $field = clone $rfield;
        $field
          ->set_id($this->get_name().'_'.$i.'_'.$field->get_name())
          ->set_name( $this->get_name().'['.$i.']['.$field->get_name().']' );
        parent::add_field($field->get_name(), $field, $i);
      }
    }
    parent::alter_request($request);
  }

  public function process($values) {
    $valuestoprocess = array_values($values[ $this->get_name() ]);

    foreach($this->get_fields() as $i => $field){
      /** @var \Degami\PHPFormsApi\Base\field $field */
      $matches = NULL;
      if( preg_match("/".$this->get_name()."\[([0-9]+)\]\[(.*?)\]/", $field->get_name(), $matches) ){
        if( isset( $valuestoprocess[ $matches[1] ][ $matches[2] ] ) ){
          $field->process( $valuestoprocess[ $matches[1] ][ $matches[2] ] );
        }
      }
    }
    //parent::process($values);
  }

  public function get_value() {
    $out = [];
    foreach($this->get_fields() as $i => $field){
      /** @var \Degami\PHPFormsApi\Base\field $field */
      if($field->is_a_value() == TRUE){
        $matches = NULL;
        if( preg_match("/".$this->get_name()."\[([0-9]+)\]\[(.*?)\]/", $field->get_name(), $matches) ){
          $out[ $matches[1] ][ $matches[2] ] = $field->get_value();
        }
      }
    }
    return $out;
  }

  public function values() {
    return $this->get_value();
  }


  public function pre_render(form $form) {
    if(!$this->pre_rendered){
      $id = $this->get_html_id();

      $repetatable_fields = "<div id=\"{$id}-row-{x}\">\n<div class=\"repeatable-row\">";
      $fake_form = new form();
      foreach($this->repetable_fields as $rfield){
        /** @var \Degami\PHPFormsApi\Base\field $field */
        $field = clone $rfield;
        $field
          ->set_id($this->get_name().'_{x}_'.$field->get_name())
          ->set_name( $this->get_name().'[{x}]['.$field->get_name().']' );
        $repetatable_fields .= $field->render($fake_form);
      }
      $repetatable_fields .= "<a href=\"#\" class=\"remove-btn btn\" name=\"{$id}-remove-{x}\">&times;</a>\n";
      $repetatable_fields .= "</div></div>";
      $repetatable_fields = str_replace("\n","", $repetatable_fields);

      $js = array_filter(array_map('trim', $fake_form->get_js() ));
      if( !empty( $js ) ){
        foreach($js as &$js_string){
          if($js_string[strlen($js_string)-1] == ';'){
            $js_string = substr($js_string,0,strlen($js_string)-1);
          }
        }
      }
      if( !empty($js) ){
        $js = "eval( ".implode(";",$js).".replace( new RegExp('\{x\}', 'g'), newrownum )  );\n";
      }else{
        $js = '';
      }

      $this->add_css("#{$id} .repeatable-row{ margin: 10px 0; padding: 10px; border: solid 1px #cecece; position: relative; }");
      $this->add_css("#{$id} .repeatable-row .remove-btn{ position: absolute; top: 5px; right: 10px; z-index: 10;}");

      $this->add_js("\$('#{$id}').delegate('.remove-btn','click',function(evt){
        evt.preventDefault();
        \$(this).closest('.repeatable-row').remove();
        var \$target = $('.fields-target:eq(0)');
        var newrownum = \$target.find('.repeatable-row').length;
        \$('input[name=\"{$id}-numreps\"]').val(newrownum);
      });");
      $this->add_js("\$('.btnaddmore', '#{$id}').click(function(evt){
        evt.preventDefault();
        var \$target = \$('.fields-target:eq(0)');
        \$( '{$repetatable_fields}'.replace( new RegExp('\{x\}', 'g'), newrownum ) ).appendTo( \$target );
        var newrownum = \$target.find('.repeatable-row').length;
        \$('input[name=\"{$id}-numreps\"]').val(newrownum);
        {$js}
      });");
    }

    return parent::pre_render($form);
  }


  public function render_field(form $form) {
    $id = $this->get_html_id();

    $output = '';
    $attributes = $this->get_attributes();

    $output .= "<div id=\"{$id}\"{$attributes}><div class=\"fields-target\">\n";

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

      $output .= "<div id=\"{$id}-row-{$partitionindex}\">\n<div class=\"repeatable-row\">\n";
      $output .= "<input type=\"hidden\" name=\"{$id}-numreps\" value=\"{$this->num_reps}\" />\n";
      foreach ($partition_fields as $name => $field) {
        $output .= $field->render($form);
      }
      $output .= "<a href=\"#\" class=\"remove-btn btn\" name=\"{$id}-remove-{$partitionindex}\">&times;</a>\n";
      $output .= "</div></div>\n";
    }

    $output .= "</div><button class=\"btn btnaddmore\" id=\"{$id}-btn-addmore\">".$this->get_text('+')."</button>";
    $output .= "</div>\n";

    return $output;
  }

}
