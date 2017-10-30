<?php
/**
 * PHP FORMS API
 * @package degami/php-forms-api
 */
/* #########################################################
   ####                 ACCESSORIES                     ####
   ######################################################### */

namespace Degami\PHPFormsApi;
use \Exception;

/**
 * the form builder class
 */
class form_builder {

  static function session_present(){
    return defined('PHP_VERSION_ID') && PHP_VERSION_ID > 54000 ? session_status() != PHP_SESSION_NONE : trim(session_id()) != '';
  }

  /**
   * returns the form_id
   * @param  callable $function_name  the function name
   * @return string                   the form_id
   */
  static function get_form_id( $function_name ){
    if( is_string($function_name) ) return $function_name;
    if( is_callable($function_name) && is_array($function_name) ) return $function_name[1];
    return 'cs_form';
  }

  /**
   * returns callable function name string
   * @param  callabe $function_name callable element
   * @return string                 the function name
   */
  static function get_definition_function_name( $function_name ){
    if( is_string($function_name) ) return $function_name;
    if( is_callable($function_name) && is_array($function_name) ){
      if( is_string( $function_name[0]) ) return $function_name[0].'::'.$function_name[1];
      if( is_object( $function_name[0]) ) return get_class($function_name[0]).'::'.$function_name[1];
    }

    return NULL;
  }

  /**
   * returns a form object.
   * this function calls the form definitor function passing an initial empty form object and the form state
   * @param  callable $callable  form_id (and also form definitor function name)
   * @param  array &$form_state  form state by reference
   * @param  array $form_options  additional form constructor options
   * @return form             a new form object
   */
  static function build_form($callable, &$form_state, $form_options = []){
    $before = memory_get_usage();

    $form_id = form_builder::get_form_id($callable);
    $function_name = form_builder::get_definition_function_name( $callable );

    $form = new form([
        'form_id' => $form_id,
        'definition_function' => $function_name,
      ] + $form_options);

    $form_state += form_builder::get_request_values($function_name);
    if(is_callable($function_name)){
      $form_obj = call_user_func_array($function_name , array_merge( [$form, &$form_state], $form_state['build_info']['args']) );
      if( ! $form_obj instanceof form ){
        throw new Exception("Error. function {$function_name} does not return a valid form object", 1);
      }

      $form = $form_obj;
      $form->set_definition_function( $function_name );
      $_SESSION['form_definition'][$form->get_id()] = $form->toArray();
    }

    $after = memory_get_usage();
    $form->allocatedSize = ($after - $before);

    return $form;
  }

  /**
   * get a new form object
   * @param  string $form_id form_id (and also form definitor function name)
   * @return form         a new form object
   */
  static function get_form($form_id){
    $form_state = [];
    $args = func_get_args();
    // Remove $form_id from the arguments.
    array_shift($args);
    $form_state['build_info']['args'] = $args;

    $form = form_builder::build_form($form_id, $form_state);
    return $form;
  }

  /**
   * returns rendered form's html string
   * @param  string $form_id form_id (and also form definitor function name)
   * @return string          form html
   */
  static function render_form($form_id){
    $form = form_builder::get_form($form_id);
    return $form->render();
  }

  /**
   * prepares the form_state array
   * @param  string $form_id the form_id
   * @return array           the form_state array
   */
  static function get_request_values($form_id){
    $out = ['input_values' => [] , 'input_form_definition'=>NULL];
    foreach(['_POST' => $_POST,'_GET' => $_GET,'_REQUEST' => $_REQUEST] as $key => $array){
      if(!empty($array['form_id']) && $array['form_id'] == $form_id){
        $out['input_values'] = $array; //array_merge($out, $array);
        $out['input_values']['__values_container'] = $key; //array_merge($out, $array);

        if(isset($array['form_id']) && isset($_SESSION['form_definition'][ $array['form_id'] ]) ){
          $out['input_form_definition'] = $_SESSION['form_definition'][ $array['form_id'] ];
        }

        break;
      }
    }
    return $out;
  }

  static function guessFormType( $value, $element_name = NULL ){
    $default_value = $value;
    $vtype = gettype( $default_value );
    switch( $vtype ){
      case 'object':
        $vtype = get_class( $default_value );
        break;
    }

    $type = NULL;
    $validate = [];
    switch ( strtolower($vtype) ){
      case 'string':
        $type = 'textfield';
        break;
      case 'integer':
        $type = 'spinner';$validate = ['integer'];
        break;
      case 'float':
      case 'double':
        $type = 'textfield';$validate = ['numeric'];
        break;
      case 'boolean':
      case 'bool':
        $type = 'checkbox';
        break;
      case 'datetime':
        $type = 'datetime';

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

    if( $type == NULL && ( $default_value == NULL || is_scalar($default_value) ) ){
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

    if( $type == NULL ) $type = 'textfield';
    return [ 'type' => $type, 'validate' => $validate, 'default_value' => $default_value ];
  }

  static function objFormDefinition(form $form, &$form_state, $object){
    $form->set_form_id( get_class($object) );
    $fields = get_object_vars($object) + get_class_vars( get_class($object) );

    $fieldset = $form->add_field( get_class($object), [
      'type' => 'fieldset',
      'title' => get_class($object),
    ]);

    foreach( $fields as $k => $v ){
      list($type, $validate, $default_value) = array_values( form_builder::guessFormType($v, $k) );
      $fieldset->add_field( $k, [
        'type' => $type,
        'title' => $k,
        'validate' => $validate,
        'default_value' => $default_value,
      ]);
    }

    $form
      ->add_field('submit', [
        'type' => 'submit',
      ]);

    return $form;
  }

  /**
   * returns a form object representing the object parameter
   * @param object $object the object to map
   * @return form form object
   */
  static function object_form( $object ){
    $form_state = [];
    $form_state['build_info']['args'] = [$object];

    $form = form_builder::build_form(
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
