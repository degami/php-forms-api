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

/**
 * the file input field class
 */
class file extends field {

  /**
   * "file already uploaded" flag
   * @var boolean
   */
  protected $uploaded = FALSE;

  /**
   * file destination directory
   * @var string
   */
  protected $destination;

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    $output = '';

    $form->set_attribute('enctype', 'multipart/form-data');

    if(!isset($this->attributes['class'])) $this->attributes['class'] = '';
    if ($this->has_errors()) {
      $this->attributes['class'] .= ' has-errors';
    }
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes( ['type','name','id','size'] );

    $output .= "<input type=\"hidden\" name=\"{$this->name}\" value=\"{$this->name}\" />";
    $output .= "<input type=\"file\" id=\"{$id}\" name=\"{$this->name}\" size=\"{$this->size}\"{$attributes} />";
    return $output;
  }

  /**
   * process hook
   * @param  mixed $value value to set
   * @param  string $name file input name
   */
  public function process($value) {
    $this->value = [
      'filepath' => (isset($value['filepath'])) ? $value['filepath'] : $this->destination .'/'. basename($_FILES[$this->get_name()]['name']),
      'filename' => (isset($value['filename'])) ? $value['filename'] : basename($_FILES[$this->get_name()]['name']),
      'filesize' => (isset($value['filesize'])) ? $value['filesize'] : $_FILES[$this->get_name()]['size'],
      'mimetype' => (isset($value['mimetype'])) ? $value['mimetype'] : $_FILES[$this->get_name()]['type'],
    ];
    if(isset($value['uploaded'])){
      $this->uploaded = $value['uploaded'];
    }
    if ($this->valid()) {
      if( @move_uploaded_file($_FILES[$this->get_name()]['tmp_name'], $this->value['filepath']) == TRUE ){
        $this->uploaded = TRUE;
      }
    }
  }

  /**
   * check if file was uploaded
   * @return boolean TRUE if file was uploaded
   */
  public function is_uploaded(){
    return $this->uploaded;
  }

  /**
   * "required" validation function
   * @param  mixed $value the element value
   * @return mixed        TRUE if valid or a string containing the error message
   */
  public static function validate_required($value = NULL) {
    if (!empty($value) &&
      (isset($value['filepath']) && !empty($value['filepath'])) &&
      (isset($value['filename']) && !empty($value['filename'])) &&
      (isset($value['mimetype']) && !empty($value['mimetype'])) &&
      (isset($value['filesize']) && $value['filesize']>=0)
    ) {
      return TRUE;
    } else {
      return "<em>%t</em> is required";
    }
  }

  /**
   * validate function
   * @return boolean this field is always valid
   */
  public function valid() {
    if ($this->uploaded) {
      return TRUE;
    }
    return parent::valid();
  }

  /**
   * is_a_value hook
   * @return boolean this is a value
   */
  public function is_a_value(){
    return TRUE;
  }
}
