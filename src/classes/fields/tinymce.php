<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\form;
use Degami\PHPFormsApi\Base\field;
use \stdClass;

/**
 * tinymce beautified textarea
 */
class tinymce extends textarea {
  /**
   * tinymce options
   * @var array
   */
  private $tinymce_options = [];

  /**
   * get tinymce options array
   * @return array tinymce options
   */
  public function &get_tinymce_options(){
    return $this->tinymce_options;
  }

  /**
   * set tinymce options array
   * @param array $options array of valid tinymce options
   */
  public function set_tinymce_options($options){
    $options = (array) $options;
    $options = array_filter($options, [$this,'is_valid_tinymce_option']);
    $this->tinymce_options = $options;

    return $this;
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->tinymce_options['selector'] = "#{$id}";
    $tinymce_options = new stdClass;
    foreach ($this->tinymce_options as $key => $value) {
      if( ! $this->is_valid_tinymce_option($key) ) continue;
      $tinymce_options->$key = $value;
    }
    $this->add_js("tinymce.init(".json_encode($tinymce_options).");");
    parent::pre_render($form);
  }

  /**
   * filters valid tinymce options
   * @param  string  $propertyname property name
   * @return boolean               TRUE if is a valid tinymce option
   */
  private function is_valid_tinymce_option($propertyname){
    // could be used to filter elements
    return TRUE;
  }
}
