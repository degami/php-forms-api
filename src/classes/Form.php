<?php
/**
 * PHP FORMS API
 * PHP Version 5.5
 *
 * @category utils
 * @package  Degami\PHPFormsApi
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/php-forms-api
 */
/* #########################################################
   ####                      FORM                       ####
   ######################################################### */

namespace Degami\PHPFormsApi;

use \Exception;
use Degami\PHPFormsApi\Traits\Tools;
use Degami\PHPFormsApi\Traits\Processors;
use Degami\PHPFormsApi\Traits\Validators;
use Degami\PHPFormsApi\Traits\Containers;
use Degami\PHPFormsApi\Abstracts\Base\Element;
use Degami\PHPFormsApi\Abstracts\Base\Field;
use Degami\PHPFormsApi\Abstracts\Base\FieldsContainer;
use Degami\PHPFormsApi\Accessories\OrderedFunctions;
use Degami\PHPFormsApi\Accessories\FormValues;
use Degami\PHPFormsApi\Fields\Datetime;
use Degami\PHPFormsApi\Fields\Geolocation;
use Degami\PHPFormsApi\Fields\Checkbox;
use Degami\PHPFormsApi\Fields\Radios;
use Degami\PHPFormsApi\Fields\Select;
use Degami\PHPFormsApi\Abstracts\Fields\FieldMultivalues;
use Degami\PHPFormsApi\Accessories\SessionBag;

/**
 * the form object class
 */
class Form extends Element
{
    use Tools, Validators, Processors, Containers;

    /**
     * form id
     *
     * @var string
     */
    protected $form_id = 'cs_form';

    /**
     * form definition function name
     *
     * @var string
     */
    protected $definition_function = '';


    /**
     * form token
     *
     * @var string
     */
    protected $form_token = '';

    /**
     * form action
     *
     * @var string
     */
    protected $action = '';

    /**
     * form method
     *
     * @var string
     */
    protected $method = 'post';

    /**
     * "form is already processsd" flag
     *
     * @var boolean
     */
    protected $processed = false;

    /**
     * "form is already validated" flag
     *
     * @var boolean
     */
    protected $validated = false;

    /**
     * "form is already submitted" flag
     *
     * @var boolean
     */
    protected $submitted = false;

    /**
     * "form is valid" flag
     *
     * @var null
     */
    protected $valid = null;

    /**
     * validate functions list
     *
     * @var array
     */
    protected $validate = [];

    /**
     * submit functions list
     *
     * @var array
     */
    protected $submit = [];

    /**
     * form output type (html/json)
     *
     * @var string
     */
    protected $output_type = 'html';

    /**
     * show inline errors
     *
     * @var boolean
     */
    protected $inline_errors = false;

    /**
     * "form already pre-rendered" flag
     *
     * @var boolean
     */
    protected $pre_rendered = false;

    /**
     * "js was aleready generated" flag
     *
     * @var boolean
     */
    protected $js_generated = false;

    /**
     * ajax submit url
     *
     * @var string
     */
    protected $ajax_submit_url = '';

    /**
     * print form on a dialog
     *
     * @var boolean
     */
    protected $on_dialog = false;

    /**
     * current step
     *
     * @var integer
     */
    private $current_step = 0;

    /**
     * array of submit functions results
     *
     * @var array
     */
    private $submit_functions_results = [];

    /**
     * "do not process form token" flag
     *
     * @var boolean
     */
    private $no_token = false;

    /**
     * Session Bag Object
     *
     * @var SessionBag
     */
    private $session_bag = null;

