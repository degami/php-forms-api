<?php
/*
function __($string){
  return str_repeat($string.' ' , 2);
}
*/
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

require_once '../vendor/autoload.php';
require_once 'recaptchalib.php';

// Generate a simple contact form
function contactform(FAPI\form $form, &$form_state){
 // $form = new FAPI\form(array(
  //   'form_id' => 'contact',
  // ));
  //
  $form->set_inline_errors(TRUE); //->set_on_dialog(TRUE);

  $form
  ->add_field('fieldset', array(
    'type' => 'tabs',
    'title' => 'Contact',
  ))->add_tab('Contact')
  ->add_field('name', array(
    'type' => 'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    'title' => 'Your name',
  ))
  ->add_field('email', array(
    'type' => 'textfield',
    'validate' => array('required', 'email'),
    'title' => 'Your email address',
  )) 
  ->add_field('message', array(
    'type' => 'tinymce',
    'postprocess' => array('xss'),
    'title' => 'Your message',
  ))
  ->add_field('switcher', array(
    'type' => 'switchbox',
    'title' => 'Yes or No',
    'default_value' => 1,
//    'default_value' => 'a',
//    'yes_value' => 'a', 'yes_label' => 'A value',
//    'no_value' => 'b', 'no_label' => 'B value',
  ))
  ->add_field('captcha', array(
    'type' => 'math_captcha',
    'title' => 'Check this out!',
    'pre_filled' => TRUE,
  ))
  ->add_field('submit', array(
    'type' => 'submit',
  ));

  return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//



// Generate a simple contact form

function contactform_ajax(FAPI\form $form, &$form_state){
  // $form = new FAPI\form(array(
  //   'form_id' => __FUNCTION__,
  //   'ajax_submit_url' => 'ajax_url.php',
  //   'output_type' => 'json',
  // ));

  $form
    ->set_form_id(__FUNCTION__)
    ->set_ajax_submit_url('ajax_url.php')
    ->set_output_type('json');

  $form->add_field('name', array(
    'type' => 'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    'title' => 'Your name',
  ));
  $form->add_field('email', array(
    'type' => 'textfield',
    'validate' => array('required', 'email'),
    'title' => 'Your email address',
  ));
  $form->add_field('message', array(
    'type' => 'textarea',
    'postprocess' => array('xss'),
    'title' => 'Your message',
  ));
  $form->add_field('submit', array(
    'type' => 'submit',
  ));
  $form->add_field('message2', array(
    'type' => 'textarea',
    'postprocess' => array('xss'),
    'title' => 'Your message 2',
  ),1);
  $form->add_field('submit2', array(
    'type' => 'submit',
  ),1);

  return $form;
}

//############################################################################//
//############################################################################//
//############################################################################//


function multistepform(FAPI\form $form, &$form_state){
/*  $form = new FAPI\form(array(
    'form_id' => __FUNCTION__,
    'action' => 'multistep.php',
  ));*/

  $form->set_action('multistep.php');

  // add to step 0
  $form
  ->add_field('login_info',array(
    'type'=>'fieldset'
  ),0)
  ->add_field('username',array(
    'title' => 'Username',
    'type'=>'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
  ))
  ->add_field('password',array(
    'title' => 'Password',
    'type'=>'password',
    'validate' => array('required'),
    'preprocess' => array('trim'),
  ))
  ->add_field('image',array(
    'title' => 'Picture',
    'type'=>'file',
    'destination' => dirname(__FILE__),
  ))
  // ->add_field('recaptcha',array(
  //   'title' => 'Recaptcha',
  //   'type'=>'recaptcha',
  //   'publickey' => RECAPTCHA_PUBLIC_KEY,
  //   'privatekey' => RECAPTCHA_PRIVATE_KEY,
  // ))
  ->add_field('submit',array(
    'type'=>'submit',
    'value' => 'Continue',
  ));

  // add to step 1
  $form
  ->add_field('personal_info',array(
    'type'=>'fieldset'
  ),1)
  ->add_field('name',array(
    'title' => 'Name',
    'type'=>'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
  ))
  ->add_field('surname',array(
    'title' => 'Surname',
    'type'=>'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
  ))
  ->add_field('birthday',array(
    'title' => 'Birthday',
    'type'=>'date',
  ))
  ->add_field('submit',array(
    'type'=>'submit',
    'value' => 'Save',
  ));

  return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//


function showallform(FAPI\form $form, &$form_state){
  $form = new FAPI\form(array(
    'form_id' => 'showall',
    //'inline_errors' => TRUE,
  //  'attributes'=>array('enctype'=>'multipart/form-data')
  ));

  $object = new stdClass;
  $object->val1='val1';

  $form->add_field('object',array(
    'type'=>'value',
    'value' => $object,
    'my_evil_option' => 'evil_value',
  ));

  // var_dump( isset($form->get_field('object')->my_evil_option) ); // evil option is not contained

  $form->add_field('fieldset', array(
    'type' => 'fieldset',
    'attributes'=>array(
      //'style' => 'width: 500px;padding: 10px 10px 10px 5px;',
    ),
    'collapsible' => true,
    'title' => 'my fieldset',
  ));

  $form->get_field('fieldset')->add_field('name', array(
    'type' => 'textfield',
    'validate' => array('multiple_by[3]','ReQuired'), // will be reordered and normalized
    'preprocess' => array('trim'),
    'title' => 'Your name',
    'tooltip' => TRUE,
    'attributes' => array(
      'style' => 'width: 100%',
     ),
  ));
  $form->get_field('fieldset')->add_field('email', array(
    'type' => 'email',
    'title' => 'Your email address',
    'attributes' => array(
      'style' => 'width: 100%',
      'placeholder' => 'yourmail@yourdomain',
     ),
  ));
  $form->get_field('fieldset')->add_field('password', array(
    'type' => 'password',
    // 'validate' => array('required'),
    'title' => 'Your Password',
    'attributes' => array(
      'style' => 'width: 100%',
     ),
    'with_confirm' => TRUE,
    'with_strength_check' => TRUE,
  ));
  $form->get_field('fieldset')->add_field('markup1', array(
    'type' => 'markup',
    'value' => 'Markup 1 before all',
    'weight' => -10,
  ));


  $form->add_field('fieldset2',array(
    'type' => 'fieldset',
    'attributes'=>array(
      // 'style' => 'width: 500px;padding: 10px 10px 10px 5px;',
    ),
    'collapsible' => true,
    'collapsed' => false,
    'title' => 'my fieldset 2',
  ))
  ->add_field('message', array(
    'type' => 'textarea',
    'postprocess' => array('xss'),
    'title' => 'Your message',
    'rows' => 10,
    'resizable' => TRUE,
    'attributes' => array(
      'style' => 'width: 100%;height: 200px;',
      'placeholder' => 'Type your message',
      'style' => 'width: 100%',
     ),
  ))
  ->add_field('message2', array(
    'type' => 'tinymce',
    'title' => 'Your beautiful message',
    'rows' => 10,
  ))
  ->add_field('masked',array(
    'title' => 'Phone',
    'type' => 'maskedfield',
    'mask'=>'0000-0000',
  ));


  $accordion = new FAPI\Containers\accordion(array(
    'collapsible' => TRUE,
    'attributes'=>array(
    // 'style' => 'width: 500px',
  )),'accordion');

  $accordion->add_accordion('accordion1');
  $accordion->add_accordion('accordion2');

  $accordion->add_field('spinner', array(
    'type' => 'spinner',
    'title' => 'Select a value',
  ),0)
  ->add_field('range', array(
    'type' => 'range',
    'title' => 'Range a value',
  ),0)
  ->add_field('number', array(
    'type' => 'number',
    'title' => 'Number field',
  ),0)
  ->add_field('color',array(
    'type'=>'color',
    'title' => 'Color',
    'default_value' => '#be2a99',
  ))
  ->add_field('colorpicker',array(
    'type' => 'colorpicker',
    'title' => 'Pick your color',
    'default_value' => '#88B2D1',
  ));

  $accordion->add_field('date', array(
    'type' => 'date',
    'title' => 'select date',
    'granularity' => 'day',
    'js_selects' => FALSE,
  ),1);

  $accordion->add_field('time', array(
    'type' => 'time',
    'title' => 'time',
    'granularity' => 'minutes',
    'default_value' => array('hours'=>10,'minutes'=>23),
    'js_selects' => FALSE,
  ),1);

  $accordion->add_field('datepicker', array(
    'type' => 'datepicker',
    'title' => 'date picker',
    'weight' => -10,
  ),1);

  $accordion->add_field('datetime', array(
    'type' => 'datetime',
    'title' => 'date time',
    'js_selects' => TRUE,
  ),1);


  $form->add_field($accordion->get_name(), $accordion);


  $form->add_field('tabs',array(
    'type' => 'tabs',
    'attributes'=>array(
      // 'style' => 'width: 500px',
    ),
  ))
  ->add_tab('tab1') //index 0
  ->add_tab('tab2') //index 1
  ->add_field('markup2',array(
    'type' => 'markup',
    'value' => 'markup bbb',
  ),0) //to tab 0
  ->add_field('markup3',array(
    'type' => 'markup',
    'value' => 'markup ccc',
  ),1) //to tab 1
  ->add_field('checkboxes', array(
    'type' => 'checkboxes',
    'options' => array(0=>'zero',1=>'one',2=>'two'),
    'default_value' => 1,
  )) //to tab 0
  ->add_field('reqtextfield', array(
    'title' => 'Required Textfield',
    'type' => 'textfield',
    'default_value' => '',
    'validate' => array('required'),
  )) //to tab 0
  ->add_field('file', array(
    'type' => 'file',
    'destination' => dirname(__FILE__),
    // 'validate' => array('required'),
  ),1) //to tab 1
  ->add_field('select', array(
    'type' => 'select',
    'title' => 'select a number - select',
    'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
    'attributes' => array(
      'placeholder' => 'select placeholder',
    ),
    'validate' => array('required'),
  ),1) //to tab 1
  ->add_field('selectmenu', array(
    'type' => 'selectmenu',
    'title' => 'select a number - selectmenu',
    'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
    'default_value' => '2',
  ),1) //to tab 1
  ->add_field('slider', array(
    'type' => 'slider',
    'title' => 'select a number - slider',
    'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
    'default_value' => '2',
    'with_val' => TRUE,
  ),1); //to tab 1


  $form->add_field('hidden1', array(
    'type' => 'hidden',
    'default_value' => 'aaaa',
  ));


  // $sortable = $form->add_field('sortable',array(
  //   'type' => 'sortable',
  // ));

  // for($i=0;$i<5;$i++){
  //   $field = array(
  //     'title' => 'Textfield '.($i+1),
  //     'type' => 'textfield',
  //   );
  //   $sortable->add_field('sortable_field_'.$i,$field);
  // }

  $sortable_table = $form->add_field('sortable',array(
    'type' => 'sortable_table',
    'table_header' => array(
      'Textfields',
    ),
  ));
  for($i=0;$i<5;$i++){
    $field = array(
      'title' => 'Textfield '.($i+1),
      'type' => 'textfield',
      'default_value' => 'value '.($i+1),
    );
    $sortable_table->add_field('sortable_field_'.$i,$field,$i);
  }

  $nestable = $form->add_field('container', array(
    'type' => 'tag_container',
    'weight' => 1000,
  ))->add_field('nestable', array(
    'type' => 'nestable',
    'prefix' => '<br /><br />',
    'suffix' => '<br /><br />',
  ));

  for($i = 0; $i < 5; $i++){
    $nestable->add_field('nested_val_'.$i,array(
      'type' => 'textfield',
      'default_value' => 'nested '.$i,
    ))->add_child()->add_field('nested_child_val_'.$i,array(
      'type' => 'textfield',
      'default_value' => 'nestedchild '.$i,
    ));
  }
  //echo '<pre>';var_dump($nestable);echo '</pre>';

  $form->add_field('progressbar', array(
    'title' => 'Progress',
    'type' => 'progressbar',
    'default_value' => '42',
    'show_label' => TRUE,
  ));

  $elemslist = array(
    'ActionScript',
    'AppleScript',
    'Asp',
    'BASIC',
    'C',
    'C++',
    'Clojure',
    'COBOL',
    'ColdFusion',
    'Erlang',
    'Fortran',
    'Groovy',
    'Haskell',
    'Java',
    'JavaScript',
    'Lisp',
    'Perl',
    'PHP',
    'Python',
    'Ruby',
    'Scala',
    'Scheme'
  );

  $form->add_field('autocomplete', array(
    'type' => 'autocomplete',
    'title' => 'autocomplete',
    'options' => $elemslist,
  ))
  ->add_field('datalist', array(
    'type' => 'datalist',
    'title' => 'datalist',
    'options' => $elemslist,
  ))
  ->add_field('multiselect',array(
    'type' => 'multiselect',
    'title' => 'multiselect',
    'size' => 8,
    'options' => $elemslist,
    'default_value' => array(4,5,7),
  ))
  ->get_field('container')
  ->add_field('checkbox', array(
    'type' => 'checkbox',
    'default_value' => 'checkbox',
    'title' => 'Check me',
    'validate' => array( array('validator'=>'required','error_message'=>'You must check the <strong>%t</strong> checkbox!' ) ),
  ))
  ->add_field('actions', array(
    'type' => 'tag_container',
    'tag' => 'div',
  ))
  ->add_field('submit', array(
    'type' => 'submit',
    'value' => 'Send',
  ))
  ->add_field('submit2', array(
    'type' => 'submit',
    'value' => 'Send2',
    'js_button' => TRUE,
  ))
  ->add_field('button', array(
    'type' => 'button',
    'value' => 'Send3',
  ))
  ->add_field('image', array(
    'type' => 'image_button',
    'src' => 'https://www.google.it/images/srpr/logo11w.png',
    'attributes' => array(
      'width' => '100',
    ),
    'js_button' => TRUE,
  ))
  ->add_field('reset', array(
    'type' => 'reset',
    'value' => 'Reset',
    'js_button' => TRUE,
  ));

  return $form;
}


//############################################################################//
//############################################################################//
//############################################################################//

function nestableform(FAPI\form $form, &$form_state){
  $nestable = $form
  ->add_field('nestable', array(
    'type' => 'nestable',
    'maxDepth' => 100,
    'prefix' => '<br /><br />',
    'suffix' => '<br /><br />',
  ))->add_field('nested_val_0',array(
    'type' => 'textfield',
    'default_value' => 'nested 0',
  ));

  $nestable2 = $form
  ->add_field('nestable2', array(
    'type' => 'nestable',
    'maxDepth' => 100,
    'prefix' => '<br /><br />',
    'suffix' => '<br /><br />',
  ))->add_field('nested2_val_0',array(
    'type' => 'textfield',
    'default_value' => 'nested2 0',
  ));

  for($i = 1; $i <= 5; $i++){
    $nestable->add_child()->add_field('nested_val_'.$i,array(
      'type' => 'value',
      'default_value' => 'nested '.$i,
      'prefix' => 'nested '.$i,
    ))->add_child()->add_field('nested_child_val_'.$i,array(
      'type' => 'value',
      'default_value' => 'nestedchild '.$i,
      'prefix' => 'nestedchild '.$i,
      'suffix' => '<a href="#" style="float:right;" onClick="javascript:{alert(\'ciao\'); return false;}">ciao</a>',
    ));

    $nestable2->add_child()->add_field('nested2_val_'.$i,array(
      'type' => 'value',
      'default_value' => 'nested2 '.$i,
      'prefix' => 'nested2 '.$i,
    ))->add_child()->add_field('nested2_child_val_'.$i,array(
      'type' => 'value',
      'default_value' => 'nestedchild2 '.$i,
      'prefix' => 'nestedchild2 '.$i,
      'suffix' => '<a href="#" style="float:right;" onClick="javascript:{alert(\'ciao\'); return false;}">ciao</a>',
    ));

  }

  $form->add_field('submit', array(
    'type' => 'submit',
    'value' => 'Send',
  ));
  return $form;
}

//############################################################################//
//############################################################################//
//############################################################################//


function pluploadform(FAPI\form $form, &$form_state){
  // $form = new FAPI\form(array('form_id' => 'plupload'));
  $form->add_field('files_upload', array(
    'type' => 'plupload',
    'title' => 'Upload Extra Files',
    'filters' => array(
      'max_file_size' => '10mb',
      'mime_types' => array(
        array('title' => "Image files", 'extensions' => "jpg,jpeg,gif,png"),
        array('title' => "PDF files", 'extensions' => "pdf"),
        // array('title' => "Zip files", 'extensions' => "zip"),
      ),
    ),
    'url' => 'file_plupload.php',
    'swf_url' => 'http://www.plupload.com//plupload/js/Moxie.swf',
    'xap_url' => 'http://www.plupload.com//plupload/js/Moxie.xap',
  ));


  $form->add_field('submit', array(
    'type' => 'submit',
  ));

  return $form;
}


//############################################################################//
//############################################################################//
//############################################################################//


function datesform(FAPI\form $form, &$form_state){
  //$form = new FAPI\form(array('form_id' => 'dates'));

  $form->add_field('date', array(
    'type' => 'date',
    'title' => 'select date',
    'granularity' => 'day',
    'js_selects' => FALSE,
  ));

  $form->add_field('time', array(
    'type' => 'time',
    'title' => 'time',
    'granularity' => 'minutes',
    'default_value' => array('hours'=>10,'minutes'=>23),
    'js_selects' => FALSE,
  ));

  $form->add_field('datepicker', array(
    'type' => 'datepicker',
    'title' => 'date picker',
  ));

  $form->add_field('datetime', array(
    'type' => 'datetime',
    'title' => 'date time',
  ));

  $form->add_field('submit', array(
    'type' => 'submit',
  ));

  return $form;
}


//############################################################################//
//############################################################################//
//############################################################################//

function eventsform(FAPI\form $form, &$form_state){
  // $form = new FAPI\form(array('form_id' => 'events'));

  $step = 0;

  $form->set_action($_SERVER['PHP_SELF']);

  $fieldset = $form->add_field('textfields', array(
    'type' => 'fieldset',
    'id' => 'fieldset-textfields',
    'title' => 'textfields',
  ));

  $num_textfields = isset($form_state['input_form_definition']['fields'][$step]['textfields']['fields']) ? (count($form_state['input_form_definition']['fields'][$step]['textfields']['fields']) + 1) : 1;
   /*$fieldset->add_field('num_textfields', array(
    'type' => 'textfield',
    'default_value' => $num_textfields,
    'size' => 3,
    'attributes' => array(  'style' => 'width: auto;' ),
  ));*/

  for($i = 0 ; $i < $num_textfields; $i++ ){
    // $suffix = new stdClass();
    // $suffix->oldnum = isset($form_state['input_form_definition']['fields'][$step]['textfields']['fields']) ? count($form_state['input_form_definition']['fields'][$step]['textfields']['fields']) : NULL;
    // $suffix->i = $i;
    // $suffix->num_textfields = $num_textfields;
    // $suffix->form_state = (!empty($form_state['input_form_definition']['fields'][$step]['textfields']['fields'])) ? array_keys($form_state['input_form_definition']['fields'][$step]['textfields']['fields']) : NULL;

    $fieldset->add_field('text_'.$i, array(
      'type' => 'textfield',
      'title' => 'text',
      'ajax_url' => $_SERVER['PHP_SELF'],
      'event' => array(
        array(
          'event' => 'focus',
          'callback' => 'events_form_callback',
          'target' => 'fieldset-textfields',
          'effect' => 'fade',
          'method' => 'replace',
        ),
      ),
      // 'suffix' => '<pre>'.var_export($suffix, TRUE).'</pre>',
    ));
  }

  if( FAPI\form::is_partial() ){
    $jsondata = json_decode($form_state['input_values']['jsondata']);
    $callback = $jsondata->callback;
    if( is_callable($callback) ){
      //$target_elem = $callback( $form )->get_field('num_textfields');
      //$fieldset->add_js('console.log(JSON.parse(\''.json_encode( array( 'build_options' => preg_replace("/\\\"|\"|\n/","",serialize($target_elem->get_build_options())),  'id' => $target_elem->get_html_id(), 'value' => $target_elem->get_value()) ).'\'))');
      $fieldset->add_js("\$('input[name=\"{$jsondata->name}\"]').focus();");
    }
    //$fieldset->add_js('alert($("#num_textfields").val())');
    //$fieldset->add_js('console.log($("#num_textfields").val())');
  }

  $form->add_field('submit', array(
    'type' => 'submit',
  ));
//var_dump($form->toArray());
  return $form;
}

function events_form_callback(FAPI\form $form){
  return $form->get_field('textfields');
}



//############################################################################//
//############################################################################//
//############################################################################//

function batchoperationsform(FAPI\form $form, &$form_state){
  $step = 0;
  $form->set_action($_SERVER['PHP_SELF']);

  $form->add_field('progressnum', array(
    'type' => 'value',
    'value' => (isset( $form_state['input_form_definition']['fields'][$step]['progressnum']['value'] ) )? $form_state['input_form_definition']['fields'][$step]['progressnum']['value'] + 20 : 0,
  ));

  $fieldset = $form->add_field('fieldset', array(
    'type' => 'tag_container',
  ));

  if( FAPI\form::is_partial() ){
    $jsondata = json_decode($form_state['input_values']['jsondata']);
    $callback = $jsondata->callback;
    if( isset($form_state['input_form_definition']['fields'][$step]['progressnum']['value']) && $form_state['input_form_definition']['fields'][$step]['progressnum']['value'] >= 100 ){
      $fieldset->add_field('done', array(
        'type' => 'markup',
        'default_value' => 'finito!',
      ));
    }else{

      if( is_callable($callback) ){
        $fieldset->add_js("setTimeout(function(){ \$('#progress','#{$form->get_id()}').trigger('click') },1000);");
      }

      $fieldset->add_field('progress', array(
        'type' => 'progressbar',
          'default_value' =>  $form->get_field('progressnum')->get_value(),
          'show_label' => TRUE,
          'ajax_url' => $_SERVER['PHP_SELF'],
          'event' => array(
            array(
              'event' => 'click',
              'callback' => 'batch_operations_form_callback',
              'target' => 'batchoperationsform',
              'effect' => '',
              'method' => 'replace',
            ),
          ),
      ));

    }

  }

  // must be outside of the fieldset in order to be processed
  $form->add_field('file', array(
    'type' => 'file',
      'ajax_url' => $_SERVER['PHP_SELF'],
      'destination' => dirname(__FILE__),
      'event' => array(
        array(
          'event' => 'change',
          'callback' => 'batch_operations_form_callback',
          'target' => 'batchoperationsform',
          'effect' => 'fade',
          'method' => 'replace',
        ),
      ),
  ));

/*  $fieldset->add_field('submit', array(
    'type' => 'submit',
  ));
*/
  return $form;
}

function batch_operations_form_callback(FAPI\form $form){
  return $form->get_field('fieldset');
}


function _batch_get_progress($filename, $offset = 0, $limit = 20){

}




//############################################################################//
//############################################################################//
//############################################################################//

function locationsform(FAPI\form $form, &$form_state){
/*
    google.maps.MapTypeId.HYBRID
    google.maps.MapTypeId.ROADMAP
    google.maps.MapTypeId.SATELLITE
    google.maps.MapTypeId.TERRAIN
*/

  $form->add_field('location', array(
    'title' => 'GeoLocation',
    'type' => 'geolocation',
  ))
  ->add_field('hr1', array('type'=>'markup','value'=>'<hr />'))
  ->add_field('map', array(
    'title' => 'MapLocation',
    'type' => 'gmaplocation',
    'scrollwheel' => TRUE,
    'zoom' => 15,
    'mapheight' => '400px',
    'default_value' => array(
      'latitude' => 45.434332,
      'longitude' => 12.338440,
    ),
    'maptype' => 'google.maps.MapTypeId.TERRAIN',
    'with_current_location' => TRUE,
  ))
  ->add_field('hr2', array('type'=>'markup','value'=>'<hr />'))
  ->add_field('decode', array(
    'title' => 'GeoDecode',
    'type' => 'gmaplocation',
    'with_geocode' => TRUE,
    'with_reverse' => TRUE,
    'lat_lon_type' => 'textfield',
    'zoom' => 15,
    'default_value' => array(
      'latitude' => 51.48257659999999,
      'longitude' => -0.0076589,
    ),
  ))
  ->add_field('hr3', array('type'=>'markup','value'=>'<hr />'))
  ->add_field('decode_nomap', array(
    'title' => 'GeoDecode No Map',
    'type' => 'gmaplocation',
    'with_geocode' => TRUE,
    'with_map' => FALSE,
    'with_reverse' => TRUE,
    'with_current_location' => TRUE,
    'lat_lon_type' => 'textfield',
    'default_value' => array(
      'latitude' => 51.48257659999999,
      'longitude' => -0.0076589,
    ),
  ))
  ->add_field('submit', array(
    'prefix' => '<br /><br />',
    'type' => 'submit',
  ));

  return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//


function repeatableform(FAPI\form $form, &$form_state){
  $form->set_inline_errors(TRUE); //->set_on_dialog(TRUE);

  $form
    ->add_field('rep', array(
      'type' => 'repeatable',
      'title' => 'Emails',
    ))
    ->add_field('name', array(
      'type' => 'textfield',
      'validate' => array('required'),
      'preprocess' => array('trim'),
      'title' => 'Your name',
    ))
    ->add_field('email', array(
      'type' => 'textfield',
      'validate' => array('required', 'email'),
      'title' => 'Your email address',
    ))
  ;


    $form
      ->add_field('hr1', array('type'=>'markup','value'=>'<hr />'))
      ->add_field('submit', array(
        'type' => 'submit',
      ));

  return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//


function bulkform(FAPI\form $form, &$form_state){
  $bulk = $form->add_field('bulk', array(
    'type' => 'bulk_table',
  ));
  $bulk->set_table_header(array(
    'text',
    'number'
  ));
  $bulk->add_operation('dump', 'dump', 'var_dump');
  $bulk->add_operation('print', 'print', 'print');

  for( $i = 0; $i < 4; $i++){
    $bulk->add_row()->add_field('text_'.$i, array(
      'type' => 'textfield',
      'default_value' => 'textfield_'.$i,
    ), $i)
    ->add_field('number_'.$i, array(
      'type' => 'number',
      'default_value' => ''.$i,
    ), $i);
  }

  $form->add_field('submit', array(
    'type' => 'submit',
  ));

  return $form;
}
