<?php
/**
 * PHP FORMS API
 * PHP Version 5.5
 *
 * @category Utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */
/* #########################################################
   ####                  FIELD BASE                     ####
   ######################################################### */

namespace Degami\PHPFormsApi\Abstracts\Base;

use Degami\PHPFormsApi\Interfaces\FieldInterface;
use Degami\PHPFormsApi\Traits\Tools;
use Degami\PHPFormsApi\Accessories\OrderedFunctions;
use Degami\PHPFormsApi\Form;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Fields\Checkbox;
use Degami\PHPFormsApi\Accessories\SessionBag;

/**
 * The field element class.
 *
 * @abstract
 */
abstract class Field extends Element implements FieldInterface
{
    use Tools;

    /**
     * validate functions list
     *
     * @var array
     */
    protected $validate = [];

    /**
     * preprocess functions list
     *
     * @var array
     */
    protected $preprocess = [];

    /**
     * postprocess functions list
     *
     * @var array
     */
    protected $postprocess = [];

    /**
     * Element js events list
     *
     * @var array
     */
    protected $event = [];

    /**
     * Element size
     *
     * @var integer
     */
    protected $size = 20;

    /**
     * Element type
     *
     * @var string
     */
    protected $type = '';

    /**
     * "stop on first validation error" flag
     *
     * @var boolean
     */
    protected $stop_on_first_error = false;

    /**
     * "show tooltip instead of label" flag
     *
     * @var boolean
     */
    protected $tooltip = false;

    /**
     * Element id
     *
     * @var null
     */
    protected $id = null;

    /**
     * Element title
     *
     * @var null
     */
    protected $title = null;

    /**
     * Element description
     *
     * @var null
     */
    protected $description = null;

    /**
     * Element disabled
     *
     * @var boolean
     */
    protected $disabled = false;

    /**
     * Element default value
     *
     * @var null
     */
    protected $default_value = null;

    /**
     * Element value
     *
     * @var null
     */
    protected $value = null;

    /**
     * "element already pre-rendered" flag
     *
     * @var boolean
     */
    protected $pre_rendered = false;

    /**
     * "this is a required field" position
     *
     * @var string
     */
    protected $required_position = 'after';

    /**
     * Element ajax url
     *
     * @var null
     */
    protected $ajax_url = null;

    /**
     * Session Bag Object
     *
     * @var SessionBag
     */
    private $session_bag = null;

    /**
     * Class constructor
     *
     * @param array  $options build options
     * @param string $name    field name
     */
    public function __construct($options = [], $name = null)
    {
        parent::__construct();

        if ($options == null) {
            $options = [];
        }
        $this->build_options = $options;

        $this->name = $name;

        $this->setClassProperties($options);

        $this->session_bag = new SessionBag();

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = trim(FORMS_FIELD_ADDITIONAL_CLASS.' '.$this->getElementClassName());
        }

        if (empty($this->type)) {
            $this->type = substr(get_class($this), strrpos(get_class($this), '\\') + 1);
        }

        if (!$this->validate instanceof OrderedFunctions) {
            $this->validate = new OrderedFunctions($this->validate, 'validator', [ __CLASS__,'order_validators' ]);
        }

        if (!$this->preprocess instanceof OrderedFunctions) {
            $this->preprocess = new OrderedFunctions($this->preprocess, 'preprocessor');
        }

        if (!$this->postprocess instanceof OrderedFunctions) {
            $this->postprocess = new OrderedFunctions($this->postprocess, 'postprocessor');
        }

        if (!$this->event instanceof OrderedFunctions) {
            $this->event = new OrderedFunctions($this->event, 'event');
        }