    /**
     * Class constructor
     *
     * @param array $options build options
     */
    public function __construct($options = [])
    {
        $this->build_options = $options;
        $this->session_bag = new SessionBag();
        $this->container_tag = FORMS_DEFAULT_FORM_CONTAINER_TAG;
        $this->container_class = FORMS_DEFAULT_FORM_CONTAINER_CLASS;

        foreach ($options as $name => $value) {
            $name = trim($name);
            if (property_exists(get_class($this), $name)) {
                $this->{$name} = $value;
            }
        }

        $hassubmitter = false;
        foreach ($this->submit as $s) {
            if (!empty($s) && is_callable($s)) {
                $hassubmitter = true;
            }
        }
        if (!$hassubmitter) {
            array_push($this->submit, "{$this->form_id}_submit");
        }

        // if (empty($this->submit) || !is_callable($this->submit)) {
        //   array_push($this->submit, "{$this->form_id}_submit");
        // }

        $hasvalidator = false;
        foreach ($this->validate as $v) {
            if (!empty($v) && is_callable($v)) {
                $hasvalidator = true;
            }
        }
        if (!$hasvalidator) {
            array_push($this->validate, "{$this->form_id}_validate");
        }

        // if (empty($this->validate) || !is_callable($this->validate)) {
        //   array_push($this->validate, "{$this->form_id}_validate");
        // }

        if (!$this->validate instanceof OrderedFunctions) {
            $this->validate = new OrderedFunctions($this->validate, 'validator');
        }

        if (!$this->submit instanceof OrderedFunctions) {
            $this->submit = new OrderedFunctions($this->submit, 'submitter');
        }

        $has_session = FormBuilder::sessionPresent();
        if ($has_session) {
            $this->form_token = sha1(mt_rand(0, 1000000));
            $this->getSessionBag()->ensurePath("/form_token");
            $this->getSessionBag()->form_token->{$this->form_token} = $_SERVER['REQUEST_TIME'];
        }
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
     * Set form id
     *
     * @param string $form_id set the form id used for getting the submit function name
     *
     * @return Form
     */
    public function setFormId($form_id)
    {
        $this->form_id = $form_id;
        return $this;
    }

    /**
     * Get the form id
     *
     * @return string form id
     */
    public function getFormId()
    {
        return $this->form_id;
    }


    /**
     * Set the form action attribute
     *
     * @param string $action the form action url
     *
     * @return Form
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get the form action url
     *
     * @return string the form action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the form method
     *
     * @param string $method form method
     *
     * @return Form
     */
    public function setMethod($method)
    {
        $this->method = strtolower(trim($method));
        return $this;
    }

    /**
     * Get the form method
     *
     * @return string form method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the ajax submit url used for form submission
     *
     * @param string $ajax_submit_url ajax endpoint url
     *
     * @return Form
     */
    public function setAjaxSubmitUrl($ajax_submit_url)
    {
        $this->ajax_submit_url = $ajax_submit_url;
        return $this;
    }

    /**
     * Get the ajax form submission url
     *
     * @return string the form ajax submission url
     */
    public function getAjaxSubmitUrl()
    {
        return $this->ajax_submit_url;
    }

    /**
     * Set the form render output type
     *
     * @param string $output_type output type ( 'html' / 'json' )
     *
     * @return Form
     */
    public function setOutputType($output_type)
    {
        $this->output_type = $output_type;
        return $this;
    }

    /**
     * Get the form render output type
     *
     * @return string form output type
     */
    public function getOutputType()
    {
        return $this->output_type;
    }


    /**
     * Set no_token flag
     *
     * @param boolean $no_token no token flag
     *
     * @return Form
     */
    public function setNoToken($no_token)
    {
        $this->no_token = $no_token;
        return $this;
    }

    /**
     * Get no_token flag
     *
     * @return boolean no token flag
     */
    public function getNoToken()
    {
        return $this->no_token;
    }


    /**
     * Set the form on_dialog preference
     *
     * @param string $on_dialog the form on_dialog preference
     *
     * @return Form
     */
    public function setOnDialog($on_dialog)
    {
        $this->on_dialog = $on_dialog;
        return $this;
    }

    /**
     * Get the form on_dialog preference
     *
     * @return string the form on_dialog preference
     */
    public function getOnDialog()
    {
        return $this->on_dialog;
    }

    /**
     * Get the form token
     *
     * @return string the form token used in form validation and submission process
     */
    public function getFormToken()
    {
        return $this->form_token;
    }

    /**
     * Return form elements (all the steps) values
     *
     * @return array form values
     */
    public function getValues()
    {
        // Warning: some messy logic in calling process->submit->values
        if (!$this->processed) {
            $this->processValue();
        }
        $output = [];
        for ($step = 0; $step <= $this->getNumSteps(); $step++) {
            foreach ($this->getFields($step) as $name => $field) {
                if ($field->isAValue() == true) {
                    $output[$name] = $field->getValues();
                    if (is_array($output[$name]) && empty($output[$name])) {
                        unset($output[$name]);
                    }
                }
            }
        }

        return new FormValues($output);
    }

    /**
     * Return form elements (all the steps) values
     *
     * @return     array form values
     * @deprecated
     */
    public function values()
    {
        return $this->getValues();
    }

    /**
     * Get current step elemets values
     *
     * @return array step values
     */
    private function getCurrentStepValues()
    {
        $output = [];
        foreach ($this->getFields($this->current_step) as $name => $field) {
            if ($field->isAValue() == true) {
                $output[$name] = $field->getValues();
                if (is_array($output[$name]) && empty($output[$name])) {
                    unset($output[$name]);
                }
            }
        }
        return $output;
    }

    /**
     * resets the form
     */
    public function resetField()
    {
        foreach ($this->getFields() as $name => $field) {
            $field->resetField();
            if (strtolower($this->method) == 'post') {
                unset($_POST[$name]);
            } else {
                unset($_GET[$name]);
            }
            unset($_REQUEST[$name]);
        }

        if (strtolower($this->method) == 'post') {
            unset($_POST['form_id']);
            unset($_POST['form_token']);
        } else {
            unset($_GET['form_id']);
            unset($_GET['form_token']);
        }
        unset($_REQUEST['form_id']);
        unset($_REQUEST['form_token']);

        if (isset($this->getSessionBag()->{$this->form_id})) {
            unset($this->getSessionBag()->{$this->form_id});
        }

        if (isset($this->getSessionBag()->form_definition[$this->form_id])) {
            unset($this->getSessionBag()->form_definition[$this->form_id]);
        }

        $this->processed = false;
        $this->validated = false;
        $this->submitted = false;
        $this->js_generated = false;
        $this->setErrors([]);
        $this->valid = null;
        $this->current_step = 0;
        $this->submit_functions_results = [];
    }

    /**
     * resets the form
     */
    public function reset()
    {
        $this->resetField();
    }

    /**
     * check if form is submitted
     *
     * @return boolean form is submitted
     */
    public function isSubmitted()
    {
        return $this->submitted;
    }


    /**
     * check if form is processed
     *
     * @return boolean form is processed
     */
    public function isProcessed()
    {
        return $this->processed;
    }


    /**
     * Get the form submit results optionally by submit function name
     *
     * @param  string $submit_function submit function name
     * @return mixed function(s) return value or function(s) data sent to stdout if not returning anything
     */
    public function getSubmitResults($submit_function = '')
    {
        if (!$this->isSubmitted()) {
            return false;
        }
        if (!empty($submit_function)) {
            if (!in_array($submit_function, array_keys($this->submit_functions_results))) {
                return false;
            }
            return $this->submit_functions_results[$submit_function];
        }
        return $this->submit_functions_results;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $request request array
     */
    private function alterRequest(&$request)
    {
        foreach ($this->getFields($this->current_step) as $field) {
            $field->alterRequest($request);
        }
    }

    /**
     * copies the request values into the right form element
     *
     * @param array   $request request array
     * @param integer $step    step number
     */
    private function injectValues($request, $step)
    {
        foreach ($this->getFields($step) as $name => $field) {
            if ($field instanceof FieldsContainer) {
                $field->processValue($request);
            } elseif (preg_match_all('/(.*?)(\[(.*?)\])+/i', $name, $matches, PREG_SET_ORDER)) {
                $value = null;
                if (isset($request[ $matches[0][1] ])) {
                    $value = $request[ $matches[0][1] ];
                    foreach ($matches as $match) {
                        if (isset($value[ $match[3] ])) {
                            $value = $value[ $match[3] ];
                        }
                    }
                }
                $field->processValue($value);
            } elseif (isset($request[$name])) {
                $field->processValue($request[$name]);
            } elseif ($field instanceof Checkbox || $field instanceof Radios) {
                // no value on request[name] && field is a checkbox or radios group - process anyway with an empty value
                $field->processValue(null);
            } elseif ($field instanceof Select) {
                if ($field->isMultiple()) {
                    $field->processValue([]);
                } else {
                    $field->processValue(null);
                }
            } elseif ($field instanceof FieldMultivalues) {
                // no value on request[name] && field is a multivalue (eg. checkboxes ?)
                // process anyway with an empty value
                $field->processValue([]);
            }
        }
    }

    /**
     * save current step request array in session
     *
     * @param array $request request array
     */
    private function saveStepRequest($request)
    {
        $files = $this->getStepFieldsByTypeAndName('file', null, $this->current_step);
        if (!empty($files)) {
            foreach ($files as $filefield) {
                $request[$filefield->getName()] = $filefield->getValues();
                $request[$filefield->getName()]['uploaded'] = $filefield->isUploaded();
            }
        }

        $recaptchas = $this->getStepFieldsByTypeAndName('recaptcha', null, $this->current_step);
        if (!empty($recaptchas)) {
            foreach ($recaptchas as $recaptchafield) {
                $request[$recaptchafield->getName()] = $recaptchafield->getValues();
                $request[$recaptchafield->getName()]['already_validated'] = $recaptchafield->isAlreadyValidated();
            }
        }

        $has_session = FormBuilder::sessionPresent();
        if ($has_session) {
            $this->getSessionBag()->ensurePath("/{$this->form_id}/steps");
            $this->getSessionBag()->{$this->form_id}->steps->add(
                [
                $this->current_step => $request
                ]
            );
        }
    }

    /**
     * starts the form processing, validating and submitting
     *
     * @param array $values the request values array
     */
    public function processValue($values = [])
    {
        $has_session = FormBuilder::sessionPresent();
        if ($has_session) {
            $this->getSessionBag()->ensurePath("/form_token");
            foreach ($this->getSessionBag()->form_token as $key => $time) {
                if ($time < ($_SERVER['REQUEST_TIME'] - FORMS_SESSION_TIMEOUT)) {
                    unset($this->getSessionBag()->form_token[$key]);
                }
            }
        }

        // let others alter the form
        $defined_functions = get_defined_functions();
        foreach ($defined_functions['user'] as $function_name) {
            if (preg_match("/.*?_{$this->form_id}_form_alter$/i", $function_name)) {
                call_user_func_array($function_name, [ &$this ]);
            }
        }

        $request = null;
        if (!$this->processed) { //&& !form::is_partial()
            if (empty($values)) {
                $request = (strtolower($this->method) == 'post') ? $_POST : $_GET;
            } else {
                $request = $values;
            }

            //alter request if needed
            $this->alterRequest($request);

            if (isset($request['form_id']) && $request['form_id'] == $this->form_id) {
                if (isset($request['current_step'])) {
                    $this->current_step = $request['current_step'];
                }
                // insert values into fields
                for ($step = 0; $step < $this->current_step; $step++) {
                    if ($has_session && isset($this->getSessionBag()->{$this->form_id}->steps['_value'.$step])) {
                        $this->injectValues($this->getSessionBag()->{$this->form_id}->steps['_value'.$step], $step);
                    }
                }

                $this->injectValues($request, $this->current_step);

                if (!$this->isFinalStep()) {
                    $this->saveStepRequest($request);
                }

                $this->processed = true;
            }
        }

        if ($this->processed == true) {
            for ($step = 0; $step <= $this->current_step; $step++) {
                foreach ($this->getFields($step) as $name => $field) {
                    $field->preProcess();
                }
            }
            if (!Form::isPartial() && !$this->submitted && $this->isValid() && $this->isFinalStep()) {
                $this->submitted = true;

                if ($has_session && isset($this->getSessionBag()->{$this->form_id})) {
                    unset($this->getSessionBag()->{$this->form_id});
                }

                for ($step = 0; $step < $this->getNumSteps(); $step++) {
                    foreach ($this->getFields($step) as $name => $field) {
                        $field->postProcess();
                    }
                }

                foreach ($this->submit as $submit_function) {
                    if (is_callable($submit_function)) {
                        if (!is_array($this->submit_functions_results)) {
                            $this->submit_functions_results = [];
                        }
                        $submitresult = '';
                        ob_start();
                        $submitresult = call_user_func_array($submit_function, [ &$this, $request ]);
                        if ($submitresult == null) {
                            $submitresult = ob_get_contents();
                        }
                        ob_end_clean();
                        $deffunctionname = FormBuilder::getDefinitionFunctionName($submit_function);
                        $this->submit_functions_results[$deffunctionname] = $submitresult;
                    }
                }
            }
        }
    }

    /**
     * starts the form processing, validating and submitting
     *
     * @param      array $values the request values array
     * @deprecated
     */
    public function process($values = [])
    {
        $this->processValue($values);
    }

    /**
     * check if form is valid / NULL if form is on the first render
     *
     * @return boolean form is valid
     */
    public function isValid()
    {
        if ($this->validated) {
            return $this->valid;
        }
        if (!isset($_REQUEST['form_id'])) {
            return null;
        } elseif ($_REQUEST['form_id'] == $this->form_id) {
            $has_session = FormBuilder::sessionPresent();
            if ($this->valid == null) {
                $this->valid = true;
            }
            if ($has_session && !$this->no_token) {
                $this->valid = false;
                $this->addError($this->getText('Form is invalid or has expired'), __FUNCTION__);
                if (isset($_REQUEST['form_token'])
                    && isset($this->getSessionBag()->form_token->{$_REQUEST['form_token']})
                ) {
                    if ($this->getSessionBag()->form_token->{$_REQUEST['form_token']} >=
                        ($_SERVER['REQUEST_TIME'] - FORMS_SESSION_TIMEOUT)
                    ) {
                        $this->valid = true;
                        $this->setErrors([]);
                        if (!Form::isPartial()) {
                            unset($this->getSessionBag()->form_token->{$_REQUEST['form_token']});
                        }
                    }
                }
            }
            for ($step = 0; $step <= $this->current_step; $step++) {
                foreach ($this->getFields($step) as $field) {
                    if (!$field->isValid()) {
                        $this->valid = false;
                    }
                }
            }

            if ($this->valid) {
                foreach ($this->getFields($this->current_step) as $field) {
                    $field->afterValidate($this);
                }
                $this->current_step++;
            }

            if ($this->isFinalStep()) {
                foreach ($this->validate as $validate_function) {
                    if (function_exists($validate_function)) {
                        $error = $validate_function(
                            $this,
                            (strtolower($this->method) == 'post') ?
                                $_POST :
                                $_GET
                        );
                        if ($error !== true) {
                            $this->valid = false;
                            $this->addError(
                                is_string($error) ?
                                    $this->getText($error) :
                                    $this->getText('Error. Form is not valid'),
                                $validate_function
                            );
                        }
                    }
                }
            }

            if (!$this->valid) {
                $this->current_step--;
            }
            if ($this->current_step < 0) {
                $this->current_step = 0;
            }

            $this->validated = true;
            return $this->valid;
        }
        return null;
    }

    /**
     * add field to form
     *
     * @param string  $name  field name
     * @param mixed   $field field to add, can be an array or a field subclass
     * @param integer $step  step to add the field to
     *
     * @return mixed
     */
    public function addField($name, $field, $step = 0)
    {
        $field = $this->getFieldObj($name, $field);
        $field->setParent($this);

        $this->fields[$step][$name] = $field;
        $this->insert_field_order[] = $name;

        if (!method_exists($field, 'onAddReturn')) {
            if ($this->isFieldContainer($field)) {
                return $field;
            }
            return $this;
        }
        if ($field->onAddReturn() == 'this') {
            return $field;
        }
        return $this;
    }

    /**
     * remove field from form
     *
     * @param string  $name field name
     * @param integer $step field step
     *
     * @return Form
     */
    public function removeField($name, $step = 0)
    {
        unset($this->fields[$step][$name]);
        if (($key = array_search($name, $this->insert_field_order)) !== false) {
            unset($this->insert_field_order[$key]);
        }
        return $this;
    }

    /**
     * Get the number of form steps
     *
     * @return int steps number
     */
    private function getNumSteps()
    {
        return count($this->fields);
    }

    /**
     * check if current is the final step
     *
     * @return boolean this is the final step
     */
    private function isFinalStep()
    {
        return ($this->getCurrentStep() >= $this->getNumSteps());
    }

    /**
     * check if this request is a "partial" ( used in elements ajax requests )
     *
     * @return boolean [description]
     */
    public static function isPartial()
    {
        return (isset($_REQUEST['partial']) && $_REQUEST['partial'] == 'true');
    }

    /**
     * Get the fields array by reference
     *
     * @param  integer $step step number
     * @return array        the array of elements for the step specified
     */
    public function &getFields($step = 0)
    {
        $notfound = [];
        if (!isset($this->fields[$step])) {
            return $notfound;
        }
        return $this->fields[$step];
    }

    /**
     * Get the step fields by type and name
     *
     * @param  array|string $field_types field types
     * @param  string       $name        field name
     * @param  integer      $step        step number
     * @return array               the array of fields matching the search criteria
     */
    private function getStepFieldsByTypeAndName($field_types, $name = null, $step = 0)
    {
        if (!is_array($field_types)) {
            $field_types = [$field_types];
        }
        $out = [];
        foreach ($this->getFields($step) as $field) {
            if ($field instanceof FieldsContainer) {
                if ($name != null) {
                    $out = array_merge($out, $field->getFieldsByTypeAndName($field_types, $name));
                } else {
                    $out = array_merge($out, $field->getFieldsByType($field_types));
                }
            } else {
                if ($name != null) {
                    if ($field instanceof Field && in_array($field->getType(), $field_types)
                        && $field->getName() == $name
                    ) {
                        $out[] = $field;
                    }
                } elseif ($field instanceof Field && in_array($field->getType(), $field_types)) {
                    $out[] = $field;
                }
            }
        }
        return $out;
    }

    /**
     * Get the form fields by type (in all the steps)
     *
     * @param  array $field_types field types
     * @return array              fields in the form
     */
    public function getFieldsByType($field_types)
    {
        if (!is_array($field_types)) {
            $field_types = [$field_types];
        }
        $out = [];

        for ($step=0; $step < $this->getNumSteps(); $step++) {
            $out = array_merge($out, $this->getStepFieldsByTypeAndName($field_types, null, $step));
        }
        return $out;
    }

    /**
     * Get the step fields by type and name (in all the steps)
     *
     * @param  array  $field_types field types
     * @param  string $name        field name
     * @return array              fields in the form matching the search criteria
     */
    public function getFieldsByTypeAndName($field_types, $name)
    {
        if (!is_array($field_types)) {
            $field_types = [$field_types];
        }
        $out = [];

        for ($step=0; $step < $this->getNumSteps(); $step++) {
            $out = array_merge($out, $this->getStepFieldsByTypeAndName($field_types, $name, $step));
        }
        return $out;
    }

    /**
     * Get field by name
     *
     * @param string  $field_name field name
     * @param integer $step       step number where to find the field
     *
     * @return Element subclass field object
     */
    public function getField($field_name, $step = 0)
    {
        return isset($this->fields[$step][$field_name]) ? $this->fields[$step][$field_name] : null;
    }

    /**
     * Get the submit element which submitted the form
     *
     * @return action subclass the submitter
     */
    public function getTriggeringElement()
    {
        $fields = $this->getFieldsByType(['submit','button','image_button']);
        foreach ($fields as $field) {
            if ($field->getClicked() == true) {
                return $field;
            }
        }

        if (Form::isPartial()) {
            $triggering_id = $_REQUEST['triggering_element'];
            return Element::searchFieldById($this, $triggering_id);
        }

        return null;
    }

    /**
     * Get the form submit
     *
     * @return OrderedFunctions form submit function(s)
     */
    public function getSubmit()
    {
        return $this->submit;
    }

    /**
     * Set form submit functions list
     *
     * @param OrderedFunctions $submit set the form submit functions list
     *
     * @return Form
     */
    public function setSubmit($submit)
    {
        if (!($submit instanceof OrderedFunctions)) {
            $submit = new OrderedFunctions($submit, 'submitter');
        }
        $this->submit = $submit;
        return $this;
    }


    /**
     * Get the form validate
     *
     * @return OrderedFunctions form validate function(s)
     */
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * Set form validate functions list
     *
     * @param OrderedFunctions $validate set the form validate functions list
     *
     * @return Form
     */
    public function setValidate($validate)
    {
        if (!($validate instanceof OrderedFunctions)) {
            $validate = new OrderedFunctions($validate, 'validator');
        }
        $this->validate = $validate;
        return $this;
    }


    /**
     * Get the form id
     *
     * @return string the form id
     */
    public function getId()
    {
        return $this->form_id;
    }

    /**
     * Get the current step number
     *
     * @return integer current step
     */
    public function getCurrentStep()
    {
        return $this->current_step;
    }

    /**
     * Get ajax url
     *
     * @return string ajax form submit url
     */
    public function getAjaxUrl()
    {
        return $this->ajax_submit_url;
    }

    /**
     * renders form errors
     *
     * @return string errors as an html <li> list
     */
    public function showErrors()
    {
        return (!$this->hasErrors()) ? '' : "<li>".implode('</li><li>', $this->getErrors())."</li>";
    }

    /**
     * renders form highlights
     *
     * @return string highlights as an html <li> list
     */
    public function showHighlights()
    {
        return (!$this->hasHighlights()) ? '' : "<li>".implode('</li><li>', $this->getHighlights())."</li>";
    }

    /**
     * Sets inline error preference
     *
     * @param  boolean $inline_errors error preference
     * @return Form
     */
    public function setInlineErrors($inline_errors)
    {
        $this->inline_errors = $inline_errors;

        return $this;
    }

    /**
     * Returns inline error preference
     *
     * @return boolean errors should be presented inline after every elemen
     */
    public function getInlineErrors()
    {
        return $this->inline_errors;
    }

    /**
     * Returns inline error preference
     *
     * @return boolean errors should be presented inline after every elemen
     */
    public function errorsInline()
    {
        return $this->getInlineErrors();
    }


    /**
     * {@inheritdoc}. using this hook form elements can modify the form element
     */
    public function preRender()
    {
        if ($this->on_dialog == true) {
            $this->addJs('$("#'.$this->getFormId().'").dialog()');
        }

        foreach ($this->getFields($this->current_step) as $name => $field) {
            if (is_object($field) && method_exists($field, 'preRender')) {
                $field->preRender($this);
            }
        }
        $this->pre_rendered = true;
    }

    /**
     * renders the form
     *
     * @param  string $override_output_type output type
     * @return string                       the form html
     */
    public function renderHTML($override_output_type = null)
    {
        $output = '';
        $errors = '';
        $highlights = '';
        $fields_html = '';

        // render needs the form to be processed
        if (!$this->processed) {
            $this->processValue();
        }

        if (!is_string($override_output_type)) {
            $override_output_type = null;
        }
        $output_type = !empty($override_output_type) ? $override_output_type : $this->getOutputType();
        $output_type = trim(strtolower($output_type));
        if ($output_type == 'json' && empty($this->ajax_submit_url)) {
            $output_type = 'html';
        }

        if ($this->isValid() === false) {
            $errors = $this->showErrors();
            $this->setAttribute('class', trim($this->getAttribute('class').' with-errors'));
            if (!$this->errorsInline()) {
                foreach ($this->getFields($this->current_step) as $field) {
                    $errors .= $field->showErrors();
                }
            }
            if (trim($errors)!='') {
                $errors =  sprintf(FORMS_ERRORS_TEMPLATE, $errors);
            }
        }

        if ($this->hasHighlights()) {
            $highlights = $this->showHighlights();
            if (trim($highlights)!='') {
                $highlights =  sprintf(FORMS_HIGHLIGHTS_TEMPLATE, $highlights);
            }
        }

        $insertorder = array_flip($this->insert_field_order);
        $weights = $order = [];
        foreach ($this->getFields($this->current_step) as $key => $elem) {
            $weights[$key]  = $elem->getWeight();
            $order[$key] = $insertorder[$key];
        }
        if (count($this->getFields($this->current_step)) > 0) {
            array_multisort($weights, SORT_ASC, $order, SORT_ASC, $this->getFields($this->current_step));
        }


        foreach ($this->getFields($this->current_step) as $name => $field) {
            if (is_object($field) && method_exists($field, 'renderHTML')) {
                $fields_html .= $field->renderHTML($this);
            }
        }

        $attributes = $this->getAttributes(['action','method','id']);
        $js = $this->generateJs();

        if (Form::isPartial()) {
            // ajax request - form item event

            $jsondata = json_decode($_REQUEST['jsondata']);
            $callback = $jsondata->callback;
            if (is_callable($callback)) {
                /**
                 * @var Field $target_elem
                 */
                $target_elem = $callback($this);

                $html = $target_elem->renderHTML($this);

                if (count($target_elem->getCss()) > 0) {
                    $html .= '<style>'.implode("\n", $target_elem->getCss())."</style>";
                }

                $js = '';
                if (count($target_elem->getJs()) > 0) {
                    $js = "(function($){\n".
                    "\t$(document).ready(function(){\n".
                    "\t\t".implode(";\n\t\t", $target_elem->getJs()).";\n".
                    "\t});\n".
                    "})(jQuery);";
                }

                return json_encode([ 'html' => $html, 'js' => $js ]);
            }

            return false;
        }

        if (!empty($this->ajax_submit_url) && $this->getOutputType() == 'json' && $output_type == 'html') {
            // print initial js for ajax form

            $output = "<script type=\"text/javascript\">"
            .preg_replace(
                "/\s+/",
                " ",
                str_replace(
                    "\n",
                    "",
                    "(function(\$){
          \$(document).ready(function(){
            var {$this->getId()}_attachFormBehaviours = function (){
              \$('#{$this->getId()}').submit(function(evt){
                evt.preventDefault();
                \$.post( \"{$this->getAjaxUrl()}\", \$('#{$this->getId()}').serialize(), function( data ) {
                  var response;
                  if(typeof data =='object') response = data;
                  else response = \$.parseJSON(data);
                  \$('#{$this->getId()}-formcontainer').html('');
                  \$(response.html).appendTo( \$('#{$this->getId()}-formcontainer') );
                  if( \$.trim(response.js) != '' ){
                    eval( response.js );
                  };
                  {$this->getId()}_attachFormBehaviours();
                });
                return false;
              });
            };
            \$.getJSON('{$this->getAjaxUrl()}',function(response){
              \$(response.html).appendTo( \$('#{$this->getId()}-formcontainer') );
              if( \$.trim(response.js) != '' ){
                eval( response.js );
              };
              {$this->getId()}_attachFormBehaviours();
            });
          });
        })(jQuery);"
                )
            ).
            "</script>\n".
            "<div id=\"{$this->getId()}-formcontainer\"></div>";
        } else {
            switch ($output_type) {
                case 'json':
                    $output = ['html'=>'','js'=>'','is_submitted'=>$this->isSubmitted()];

                    $output['html']  = $this->getElementPrefix();
                    $output['html'] .= $this->getPrefix();
                    $output['html'] .= $highlights;
                    $output['html'] .= $errors;
                    $output['html'] .= "<form action=\"{$this->action}\" id=\"{$this->form_id}\"";
                    $output['html'] .= "method=\"{$this->method}\"{$attributes}>\n";
                    $output['html'] .= $fields_html;
                    $output['html'] .= "<input type=\"hidden\" name=\"form_id\" value=\"{$this->form_id}\" />\n";
                    if (!$this->no_token) {
                        $output['html'] .= "<input type=\"hidden\" name=\"form_token\"".
                        " value=\"{$this->form_token}\" />\n";
                    }
                    if ($this->getNumSteps() > 1) {
                        $output['html'] .= "<input type=\"hidden\" name=\"current_step\" ".
                        "value=\"{$this->current_step}\" />\n";
                    }
                    $output['html'] .= "</form>\n";
                    $output['html'] .= $this->getSuffix();
                    $output['html'] .= $this->getElementSuffix();

                    if (count($this->getCss())>0) {
                        $output['html'] .= "<style>".implode("\n", $this->getCss())."</style>";
                    }

                    if (!empty($js)) {
                        $output['js'] = $js;
                    }

                    $output = json_encode($output);
                    break;

                case 'html':
                default:
                    $output = $this->getElementPrefix();
                    $output .= $this->getPrefix();
                    $output .= $highlights;
                    $output .= $errors;
                    $output .= "<form action=\"{$this->action}\" id=\"{$this->form_id}\"";
                    $output .= "method=\"{$this->method}\"{$attributes}>\n";
                    $output .= $fields_html;
                    $output .= "<input type=\"hidden\" name=\"form_id\" value=\"{$this->form_id}\" />\n";
                    if (!$this->no_token) {
                        $output .= "<input type=\"hidden\" name=\"form_token\" value=\"{$this->form_token}\" />\n";
                    }
                    if ($this->getNumSteps() > 1) {
                        $output .= "<input type=\"hidden\" name=\"current_step\" value=\"{$this->current_step}\" />\n";
                    }
                    $output .= "</form>\n";
                    if (count($this->getCss())>0) {
                        $output .= "<style>".implode("\n", $this->getCss())."</style>";
                    }

                    if (!empty($js)) {
                        $output .= "\n<script type=\"text/javascript\">\n".$js."\n</script>\n";
                    }
                    $output .= $this->getSuffix();
                    $output .= $this->getElementSuffix();
                    break;
            }
        }
        return $output;
    }

    /**
     * renders the form
     *
     * @param      string $override_output_type output type
     * @return     string                       the form html
     * @deprecated
     */
    public function render($override_output_type = null)
    {
        return $this->renderHTML($override_output_type);
    }

    /**
     * generate the js string
     *
     * @return string the js into a jquery sandbox
     */
    public function generateJs()
    {
        if (!$this->pre_rendered) {
            $this->preRender();
        } // call all elements pre_render, so they can attach js to the form element;

        $js = array_filter(array_map('trim', $this->getJs()));
        if (!empty($js) && !$this->js_generated) {
            foreach ($js as &$js_string) {
                if ($js_string[strlen($js_string)-1] == ';') {
                    $js_string = substr($js_string, 0, strlen($js_string)-1);
                }
            }

            $this->js_generated = true;
            return "(function($){\n".
            "\t$(document).ready(function(){\n".
            "\t\t".implode(";\n\t\t", $js).";\n".
            "\t});\n".
            "})(jQuery);";
        }
        return "";
    }


    /**
     * toString magic method
     *
     * @return string the form html
     */
    public function __toString()
    {
        try {
            return $this->renderHTML();
        } catch (Exception $e) {
            return $e->getMessage()."\n".$e->getTraceAsString();
        }
    }


    /**
     * on_add_return overload
     *
     * @return string 'this'
     */
    protected function onAddReturn()
    {
        return 'this';
    }

    /**
     * Set the form definition function name
     *
     * @param string $function_name form definition function name
     */
    public function setDefinitionFunction($function_name)
    {
        $this->definition_function = $function_name;
        return $this;
    }

    /**
     * Get the form definition function body
     *
     * @return string form definition function body
     */
    public function getDefinitionBody()
    {
        $body = false;

        try {
            $definition_name = (!empty($this->definition_function) ? $this->definition_function : $this->getFormId());
            if (is_callable($definition_name)) {
                if (function_exists($definition_name)) {
                    $func = new \ReflectionFunction($definition_name);
                } else {
                    $func = new \ReflectionMethod($definition_name);
                }

                if (is_object($func)) {
                    $filename = $func->getFileName();
                    $start_line = $func->getStartLine() - 1; // it's actually - 1,
                                                             // otherwise you wont get the function() block
                    $end_line = $func->getEndLine();
                    $length = $end_line - $start_line;

                    $source = file($filename);
                    $body = implode("", array_slice($source, $start_line, $length));
                    $body = str_replace('<', '&lt;', $body);
                    $body = str_replace('>', '&gt;', $body);
                }
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
        return $body;
    }
}
