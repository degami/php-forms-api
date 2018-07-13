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
use Degami\PHPFormsApi\Abstracts\Base\fields_container;
use Degami\PHPFormsApi\Accessories\tag_element;

/**
 * a fieldset field container
 */
class fieldset extends fields_container {

  /**
   * collapsible flag
   * @var boolean
   */
  protected $collapsible = FALSE;

  /**
   * collapsed flag
   * @var boolean
   */
  protected $collapsed = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    static $js_collapsible_added = FALSE;
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->collapsible) {
      $this->attributes['class'] .= ' collapsible';
      if ($this->collapsed) {
        $this->attributes['class'] .= ' collapsed';
      } else {
        $this->attributes['class'] .= ' expanded';
      }

      if( !$js_collapsible_added ){
        $this->add_js("
          \$('fieldset.collapsible').find('legend').css({'cursor':'pointer'}).click(function(evt){
            evt.preventDefault();
            var \$this = \$(this);
            \$this.parent().find('.fieldset-inner').toggle( 'blind', {}, 500, function(){
              if(\$this.parent().hasClass('expanded')){
                \$this.parent().removeClass('expanded').addClass('collapsed');
              }else{
                \$this.parent().removeClass('collapsed').addClass('expanded');
              }
            });
          });
          \$('fieldset.collapsible.collapsed .fieldset-inner').hide();");
        $js_collapsible_added = TRUE;
      }
    }

    parent::pre_render($form);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    $insertorder = array_flip($this->insert_field_order);
    $weights = [];
    foreach ($this->get_fields() as $key => $elem) {
      $weights[$key]  = $elem->get_weight();
      $order[$key] = $insertorder[$key];
    }
    if( count( $this->get_fields() ) > 0 )
      array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->get_fields());

    $tag = new tag_element([
      'tag' => 'fieldset',
      'id' => $id,
      'attributes' => $this->attributes,
    ]);
    if (!empty($this->title)) {
      $tag->add_child(new tag_element([
        'tag' => 'legend',
        'text' => $this->get_text($this->title),
      ]));
    }
    $inner = new tag_element([
      'tag' => 'div',
      'attributes' => ['class' => 'fieldset-inner'],
    ]);

    foreach ($this->get_fields() as $name => $field) {
      $inner->add_child( $field->render($form) );
    }

    $tag->add_child($inner);
    return $tag;
  }
}
