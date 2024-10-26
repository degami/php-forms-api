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

use Degami\Basics\DataBag;
use \Exception;
use Degami\Basics\Traits\ToolsTrait as BasicToolsTrait;
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
 * The form object class
 */
class Form extends Element
{
    use BasicToolsTrait, Tools, Validators, Processors, Containers ;

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
     * "form is already processed" flag
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
     * form state array
     *
     * @var array
     */
    private $form_state = [];

    public $allocatedSize = 0;

    /**
     * Class constructor
     *
     * @param array $options build options
     */
    public function __construct($options = [])
    {
        parent::__construct();

        $this->build_options = $options;
        $this->session_bag = new SessionBag();
        $this->container_tag = FORMS_DEFAULT_FORM_CONTAINER_TAG;
        $this->container_class = FORMS_DEFAULT_FORM_CONTAINER_CLASS;

        $this->setClassProperties($options);

        $has_submitter = false;
        foreach ($this->submit as $s) {
            if (!empty($s) && is_callable($s)) {
                $has_submitter = true;
            }
        }
        if (!$has_submitter) {
            array_push($this->submit, "{$this->form_id}_submit");
        }

        // if (empty($this->submit) || !is_callable($this->submit)) {
        //   array_push($this->submit, "{$this->form_id}_submit");
        // }

        $has_validator = false;
        foreach ($this->validate as $v) {
            if (!empty($v) && is_callable($v)) {
                $has_validator = true;
            }
        }
        if (!$has_validator) {
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
    public function getSessionBag(): SessionBag
    {
        return $this->session_bag;
    }

    /**
     * Set form id
     *
     * @param string $form_id set the form id used for getting the submit function name
     * @return self
     */
    public function setFormId(string $form_id): Form
    {
        $this->form_id = $form_id;
        return $this;
    }

    /**
     * Get the form id
     *
     * @return string form id
     */
    public function getFormId(): string
    {
        return $this->form_id;
    }


    /**
     * Set the form action attribute
     *
     * @param string $action the form action url
     * @return self
     */
    public function setAction(string $action): Form
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get the form action url
     *
     * @return string the form action
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set the form method
     *
     * @param string $method form method
     * @return self
     */
    public function setMethod(string $method): Form
    {
        $this->method = strtolower(trim($method));
        return $this;
    }

    /**
     * Get the form method
     *
     * @return string form method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the ajax submit url used for form submission
     *
     * @param string $ajax_submit_url ajax endpoint url
     * @return self
     */
    public function setAjaxSubmitUrl(string $ajax_submit_url): Form
    {
        $this->ajax_submit_url = $ajax_submit_url;
        return $this;
    }

    /**
     * Get the ajax form submission url
     *
     * @return string the form ajax submission url
     */
    public function getAjaxSubmitUrl(): string
    {
        return $this->ajax_submit_url;
    }

    /**
     * Set the form render output type
     *
     * @param string $output_type output type ( 'html' / 'json' )
     * @return Form
     */
    public function setOutputType(string $output_type): Form
    {
        $this->output_type = $output_type;
        return $this;
    }

    /**
     * Get the form render output type
     *
     * @return string form output type
     */
    public function getOutputType(): string
    {
        return $this->output_type;
    }


    /**
     * Set no_token flag
     *
     * @param boolean $no_token no token flag
     * @return Form
     */
    public function setNoToken(bool $no_token): Form
    {
        $this->no_token = $no_token;
        return $this;
    }

    /**
     * Get no_token flag
     *
     * @return boolean no token flag
     */
    public function getNoToken(): bool
    {
        return $this->no_token;
    }


    /**
     * Set the form on_dialog preference
     *
     * @param string $on_dialog the form on_dialog preference
     * @return Form
     */
    public function setOnDialog(string $on_dialog): Form
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
    public function getFormToken(): string
    {
        return $this->form_token;
    }

    /**
     * Return form elements (all the steps) values
     *
     * @return FormValues form values
     */
    public function getValues(): FormValues
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
     * @return     FormValues form values
     */
    public function values(): FormValues
    {
        return $this->getValues();
    }

    /**
     * Get current step elements values
     *
     * @return array step values
     */
    private function getCurrentStepValues(): array
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
     *
     * @return self
     */
    public function resetField(): Form
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

        return $this;
    }

    /**
     * resets the form
     *
     * @return self
     */
    public function reset(): Form
    {
        return $this->resetField();
    }

    /**
     * Check if form is submitted
     *
     * @return boolean form is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->submitted;
    }


    /**
     * Check if form is processed
     *
     * @return boolean form is processed
     */
    public function isProcessed(): bool
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
     * {@inheritdocs}
     *
     * @param array $request request array
     */
    private function alterRequest(array &$request)
    {
        foreach ($this->getFields($this->current_step) as $field) {
            $field->alterRequest($request);
        }
    }

    /**
     * copies the request values into the right form element
     *
     * @param array|DataBag   $request request array
     * @param int $step    step number
     */
    private function injectValues($request, int $step)
    {
        foreach ($this->getFields($step) as $name => $field) {
            if ($field instanceof FieldsContainer) {
                $field->processValue($request);
            } elseif (($requestValue = static::traverseArray($request, $field->getName())) != null) {
                $field->processValue($requestValue);
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
    private function saveStepRequest(array $request)
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
     * save form_state array into form object, for use into process function
     *
     * @param array &$form_state [description]
     * @return self
     */
    public function setFormState(&$form_state = []): Form
    {
        $this->form_state = $form_state;
        return $this;
    }

    /**
     * To array override
     *
     * @return array array representation for the element properties
     */
    public function toArray(): array
    {
        $values = parent::toArray();
        unset($values['form_state']);
        return $values;
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

            // recursive url_decode request elements
            array_walk_recursive($request, function (&$item, $key) {
                if (is_scalar($item)) {
                    $item = urldecode($item);
                }
            });

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
                        $submitresult = call_user_func_array($submit_function, [ &$this, &$this->form_state, $request ]);
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
     */
    public function process($values = [])
    {
        $this->processValue($values);
    }

    /**
     * Check if form is valid / NULL if form is on the first render
     *
     * @return boolean form is valid
     */
    public function isValid(): ?bool
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
                    if (is_callable($validate_function)) {
                        $error = call_user_func_array($validate_function, [
                            &$this,
                            &$this->form_state,
                            (strtolower($this->method) == 'post') ? $_POST : $_GET
                        ]);
                        if ($error !== true) {
                            $this->valid = false;
                            $this->addError(
                                is_string($error) ? $this->getText($error) : $this->getText('Error. Form is not valid'),
                                FormBuilder::getCallablaStringName($validate_function)
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
     * Add field to form
     *
     * @param string $name field name
     * @param mixed $field field to add, can be an array or a field subclass
     * @param int $step step to add the field to
     * @return mixed
     * @throws Exceptions\FormException
     */
    public function addField(string $name, $field, int $step = 0)
    {
        $field = $this->getFieldObj($name, $field);
        $field->setParent($this);

        $this->setField($name, $field, $step);
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
     * @param string $name field name
     * @param int $step field step
     * @return self
     */
    public function removeField(string $name, int $step = 0): Form
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
    private function getNumSteps(): int
    {
        return count($this->fields);
    }

    /**
     * Check if current is the final step
     *
     * @return boolean this is the final step
     */
    private function isFinalStep(): bool
    {
        return ($this->getCurrentStep() >= $this->getNumSteps());
    }

    /**
     * Check if this request is a "partial" ( used in elements ajax requests )
     *
     * @return boolean [description]
     */
    public static function isPartial(): bool
    {
        return (isset($_REQUEST['partial']) && $_REQUEST['partial'] == 'true');
    }

    /**
     * Get the fields array by reference
     *
     * @param  int $step step number
     * @return array        the array of elements for the step specified
     */
    public function &getFields(int $step = 0): array
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
     * @param  ?string       $name        field name
     * @param  int      $step        step number
     * @return array               the array of fields matching the search criteria
     */
    private function getStepFieldsByTypeAndName($field_types, string $name = null, int $step = 0): array
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
     * @param array $field_types field types
     * @return array              fields in the form
     */
    public function getFieldsByType(array $field_types): array
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
     * @param array $field_types field types
     * @param string $name field name
     * @return array              fields in the form matching the search criteria
     */
    public function getFieldsByTypeAndName(array $field_types, string $name): array
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
     * @param string $field_name field name
     * @param int $step step number where to find the field
     *
     * @return Element subclass field object
     */
    public function getField(string $field_name, int $step = 0): ?Element
    {
        return isset($this->fields[$step][$field_name]) ? $this->fields[$step][$field_name] : null;
    }

    /**
     * Set field
     *
     * @param string $field_name field name
     * @param Element $field subclass field object
     * @param integer $step step number where to put the field
     * @return self
     */
    public function setField(string $field_name, Element $field, $step = 0): Form
    {
        $field->setName($field_name);
        $this->fields[$step][$field_name] = $field;
        return $this;
    }

    /**
     * Get the submit element which submitted the form
     *
     * @return ?Element subclass the submitter
     */
    public function getTriggeringElement(): ?Element
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
     * @param array|OrderedFunctions $submit set the form submit functions list
     * @return self
     */
    public function setSubmit($submit): Form
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
     * @param array|OrderedFunctions $validate set the form validate functions list
     * @return self
     */
    public function setValidate($validate): Form
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
    public function getId(): string
    {
        return $this->form_id;
    }

    /**
     * Get the current step number
     *
     * @return int current step
     */
    public function getCurrentStep(): int
    {
        return $this->current_step;
    }

    /**
     * Get ajax url
     *
     * @return string ajax form submit url
     */
    public function getAjaxUrl(): string
    {
        return $this->ajax_submit_url;
    }

    /**
     * renders form errors
     *
     * @return string errors as an html <li> list
     */
    public function showErrors(): string
    {
        return $this->notifications->renderHTML('error');
    }

    /**
     * renders form highlights
     *
     * @return string highlights as an html <li> list
     */
    public function showHighlights(): string
    {
        return $this->notifications->renderHTML('highlight');
    }

    /**
     * Sets inline error preference
     *
     * @param boolean $inline_errors error preference
     * @return self
     */
    public function setInlineErrors(bool $inline_errors): Form
    {
        $this->inline_errors = $inline_errors;

        return $this;
    }

    /**
     * Returns inline error preference
     *
     * @return boolean errors should be presented inline after every element
     */
    public function getInlineErrors(): bool
    {
        return $this->inline_errors;
    }

    /**
     * Returns inline error preference
     *
     * @return boolean errors should be presented inline after every element
     */
    public function errorsInline(): bool
    {
        return $this->getInlineErrors();
    }


    /**
     * {@inheritdocs}.
     * using this hook form elements can modify the form element
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
     * @param  ?string $override_output_type output type
     * @return string                       the form html
     */
    public function renderHTML(string $override_output_type = null)
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

        if (!Form::isPartial() && $this->isValid() === false) {
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
            $order[$key] = isset($insertorder[$key]) ? $insertorder[$key] : PHP_INT_MAX;
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
            $callback = unserialize(base64_decode($jsondata->callback));
            if (is_callable($callback)) {
                /**
                 * @var Field $target_elem
                 */
                $target_elem = call_user_func_array($callback, [$this]);

                $html = $target_elem->renderHTML($this);

                if (count($target_elem->getCss()) > 0) {
                    $html .= '<style>'.implode("\n", $target_elem->getCss())."</style>";
                }

                $js = '';
                if (count($target_elem->getJs()) > 0) {
                    $js = $this->encapsulateJs($target_elem->getJs());
                }

                return json_encode([ 'html' => $html, 'js' => $js ]);
            }

            return false;
        }

        if (!empty($this->ajax_submit_url) && $this->getOutputType() == 'json' && $output_type == 'html') {
            // print initial js for ajax form

            $initial_js = ["var {$this->getId()}_attachFormBehaviours = function (){
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
            };",
            "\$.getJSON('{$this->getAjaxUrl()}',function(response){
              \$(response.html).appendTo( \$('#{$this->getId()}-formcontainer') );
              if( \$.trim(response.js) != '' ){
                eval( response.js );
              };
              {$this->getId()}_attachFormBehaviours();
            });"];

            $output = "<script type=\"text/javascript\">".
                        $this->encapsulateJs(array_map([$this, 'minifyJs'], $initial_js)).
                        "</script>\n".
                        "<div id=\"{$this->getId()}-formcontainer\"></div>";
        } else {
            $form_tag_html = $this->getElementPrefix();
            $form_tag_html .= $this->getPrefix();
            $form_tag_html .= $highlights;
            $form_tag_html .= $errors;
            $form_tag_html .= "<form action=\"{$this->action}\" id=\"{$this->form_id}\" ";
            $form_tag_html .= "method=\"{$this->method}\"{$attributes}>\n";
            $form_tag_html .= $fields_html;
            $form_tag_html .= "<input type=\"hidden\" name=\"form_id\" value=\"{$this->form_id}\" />\n";
            if (!$this->no_token) {
                $form_tag_html .= "<input type=\"hidden\" name=\"form_token\" value=\"{$this->form_token}\" />\n";
            }
            if ($this->getNumSteps() > 1) {
                $form_tag_html .= "<input type=\"hidden\" name=\"current_step\" value=\"{$this->current_step}\" />\n";
            }
            $form_tag_html .= "</form>\n";
            $form_tag_html .= $this->getSuffix();
            $form_tag_html .= $this->getElementSuffix();

            switch ($output_type) {
                case 'json':
                    $output = ['html'=>'','js'=>'','is_submitted'=>$this->isSubmitted()];

                    $output['html']  = $form_tag_html;

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
                    $output = $form_tag_html;

                    if (count($this->getCss())>0) {
                        $output .= "<style>".implode("\n", $this->getCss())."</style>";
                    }

                    if (!empty($js)) {
                        $output .= "\n<script type=\"text/javascript\">\n".$js."\n</script>\n";
                    }
                    break;
            }
        }
        return $output;
    }

    /**
     * renders the form
     *
     * @param      ?string $override_output_type output type
     * @return     string                       the form html
     */
    public function render(string $override_output_type = null)
    {
        return $this->renderHTML($override_output_type);
    }

    /**
     * generate the js string
     *
     * @return string the js into a jquery sandbox
     */
    public function generateJs(): string
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
            return $this->encapsulateJs($js);
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
    protected function onAddReturn(): string
    {
        return 'this';
    }

    /**
     * Set the form definition function name
     *
     * @param string $function_name form definition function name
     * @return self
     */
    public function setDefinitionFunction(string $function_name): Form
    {
        $this->definition_function = $function_name;
        return $this;
    }

    /**
     * Get the form definition function body
     *
     * @return string|false form definition function body
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

    private function encapsulateJs($js_array, $jquery_var_name = 'jQuery'): string
    {
        if (!is_array($js_array)) {
            $js_array = [$js_array];
        }

        return "(function(\$){\n".
        "\t$(document).ready(function(){\n".
        "\t\t".implode(";\n\t\t", $js_array).";\n".
        "\t});\n".
        "})({$jquery_var_name});";
    }
}