        $this->value = $this->default_value;
    }

    /**
     * class "static" constructor
     *
     * @param  array  $options build options
     * @param  string $name    field name
     * @return Field
     */
    public static function getInstance($options = [], $name = null)
    {
        // let others alter the field
        static::executeAlter("/.*?_".static::getClassNameString()."_alter$/i", [&$options, &$name]);
        return new static($options, $name);
    }

    /**
     * Get Session Bag
     *
     * @return SessionBag
     */
    public function getSessionBag()
    {
        return $this->session_bag;
    }

    /**
     * Return field value
     *
     * @return mixed field value
     */
    public function getValues()
    {
        return $this->getValue();
    }

    /**
     * Return field value
     *
     * @return mixed field value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set field value
     *
     * @param  mixed $value value to set
     * @return Field
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get default value
     *
     * @return mixed default value
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * Set default value
     *
     * @param  mixed $default_value default value
     * @return Field
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;

        return $this;
    }

    /**
     * resets the field
     */
    public function resetField()
    {
        $this->setValue($this->getDefaultValue());
        $this->pre_rendered = false;
        $this->setErrors([]);
    }

    /**
     * Get field type
     *
     * @return string field type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get field validate
     *
     * @return OrderedFunctions field validate
     */
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * Get field preprocess
     *
     * @return OrderedFunctions field preprocess
     */
    public function getPreprocess()
    {
        return $this->preprocess;
    }

    /**
     * Get field postprocess
     *
     * @return OrderedFunctions field postprocess
     */
    public function getPostprocess()
    {
        return $this->postprocess;
    }

    /**
     * Get field id
     *
     * @return string field id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set field id
     *
     * @param  string $id field id
     * @return Field
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get field html id
     *
     * @return string the html id attributes
     */
    public function getHtmlId()
    {
        return strtolower(!empty($this->id) ? $this->getId() : $this->getName());
    }

    /**
     * Get css class name for field
     *
     * @return string css class name
     */
    public function getElementClassName()
    {
        return strtolower(substr(get_class($this), strrpos(get_class($this), '\\') + 1));
    }

    /**
     * Get field ajax url
     *
     * @return string field ajax url
     */
    public function getAjaxUrl()
    {
        return $this->ajax_url;
    }

    /**
     * Process (set) the field value
     *
     * @param mixed $value value to set
     */
    public function processValue($value)
    {
        $this->setValue($value);
    }

    /**
     * execute the preprocess ( or postprocess ) list of functions
     *
     * @param string $process_type which list to process
     */
    public function preProcess($process_type = "preprocess")
    {
        foreach ($this->{$process_type} as $processor) {
            $processor_func = "process".ucfirst($processor);
            if (method_exists(get_class($this), $processor_func)) {
                $this->value = call_user_func([$this, $processor_func], $this->value);
            } elseif (method_exists(Form::class, $processor_func)) {
                $this->value = call_user_func([Form::class,$processor_func], $this->value);
            } elseif (function_exists("process_{$this->getType()}_{$processor}")) {
                $processor_func = "process_{$this->getType()}_{$processor}";
                $this->value = $processor_func($this->value);
            }
        }
    }

    /**
     * postprocess field
     */
    public function postProcess()
    {
        $this->preProcess("postprocess");
    }

    /**
     * which element should return the add_field() function
     *
     * @return string one of 'parent' or 'this'
     */
    public function onAddReturn()
    {
        return 'parent';
    }

    /**
     * Check if field is valid using the validate functions list
     *
     * @return boolean valid state
     */
    public function isValid()
    {
        $this->setErrors([]);

        foreach ($this->validate as $validator) {
            $matches = [];
            if (is_array($validator)) {
                $validator_func = $validator['validator'];
            } else {
                $validator_func = $validator;
            }
            preg_match('/^([A-Za-z0-9_]+)(\[(.+)\])?$/', $validator_func, $matches);
            if (!isset($matches[1])) {
                continue;
            }
            $validator_func = "validate".ucfirst($matches[1]);
            $options = isset($matches[3]) ? $matches[3] : null;
            if (function_exists($validator_func)) {
                $error = $validator_func($this->value, $options);
            } elseif (method_exists(get_class($this), $validator_func)) {
                $error = call_user_func([get_class($this), $validator_func], $this->value, $options);
            } elseif (method_exists(Form::class, $validator_func)) {
                $error = call_user_func([Form::class, $validator_func], $this->value, $options);
            }
            if (isset($error) && $error !== true) {
                $titlestr = (!empty($this->title)) ? $this->title : (!empty($this->name) ? $this->name : $this->id);
                if (empty($error)) {
                    $error = '%t - Error.';
                }
                $this->addError(str_replace('%t', $titlestr, $this->getText($error)), $validator_func);
                if (is_array($validator) && !empty($validator['error_message'])) {
                    $this->addError(
                        str_replace('%t', $titlestr, $this->getText($validator['error_message'])),
                        $validator_func
                    );
                }

                if ($this->stop_on_first_error) {
                    return false;
                }
            }
        }

        if ($this->hasErrors()) {
            return false;
        }

        return true;
    }

    /**
     * renders field errors
     *
     * @return string errors as a <li> list
     */
    public function showErrors()
    {
        return $this->notifications->renderHTML('error');
    }

    /**
     * Pre render. this function will be overloaded by subclasses where needed
     *
     * @param Form $form form object
     */
    public function preRender(Form $form)
    {
        $this->pre_rendered = true;
        // should not return value, just change element/form state
        return;
    }

    /**
     * render the field
     *
     * @param Form $form form object
     *
     * @return string        the field html
     */
    public function renderHTML(Form $form)
    {
        $id = $this->getHtmlId();
        $output = $this->getElementPrefix();
        $output .= $this->getPrefix();

        if (!($this instanceof FieldsContainer) && !($this instanceof Checkbox)) {
            // containers do not need label. checkbox too, as the render function prints the label itself
            $required = ($this->validate->hasValue('required')) ? '<span class="required">*</span>' : '';
            $requiredafter = $requiredbefore = $required;
            if ($this->required_position == 'before') {
                $requiredafter = '';
                $requiredbefore = $requiredbefore.' ';
            } else {
                $requiredbefore = '';
                $requiredafter = ' '.$requiredafter;
            }

            if (!empty($this->title)) {
                if ($this->tooltip == false) {
                    $this->label_class .= " label-" . $this->getElementClassName();
                    $this->label_class = trim($this->label_class);
                    $label_class = (!empty($this->label_class)) ? " class=\"{$this->label_class}\"" : "";
                    $output .= "<label for=\"{$id}\" {$label_class}>{$requiredbefore}".
                                $this->getText($this->title).
                                "{$requiredafter}</label>\n";
                } else {
                    if (!in_array('title', array_keys($this->attributes))) {
                        $this->attributes['title'] = strip_tags($this->getText($this->title).$required);
                    }

                    $id = $this->getHtmlId();
                    $form->addJs("\$('#{$id}','#{$form->getId()}').tooltip();");
                }
            }
        }

        if (!$this->pre_rendered) {
            $this->preRender($form);
            $this->pre_rendered = true;
        }
        $output .= $this->renderField($form);

        if (!($this instanceof FieldsContainer)) {
            if (!empty($this->description)) {
                $output .= "<div class=\"description\">{$this->description}</div>";
            }
        }
        if ($form->errorsInline() == true && $this->hasErrors()) {
            $output.= '<div class="inline-error has-errors">'.implode("<br />", $this->getErrors()).'</div>';
        }

        $output .= $this->getSuffix();
        $output .= $this->getElementSuffix();

        if (count($this->event) > 0 && trim($this->getAjaxUrl()) != '') {
            foreach ($this->event as $event) {
                $eventjs = $this->generateEventJs($event, $form);
                $this->addJs($eventjs);
            }
        }

        // let others alter the output
        static::executeAlter("/.*?_".static::getClassNameString()."_render_output_alter$/i", [&$output]);

        // return html string
        return $output;
    }

    /**
     * generate the necessary js to handle ajax field event property
     *
     * @param array $event event element
     * @param Form  $form  form object
     *
     * @return string         javascript code
     */
    public function generateEventJs($event, Form $form)
    {
        $id = $this->getHtmlId();
        if (empty($event['event'])) {
            return false;
        }
        $question_ampersand = '?';
        if (preg_match("/\?/i", $this->getAjaxUrl())) {
            $question_ampersand = '&';
        }

        $eventjs = "\$('#{$id}','#{$form->getId()}').on('{$event['event']}',function(evt){
          evt.preventDefault();
          var \$target = ".
          ((isset($event['target']) && !empty($event['target'])) ?
            "\$('#".$event['target']."')" :
            "\$('#{$id}').parent()").
          ";
          var jsondata = { 
            'name':\$('#{$id}').attr('name'), 
            'value':\$('#{$id}').val(),
            'callback':'{$event['callback']}' 
          };
          var postdata = new FormData();
          postdata.append('form_id', '{$form->getId()}');
          postdata.append('jsondata', JSON.stringify(jsondata));
          \$('#{$form->getId()} input,#{$form->getId()} select,#{$form->getId()} textarea')
          .each(function(index, elem){
            var \$this = \$(this);
            if( \$this.serialize() != '' ){
              var elem = \$this.serialize().split('=',2);
              postdata.append(elem[0], elem[1]);
            }else if( 
                \$this.prop('tagName').toLowerCase() == 'input' && 
                \$this.attr('type').toLowerCase() == 'file' 
            ){
              postdata.append(\$this.attr('name'), (\$this)[0].files[0] );
            }
          });
          var \$loading = \$('<div id=\"{$id}-event-loading\"></div>')
          .appendTo(\$target)
          .css({'font-size':'0.5em'})
          .progressbar({value: false});
          \$.data(\$target[0],'loading', \$loading.attr('id'));
          \$.ajax({
            type: \"POST\",
            contentType: false,
            processData: false,
            url: \"{$this->getAjaxUrl()}{$question_ampersand}partial=true&triggering_element={$this->getHtmlId()}\",
            data: postdata,
            success: function( data ){
              var response;
              if(typeof data =='object') { response = data; }
              else { response = \$.parseJSON(data); }
              ".(
                    (!empty($event['method']) && $event['method'] == 'replace') ?
                    "\$target.html('');":
                    ""
                )."
              ".(
                    (!empty($event['effect']) && $event['effect'] == 'fade') ?
                    "\$target.hide(); \$(response.html).appendTo(\$target); \$target.fadeIn('fast');":
                    "\$(response.html).appendTo(\$target);"
                )."
              if( \$.trim(response.js) != '' ){ eval( response.js ); };

              var element_onsuccess = \$.data( \$('#{$id}','#{$form->getId()}')[0], 'element_onsuccess' );
              if( !!(
                        element_onsuccess && element_onsuccess.constructor && 
                        element_onsuccess.call && element_onsuccess.apply
              ) ){
                element_onsuccess();
              }
            },
            error: function ( jqXHR, textStatus, errorThrown ){
              var element_onerror = \$.data( \$('#{$id}','#{$form->getId()}')[0], 'element_onerror' );
              if( !!(element_onerror && element_onerror.constructor && element_onerror.call && element_onerror.apply) ){
                element_onerror();
              }

              if(\$.trim(errorThrown) != '') alert(textStatus+': '+errorThrown);
            },
            complete: function( jqXHR, textStatus ){
              var loading = \$.data(\$target[0],'loading');
              \$('#'+loading).remove();
            }
          });
          return false;
        });";
        return $eventjs;
    }

    /**
     * alter request hook
     *
     * @param array &$request request array
     */
    public function alterRequest(&$request)
    {
        // implementing this function fields can change the request array
    }
    /**
     * after validate hook
     *
     * @param Form $form form object
     */
    public function afterValidate(Form $form)
    {
        // here field can do things after the validation has passed
    }
}
