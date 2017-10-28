<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

/**
 * the progressbar field class
 */
class progressbar extends markup {

  /**
   * "indeterminate progressbar" flag
   * @var boolean
   */
  protected $indeterminate = FALSE;

  /**
   * "show label" flag
   * @var boolean
   */
  protected $show_label = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    if($this->indeterminate == TRUE || !is_numeric($this->value) ){
      $this->add_js("\$('#{$id}','#{$form->get_id()}').progressbar({ value: false });");
    }else if( $this->show_label == TRUE ){
      $this->add_js("
        \$('#{$id}','#{$form->get_id()}').progressbar({ value: parseInt({$this->value}) });
        \$('#{$id} .progress-label','#{$form->get_id()}').text('{$this->value}%');
      ");
    }else{
      $this->add_js("\$('#{$id}','#{$form->get_id()}').progressbar({ value: parseInt({$this->value}) });");
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
    $attributes = $this->get_attributes();

    if($this->show_label == TRUE){
      $this->add_css("#{$form->get_id()} #{$id}.ui-progressbar {position: relative;}");
      $this->add_css("#{$form->get_id()} #{$id} .progress-label {position: absolute;left: 50%;top: 4px;}");
    }

    return "<div id=\"{$id}\"{$attributes}>".(($this->show_label == TRUE ) ? "<div class=\"progress-label\"></div>":"")."</div>\n";
  }
}
