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
use Degami\PHPFormsApi\Abstracts\Fields\composed_field;

/**
 * the geolocation field class
 */
class geolocation extends composed_field {

  /**
   * latitude
   * @var float
   */
  protected $latitude;

  /**
   * longitude
   * @var float
   */
  protected $longitude;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options,$name);

    $defaults = isset($options['default_value']) ? $options['default_value'] : ['latitude' => 0, 'longitude' => 0];

    unset($options['title']);
    unset($options['prefix']);
    unset($options['suffix']);
    $options['container_tag'] = '';

    if(!isset($options['size']))
      $options['size'] = 5;

    $options['type'] = 'textfield';
    $options['suffix'] = $this->get_text('latitude').' ';
    $options['default_value'] = (is_array($defaults) && isset($defaults['latitude'])) ? $defaults['latitude'] : 0;
    $this->latitude = new textfield($options,$name.'_latitude');

    $options['type'] = 'textfield';
    $options['suffix'] = $this->get_text('longitude').' ';
    $options['default_value'] = (is_array($defaults) && isset($defaults['longitude'])) ? $defaults['longitude'] : 0;
    $this->longitude = new textfield($options,$name.'_longitude');
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    parent::pre_render($form);

    $this->latitude->pre_render($form);
    $this->longitude->pre_render($form);
  }

  /**
   * preprocess hook . it simply calls the sub elements preprocess
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    $this->latitude->preprocess($process_type);
    $this->longitude->preprocess($process_type);
  }

  /**
   * process hook . it simply calls the sub elements process
   * @param  array $values value to set
   */
  public function process($values) {
    $this->latitude->process($values[$this->get_name().'_latitude']);
    $this->longitude->process($values[$this->get_name().'_longitude']);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    return $this->latitude->valid() && $this->longitude->valid();
  }


  /**
   * renders form errors
   * @return string errors as an html <li> list
   */
  public function show_errors() {
    return (trim($this->latitude->show_errors() . $this->longitude->show_errors()) == '') ? '' : trim($this->latitude->show_errors() . $this->longitude->show_errors());
  }


  /**
   * resets the sub elements
   */
  public function reset() {
    $this->latitude->reset();
    $this->longitude->reset();
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $attributes = $this->get_attributes();

    $this->tag = 'div';
    $output = "<{$this->tag} id=\"{$id}\"{$attributes}>\n";

    $required = ($this->validate->has_value('required')) ? '<span class="required">*</span>' : '';
    $requiredafter = $requiredbefore = $required;
    if($this->required_position == 'before') { $requiredafter = ''; $requiredbefore = $requiredbefore.' '; }
    else { $requiredbefore = ''; $requiredafter = ' '.$requiredafter; }

    if(!empty($this->title)){
      if ( $this->tooltip == FALSE ) {
        $this->label_class .= " label-" .$this->get_element_class_name();
        $this->label_class = trim($this->label_class);
        $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
        $output .= "<label for=\"{$id}\"{$label_class}>{$requiredbefore}".$this->get_text($this->title)."{$requiredafter}</label>\n";
      } else {
        if( !in_array('title', array_keys($this->attributes)) ){
          $this->attributes['title'] = strip_tags($this->get_text($this->title).$required);
        }

        $id = $this->get_html_id();
        $form->add_js("\$('#{$id}','#{$form->get_id()}').tooltip();");
      }
    }
    $output .= $this->latitude->render($form);
    $output .= $this->longitude->render($form);
    $output .= "</{$this->tag}>\n";
    return $output;
  }

  /**
   * return field value
   * @return array field value
   */
  public function values() {
    return [
      'latitude'=> $this->latitude->values(),
      'longitude'=> $this->longitude->values(),
    ];
  }
}
