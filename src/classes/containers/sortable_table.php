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

      if($handle_position != 'right'){
        $thead->add_child(new tag_element([
          'tag' => 'th', 'text' => '&nbsp;',
        ]));
      }
      foreach($this->table_header as $th){
        $thead->add_child(new tag_element([
          'tag' => 'th',
          'text' => $this->get_text($th),
        ]));
      }
      if($handle_position == 'right'){
        $thead->add_child(new tag_element([
          'tag' => 'th', 'text' => '&nbsp;',
        ]));
      }
    }

    $tbody = new tag_element([
      'tag' => 'tbody',
    ]);
    $tag->add_child($tbody);

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

      $trow = new tag_element([
        'tag' => 'tr',
        'id' => $id.'-sortable-'.$trindex,
        'attributes' => [ 'class' => 'tab-inner ui-state-default'],
      ]);
      $tbody->add_child($trow);

      if($handle_position != 'right'){
        $td = new tag_element([
          'tag' => 'td',
          'attributes' => [  'width' => 16, 'style' => 'width: 16px;'],
          'children' => [ 
            new tag_element([
              'tag' => 'span',
              'attributes' => ['class' => 'ui-icon ui-icon-arrowthick-2-n-s','style' => 'display: inline-block;'],
            ]) 
          ],
        ]);       
        $trow->add_child($td);
      }

      foreach ($partition_fields as $name => $field) {
        /** @var field $field */
        $fieldhtml = $field->render($form);
        if( trim($fieldhtml) != '' ){
          $trow->add_child(new tag_element([
            'tag' => 'td',
            'children' => [ $fieldhtml ],
          ]));
        }
      }

      $trow->add_child(new tag_element([
        'tag' => 'input',
        'type' => 'hidden',
        'name' => $id.'-delta-'.$trindex,
        'value' => $trindex,
        'has_close' => FALSE,
      ]));
      if($handle_position == 'right'){
        $td = new tag_element([
          'tag' => 'td',
          'attributes' => [  'width' => 16, 'style' => 'width: 16px;'],
          'children' => [
            new tag_element([
              'tag' => 'span',
              'attributes' => ['class' => 'ui-icon ui-icon-arrowthick-2-n-s','style' => 'display: inline-block;'],
            ])
          ],
        ]);
        $trow->add_child($td);
      }
    }

    return $tag;
  }
}
