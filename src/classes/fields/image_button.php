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
use Degami\PHPFormsApi\Abstracts\Fields\clickable;

/**
 * the image submit input type field class
 */
class image_button extends clickable {

  /**
   * image source
   * @var string
   */
  protected $src;

  /**
   * image alternate
   * @var string
   */
  protected $alt;

  /**
   * class constructor
   * @param array  $options build options
   * @param string $name    field name
   */
  public function __construct($options = [], $name = NULL) {
    $this->default_value = [
      'x'=>-1,
      'y'=>-1,
    ];

    parent::__construct($options, $name);
  }

  /**
   * render_field hook
   * @param  form $form form object
   * @return string        the element html
   */
  public function render_field(form $form) {
    $id = $this->get_html_id();
    if($this->disabled == TRUE) $this->attributes['disabled']='disabled';
    $attributes = $this->get_attributes( ['type','name','id','value','src','alt'] );
    //  value=\"{$this->value}\"
    $output = "<input id=\"{$id}\" name=\"{$this->name}\" type=\"image\" src=\"{$this->src}\" alt=\"{$this->alt}\"{$attributes} />\n";
    return $output;
  }

  /**
   * alter_request hook
   * @param  array $request request array
   */
  public function alter_request(&$request){
    foreach($request as $key => $val){
      //IMAGE BUTTONS HANDLE
      if(preg_match('/^(.*?)_(x|y)$/',$key,$matches) && $this->get_name() == $matches[1] ){
        //assume this is an input type="image"
        if( isset($request[$matches[1].'_'.(($matches[2] == 'x')?'y':'x')]) ){
          $request[$matches[1]] = [
            'x'=>$request[$matches[1].'_x'],
            'y'=>$request[$matches[1].'_y'],
          ];

          unset($request[$matches[1].'_x']);
          unset($request[$matches[1].'_y']);
        }
      }
    }
  }

}
