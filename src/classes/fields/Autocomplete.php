<?php
/**
 * PHP FORMS API
 *
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                    FIELDS                       ####
   ######################################################### */

namespace Degami\PHPFormsApi\Fields;

use Degami\PHPFormsApi\Form;

/**
 * the "autocomplete" text input field class
 */
class Autocomplete extends Textfield
{
    /**
     * autocomplete path
     *
     * @var mixed
     */
    protected $autocomplete_path = false;

    /**
     * options for autocomplete (if autocomplete path was not provided)
     *
     * @var array
     */
    protected $options = [];

    /**
     * minimum string length for autocomplete
     *
     * @var integer
     */
    protected $min_length = 3;

    /**
     * class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options, $name = null)
    {
        if (!isset($options['attributes']['class'])) {
            $options['attributes']['class'] = '';
        }
        $options['attributes']['class'].=' autocomplete';

        parent::__construct($options, $name);
    }

    /**
     * {@inheritdoc}
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        if ($this->pre_rendered == true) {
            return;
        }
        $id = $this->getHtmlId();

        $this->addJs(
            "
      \$('#{$id}','#{$form->getId()}')
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
    "
        );

        parent::preRender($form);
    }
}
