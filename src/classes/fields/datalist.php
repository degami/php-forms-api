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
use Degami\PHPFormsApi\Abstracts\Base\field;
use Degami\PHPFormsApi\Accessories\tag_element;
use Degami\PHPFormsApi\Abstracts\Fields\field_multivalues;

/**
* the "autocomplete" text input field class
*/
class datalist extends field_multivalues{
  /**
  * class constructor
  * @param array  $options build options
  * @param string $name    field name
  */
  public function __construct($options,$name) {

    if(isset($options['options'])){
      foreach($options['options'] as $k => $o){
        if( $o instanceof option ){
          $o->set_parent($this);
          $this->options[] = $o;
        }else{
          $option = new option( $o , $o );
          $option->set_parent($this);
          $this->options[] = $option;
        }
      }
      unset($options['options']);
    }

    if(isset($options['default_value'])){
      if(is_array($options['default_value'])) $options['default_value'] = reset($options['default_value']);
      $options['default_value'] = "".$options['default_value'];
    }

    parent::__construct($options,$name);
  }

  /**
  * render_field hook
  * @param  form $form form object
  * @return string        the element html
  */
  public function render_field(form $form) {
    $id = $this->get_html_id();

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    if( is_array($this->value) ) $this->value = '';

    $output = "";
    $tag = new tag_element([
      'tag' => 'input',
      'type' => 'text',
      'id' => $id,
      'name' => $this->name,
      'value' => htmlspecialchars($this->value),
      'attributes' => $this->attributes + ['size' => $this->size, 'list' => $this->name."-data"],
    ]);
    $output .= $tag->render_tag();

    $tag = new tag_element([
      'tag' => 'datalist',
      'type' => null,
      'id' => $this->name.'-data',
      'value_needed' => FALSE,
      'has_close' => TRUE,
    ]);
    foreach ($this->options as $key => $opt) {
      $tag->add_child( new tag_element([
        'tag' => 'option',
        'type' => null,
        'value' => $opt->get_key(),
        'text' => $this->get_text($opt->get_label()),
        'has_close' => TRUE,
      ]) );
    }
    $output .= $tag->render_tag();

    return $output;
  }
}
