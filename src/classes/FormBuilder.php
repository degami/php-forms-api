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
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi;

use Degami\PHPFormsApi\Accessories\SessionBag;
use Degami\PHPFormsApi\Exceptions\FormException;

/**
 * The form builder class
 */
class FormBuilder
{
    /**
     * Check if session is present
     *
     * @return bool
     */
    public static function sessionPresent()
    {
        return defined('PHP_VERSION_ID') && PHP_VERSION_ID > 54000 ?
          session_status() != PHP_SESSION_NONE : trim(session_id()) != '';
    }

    /**
     * Get Session Bag
     * @param boolean $refresh force refresh
     *
     * @return SessionBag
     */
    public static function getSessionBag($refresh = false)
    {
        /** @var SessionBag */
        static $session_bag = null;
        if (!$session_bag instanceof SessionBag || $refresh == true) {
            $session_bag = new SessionBag();
        }
        return $session_bag;
    }

    /**
     * Returns the form_id
     *
     * @param  callable $function_name the function name
     * @return string                   the form_id
     */
    public static function getFormId($function_name)
    {
        if (is_string($function_name)) {
            return $function_name;
        }
        if (is_callable($function_name) && is_array($function_name)) {
            return $function_name[1];
        }
        return 'cs_form';
    }

    /**
     * Returns callable function name string
     *
     * @param  callable $function_name callable element
     * @return string                 the function name
     */
    public static function getDefinitionFunctionName($function_name)
    {
        if (is_string($function_name)) {
            return $function_name;
        }
        if (is_callable($function_name) && is_array($function_name)) {
            if (is_string($function_name[0])) {
                return $function_name[0].'::'.$function_name[1];
            }
            if (is_object($function_name[0])) {
                return get_class($function_name[0]).'::'.$function_name[1];
            }
        }

        return null;
    }

    /**
     * Returns a form object.
     * This function calls the form definitor function passing an
     * initial empty form object and the form state
     *
     * @param callable $callable     form_id (and also form definitor function name)
     * @param array    &$form_state  form state by reference
     * @param array    $form_options additional form constructor options
     *
     * @return Form             a new form object
     * @throws FormException
     */
    public static function buildForm($callable, &$form_state, $form_options = [])
    {
        $before = memory_get_usage();

        $form_id = FormBuilder::getFormId($callable);
        $function_name = FormBuilder::getDefinitionFunctionName($callable);

        $form = new Form(
            [
            'form_id' => $form_id,
            'definition_function' => $function_name,
            ] + $form_options
        );

        $form_state += FormBuilder::getRequestValues($function_name);
        if (is_callable($function_name)) {
            $form_obj = call_user_func_array(
                $function_name,
                array_merge(
                    [$form, &$form_state],
                    $form_state['build_info']['args']
                )
            );
            if (! $form_obj instanceof Form) {
                throw new FormException(
                    "Function {$function_name} does not return a valid form object",
                    1
                );
            }

            $form = $form_obj;
            $form->setDefinitionFunction($function_name);
            self::getSessionBag()->ensurePath('/form_definition');
            self::getSessionBag()->form_definition[$form->getId()] = $form->toArray();
        }

        $after = memory_get_usage();
        $form->allocatedSize = ($after - $before);

        return $form;
    }

    /**
     * Get a new form object
     *
     * @param string $form_id form_id (and also form definitor function name)
     *
     * @return Form         a new form object
     * @throws FormException
     */
    public static function getForm($form_id)
    {
        $form_state = [];
        $args = func_get_args();
        // Remove $form_id from the arguments.
        array_shift($args);
        $form_state['build_info']['args'] = $args;

        $form = FormBuilder::buildForm($form_id, $form_state);
        return $form;
    }

    /**
     * Returns rendered form's html string
     *
     * @param  string $form_id form_id (and also form definitor function name)
     * @return string          form html
     * @throws FormException
     */
    public static function renderForm($form_id)
    {
        $form = FormBuilder::getForm($form_id);
        return $form->renderHTML();
    }

