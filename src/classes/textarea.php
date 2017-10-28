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
 * the textarea field class
 */
class textarea extends field {

  /**
   * rows
   * @var integer
   */
  protected $rows = 5;

  /**
   * resizable flag
   * @var boolean
   */
  protected $resizable = FALSE;

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    if($this->resizable == TRUE){
      $this->add_js("\$('#{$id}','#{$form->get_id()}').resizable({handles:\"se\"});");
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

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    $errors = $this->get_errors();
    if (!empty($errors)) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes(array('name','id','value','rows','cols'));
    $output = "<textarea id=\"{$id}\" name=\"{$this->name}\" cols=\"{$this->size}\" rows=\"{$this->rows}\"{$attributes}>\n".$this->value."</textarea>";
    return $output;
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}
