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
 * the datetime select group field class
 */
class datetime extends tag_container {

  /**
   * date sub element
   * @var date
   */
  protected $date = NULL;

  /**
   * time sub_element
   * @var time
   */
  protected $time = NULL;

  /**
   * "use js selects" flag
   * @var boolean
   */
  protected $js_selects = FALSE;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    parent::__construct($options,$name);

    unset($options['title']);
    $options['container_tag'] = '';

    $options['type'] = 'date';
    $this->date = new date($options,$name.'_date');

    $options['type'] = 'time';
    $this->time = new time($options,$name.'_time');
  }

  /**
   * pre_render hook
   * @param  form $form form object
   */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();
    $this->add_css("#{$id} div.date,#{$id} div.time{display: inline-block;margin-right: 5px;}");

    $this->date->pre_render($form);
    $this->time->pre_render($form);

    foreach( $this->date->get_js() as $date_js_line ){
      if(!empty($date_js_line)){
          $this->add_js($date_js_line);
      }
    }

    foreach( $this->time->get_js() as $time_js_line){
      if(!empty($time_js_line)){
          $this->add_js($time_js_line);
      }
    }

    parent::pre_render($form);
  }

  /**
   * preprocess hook . it simply calls the sub elements preprocess
   * @param  string $process_type preprocess type
   */
  public function preprocess($process_type = "preprocess") {
    $this->date->preprocess($process_type);
    $this->time->preprocess($process_type);
  }

  /**
   * process hook . it simply calls the sub elements process
   * @param  array $values value to set
   */
  public function process($values) {
    $this->date->process($values[$this->get_name().'_date']);
    $this->time->process($values[$this->get_name().'_time']);
  }

  /**
   * validate hook
   * @return boolean TRUE if element is valid
   */
  public function valid() {
    return $this->date->valid() && $this->time->valid();
  }

  /**
   * renders form errors
   * @return string errors as an html <li> list
   */
  public function show_errors() {
    return (trim($this->date->show_errors() . $this->time->show_errors()) == '') ? '' : trim($this->date->show_errors() . $this->time->show_errors());
  }

  /**
   * resets the sub elements
   */
  public function reset() {
    $this->date->reset();
    $this->time->reset();
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
    $output .= $this->date->render($form);
    $output .= $this->time->render($form);
    $output .= "</{$this->tag}>\n";
    return $output;
  }

  /**
   * return field value
   * @return array field value
   */
  public function values() {
    return [
      'date'=> $this->date->values(),
      'time'=> $this->time->values(),
      'datetime' => $this->date->value_string().' '.$this->time->value_string(),
    ];
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }

  /**
   * on_add_return overload
   * @return string 'parent'
   */
  protected function on_add_return(){
    return 'parent';
  }
}