    /**
     * Prepares the form_state array
     *
     * @param  string $form_id the form_id
     * @return array           the form_state array
     */
    public static function getRequestValues($form_id)
    {
        $out = ['input_values' => [] , 'input_form_definition' => null];
        foreach (['_POST' => $_POST,'_GET' => $_GET,'_REQUEST' => $_REQUEST] as $key => $array) {
            if (!empty($array['form_id']) && $array['form_id'] == $form_id) {
                $out['input_values'] = $array; //array_merge($out, $array);
                $out['input_values']['__values_container'] = $key; //array_merge($out, $array);

                if (isset($array['form_id']) && isset(self::getSessionBag()->form_definition[ $array['form_id'] ])) {
                    $out['input_form_definition'] = self::getSessionBag()->form_definition[ $array['form_id'] ];
                }

                break;
            }
        }
        return $out;
    }

    /**
     * guess form field type by value
     *
     * @param  mixed       $value        value to find field to
     * @param  string|null $element_name element name
     * @return array                      form field info
     */
    public static function guessFormType($value, $element_name = null)
    {
        $default_value = $value;
        $vtype = gettype($default_value);
        switch ($vtype) {
            case 'object':
                $vtype = get_class($default_value);
                break;
        }

        $type = null;
        $validate = [];
        switch (strtolower($vtype)) {
            case 'string':
                $type = 'textfield';
                break;
            case 'integer':
                $type = 'spinner';
                $validate = ['integer'];
                break;
            case 'float':
            case 'double':
                $type = 'textfield';
                $validate = ['numeric'];
                break;
            case 'boolean':
            case 'bool':
                $type = 'checkbox';
                break;
            case 'datetime':
                $type = 'datetime';
                /** @var \DateTime $default_value */
                $default_value = [
                    'year'    => $default_value->format('Y'),
                    'month'   => $default_value->format('m'),
                    'day'     => $default_value->format('d'),
                    'hours'   => $default_value->format('H'),
                    'minutes' => $default_value->format('i'),
                    'seconds' => $default_value->format('s'),
                ];
                break;
            case 'date':
                $type = 'date';

                /** @var \DateTime $default_value */
                $default_value = [
                    'year'    => $default_value->format('Y'),
                    'month'   => $default_value->format('m'),
                    'day'     => $default_value->format('d'),
                    'hours'   => $default_value->format('H'),
                    'minutes' => $default_value->format('i'),
                    'seconds' => $default_value->format('s'),
                ];

                break;
            case 'array':
            case 'object':
                $type = 'textarea';
                $default_value = json_encode($default_value);
                break;
        }

        if ($type == null && ($default_value == null || is_scalar($default_value))) {
            switch ($element_name) {
                case 'id':
                case 'surname':
                case 'name':
                    $type = 'textfield';
                    break;
                case 'email':
                    $type = 'textfield';
                    $validate = ['email'];
                    break;
                case 'date':
                case 'day':
                case 'birth':
                case 'birthdate':
                case 'birthday':
                    $type = 'date';
                    break;
                case 'time':
                    $type = 'time';
                    break;
                default:
                    break;
            }
        }

        if ($type == null) {
            $type = 'textfield';
        }
        return [ 'type' => $type, 'validate' => $validate, 'default_value' => $default_value ];
    }

    /**
     * Get a form to represent given object
     *
     * @param  Form  $form        initial form object
     * @param  array &$form_state form state
     * @param  mixed $object      object to represent
     * @return Form                form object
     */
    public static function objFormDefinition(Form $form, &$form_state, $object)
    {
        $form->setFormId(get_class($object));
        $fields = get_object_vars($object) + get_class_vars(get_class($object));

        $fieldset = $form->addField(
            get_class($object),
            [
                'type' => 'fieldset',
                'title' => get_class($object),
            ]
        );

        foreach ($fields as $k => $v) {
            list($type, $validate, $default_value) = array_values(FormBuilder::guessFormType($v, $k));
            $fieldset->addField(
                $k,
                [
                    'type' => $type,
                    'title' => $k,
                    'validate' => $validate,
                    'default_value' => $default_value,
                ]
            );
        }

        $form
        ->addField(
            'submit',
            ['type' => 'submit']
        );

        return $form;
    }

    /**
     * Returns a form object representing the object parameter
     *
     * @param object $object the object to map
     *
     * @return Form form object
     * @throws FormException
     */
    public static function objectForm($object)
    {
        $form_state = [];
        $form_state['build_info']['args'] = [$object];

        $form = FormBuilder::buildForm(
            [__CLASS__, 'objFormDefinition'],
            $form_state,
            [
                'submit' => [strtolower(get_class($object).'_submit')],
                'validate' => [strtolower(get_class($object).'_validate')],
            ]
        );
        return $form;
    }
}
