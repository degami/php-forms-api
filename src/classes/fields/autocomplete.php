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
* the "autocomplete" text input field class
*/
class autocomplete extends textfield{
  /**
  * autocomplete path
  * @var mixed
  */
  protected $autocomplete_path = FALSE;

  /**
  * options for autocomplete (if autocomplete path was not provided)
  * @var array
  */
  protected $options = [];

  /**
  * minimum string length for autocomplete
  * @var integer
  */
  protected $min_length = 3;

  /**
  * class constructor
  * @param array  $options build options
  * @param string $name    field name
  */
  public function __construct($options, $name = NULL){
    if(!isset($options['attributes']['class'])){
      $options['attributes']['class'] = '';
    }
    $options['attributes']['class'].=' autocomplete';

    parent::__construct($options, $name);
  }

  /**
  * pre_render hook
  * @param  form $form form object
  */
  public function pre_render(form $form){
    if( $this->pre_rendered == TRUE ) return;
    $id = $this->get_html_id();

    $this->add_js("
      \$('#{$id}','#{$form->get_id()}')
      .bind( 'keydown', function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB && \$( this ).autocomplete( 'instance' ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        source: ".((!empty($this->options)) ? json_encode($this->options) : "'{$this->autocomplete_path}'").",
        minLength: {$this->min_length},
        focus: function() {
          return false;
        }
      });
    ");

    parent::pre_render($form);
  }
}
