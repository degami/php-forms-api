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
function contactform(FAPI\Form $form, &$form_state)
{
 // $form = new FAPI\form(array(
  //   'form_id' => 'contact',
  // ));
  //
    $form->setInlineErrors(true); //->set_on_dialog(TRUE);

    $form
    ->addField('fieldset', array(
    'type' => 'tabs',
    'title' => 'Contact',
    ))->addTab('Contact')
    ->addField('name', array(
    'type' => 'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    'title' => 'Your name',
    ))
    ->addField('email', array(
    'type' => 'textfield',
    'validate' => array('required', 'email'),
    'title' => 'Your email address',
    ))
    ->addField('message', array(
    'type' => 'tinymce',
    'postprocess' => array('xss'),
    'title' => 'Your message',
    ))
    ->addField('switcher', array(
    'type' => 'switchbox',
    'title' => 'Yes or No',
    'default_value' => 1,
//    'default_value' => 'a',
//    'yes_value' => 'a', 'yes_label' => 'A value',
//    'no_value' => 'b', 'no_label' => 'B value',
    ))
    ->addField('captcha', array(
    'type' => 'math_captcha',
    'title' => 'Check this out!',
    'pre_filled' => true,
    ))
    ->addField('submit', array(
    'type' => 'submit',
    ));

    return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//



// Generate a simple contact form

function contactform_ajax(FAPI\Form $form, &$form_state)
{
  // $form = new FAPI\form(array(
  //   'form_id' => __FUNCTION__,
  //   'ajax_submit_url' => 'ajax_url.php',
  //   'output_type' => 'json',
  // ));

    $form
    ->setFormId(__FUNCTION__)
    ->setAjaxSubmitUrl('ajax_url.php')
    ->setOutputType('json');

    $form->addField('name', array(
    'type' => 'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    'title' => 'Your name',
    ));
    $form->addField('email', array(
    'type' => 'textfield',
    'validate' => array('required', 'email'),
    'title' => 'Your email address',
    ));
    $form->addField('message', array(
    'type' => 'textarea',
    'postprocess' => array('xss'),
    'title' => 'Your message',
    ));
    $form->addField('submit', array(
    'type' => 'submit',
    ));
    $form->addField('message2', array(
    'type' => 'textarea',
    'postprocess' => array('xss'),
    'title' => 'Your message 2',
    ), 1);
    $form->addField('submit2', array(
    'type' => 'submit',
    ), 1);

    return $form;
}

//############################################################################//
//############################################################################//
//############################################################################//


function multistepform(FAPI\Form $form, &$form_state)
{
/*  $form = new FAPI\form(array(
    'form_id' => __FUNCTION__,
    'action' => 'multistep.php',
  ));*/

    $form->setAction('multistep.php');

  // add to step 0
    $form
    ->addField('login_info', array(
    'type'=>'fieldset'
    ), 0)
    ->addField('username', array(
    'title' => 'Username',
    'type'=>'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    ))
    ->addField('password', array(
    'title' => 'Password',
    'type'=>'password',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    ))
    ->addField('image', array(
    'title' => 'Picture',
    'type'=>'file',
    'destination' => dirname(__FILE__),
    ))
  // ->addField('recaptcha',array(
  //   'title' => 'Recaptcha',
  //   'type'=>'recaptcha',
  //   'publickey' => RECAPTCHA_PUBLIC_KEY,
  //   'privatekey' => RECAPTCHA_PRIVATE_KEY,
  // ))
    ->addField('submit', array(
    'type'=>'submit',
    'value' => 'Continue',
    ));

  // add to step 1
    $form
    ->addField('personal_info', array(
    'type'=>'fieldset'
    ), 1)
    ->addField('name', array(
    'title' => 'Name',
    'type'=>'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    ))
    ->addField('surname', array(
    'title' => 'Surname',
    'type'=>'textfield',
    'validate' => array('required'),
    'preprocess' => array('trim'),
    ))
    ->addField('birthday', array(
    'title' => 'Birthday',
    'type'=>'date',
    ))
    ->addField('submit', array(
    'type'=>'submit',
    'value' => 'Save',
    ));

    return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//


function showallform(FAPI\Form $form, &$form_state)
{
    $form = new FAPI\Form(array(
    'form_id' => 'showall',
    //'inline_errors' => TRUE,
  //  'attributes'=>array('enctype'=>'multipart/form-data')
    ));

    $object = new stdClass;
    $object->val1='val1';

    $form->addField('object', array(
    'type'=>'value',
    'value' => $object,
    'my_evil_option' => 'evil_value',
    ));

  // var_dump( isset($form->get_field('object')->my_evil_option) ); // evil option is not contained

    $form->addField('fieldset', array(
    'type' => 'fieldset',
    'attributes'=>array(
      //'style' => 'width: 500px;padding: 10px 10px 10px 5px;',
    ),
    'collapsible' => true,
    'title' => 'my fieldset',
    ));

    $form->getField('fieldset')->addField('name', array(
    'type' => 'textfield',
    'validate' => array('multiple_by[3]','ReQuired'), // will be reordered and normalized
    'preprocess' => array('trim'),
    'title' => 'Your name',
    'tooltip' => true,
    'attributes' => array(
      'style' => 'width: 100%',
     ),
    ));
    $form->getField('fieldset')->addField('email', array(
    'type' => 'email',
    'title' => 'Your email address',
    'attributes' => array(
      'style' => 'width: 100%',
      'placeholder' => 'yourmail@yourdomain',
     ),
    ));
    $form->getField('fieldset')->addField('password', array(
    'type' => 'password',
    // 'validate' => array('required'),
    'title' => 'Your Password',
    'attributes' => array(
      'style' => 'width: 100%',
     ),
    'with_confirm' => true,
    'with_strength_check' => true,
    ));
    $form->getField('fieldset')->addField('markup1', array(
    'type' => 'markup',
    'value' => 'Markup 1 before all',
    'weight' => -10,
    ));


    $form->addField('fieldset2', array(
    'type' => 'fieldset',
    'attributes'=>array(
      // 'style' => 'width: 500px;padding: 10px 10px 10px 5px;',
    ),
    'collapsible' => true,
    'collapsed' => false,
    'title' => 'my fieldset 2',
    ))
    ->addField('message', array(
    'type' => 'textarea',
    'postprocess' => array('xss'),
    'title' => 'Your message',
    'rows' => 10,
    'resizable' => true,
    'attributes' => array(
      'style' => 'width: 100%;height: 200px;',
      'placeholder' => 'Type your message',
      'style' => 'width: 100%',
     ),
    ))
    ->addField('message2', array(
    'type' => 'tinymce',
    'title' => 'Your beautiful message',
    'rows' => 10,
    ))
    ->addField('masked', array(
    'title' => 'Phone',
    'type' => 'maskedfield',
    'mask'=>'0000-0000',
    ));

    $accordion = new FAPI\Containers\Accordion(array(
    'collapsible' => true,
    'attributes'=>array(
    // 'style' => 'width: 500px',
    )), 'accordion');

    $accordion->addAccordion('accordion1');
    $accordion->addAccordion('accordion2');

    $accordion->addField('spinner', array(
    'type' => 'spinner',
    'title' => 'Select a value',
    ), 0)
    ->addField('range', array(
    'type' => 'range',
    'title' => 'Range a value',
    ), 0)
    ->addField('number', array(
    'type' => 'number',
    'title' => 'Number field',
    ), 0)
    ->addField('color', array(
    'type'=>'color',
    'title' => 'Color',
    'default_value' => '#be2a99',
    ))
    ->addField('colorpicker', array(
    'type' => 'colorpicker',
    'title' => 'Pick your color',
    'default_value' => '#88B2D1',
    ));

    $accordion->addField('date', array(
    'type' => 'date',
    'title' => 'select date',
    'granularity' => 'day',
    'js_selects' => false,
    ), 1);

    $accordion->addField('time', array(
    'type' => 'time',
    'title' => 'time',
    'granularity' => 'minutes',
    'default_value' => array('hours'=>10,'minutes'=>23),
    'js_selects' => false,
    ), 1);

    $accordion->addField('datepicker', array(
    'type' => 'datepicker',
    'title' => 'date picker',
    'weight' => -10,
    ), 1);

    $accordion->addField('datetime', array(
    'type' => 'datetime',
    'title' => 'date time',
    'js_selects' => true,
    ), 1);


    $form->addField($accordion->getName(), $accordion);


    $form->addField('tabs', array(
    'type' => 'tabs',
    'attributes'=>array(
      // 'style' => 'width: 500px',
    ),
    ))
    ->addTab('tab1') //index 0
    ->addTab('tab2') //index 1
    ->addTab('tab3') //index 2
    ->addField('markup2', array(
    'type' => 'markup',
    'value' => 'markup bbb',
    ), 0) //to tab 0
    ->addField('markup3', array(
    'type' => 'markup',
    'value' => 'markup ccc',
    ), 1) //to tab 1
    ->addField('checkboxes', array(
    'type' => 'checkboxes',
    'options' => array(0=>'zero',1=>'one',2=>'two'),
    'default_value' => 1,
    )) //to tab 0
    ->addField('radios', array(
    'type' => 'radios',
    'options' => array(0=>'zero',1=>'one',2=>'two'),
    'default_value' => 2,
    ), 2) //to tab 2
    ->addField('reqtextfield', array(
    'title' => 'Required Textfield',
    'type' => 'textfield',
    'default_value' => '',
    'validate' => array('required'),
    )) //to tab 0
    ->addField('file', array(
    'type' => 'file',
    'destination' => dirname(__FILE__),
    // 'validate' => array('required'),
    ), 1) //to tab 1
    ->addField('select', array(
    'type' => 'select',
    'title' => 'select a number - select',
    'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
    'attributes' => array(
      'placeholder' => 'select placeholder',
    ),
    'validate' => array('required'),
    ), 1) //to tab 1
    ->addField('selectmenu', array(
    'type' => 'selectmenu',
    'title' => 'select a number - selectmenu',
    'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
    'default_value' => '2',
    ), 1) //to tab 1
    ->addField('slider', array(
    'type' => 'slider',
    'title' => 'select a number - slider',
    'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
    'default_value' => '2',
    'with_val' => true,
    ), 1); //to tab 1


    $form->addField('hidden1', array(
    'type' => 'hidden',
    'default_value' => 'aaaa',
    ));


    $sortable = $form->addField('sortable', array(
    'type' => 'sortable',
    ));

    for ($i=0; $i<5; $i++) {
        $field = array(
        'title' => 'Textfield '.($i+1),
        'type' => 'textfield',
        );
        $sortable->addField('sortable_field_'.$i, $field);
    }

    $sortable_table = $form->addField('sortable_table', array(
    'type' => 'sortable_table',
    'table_header' => array(
      'Textfields',
    ),
    ));
    for ($i=0; $i<5; $i++) {
        $field = array(
        'title' => 'Textfield '.($i+1),
        'type' => 'textfield',
        'default_value' => 'value '.($i+1),
        );
        $sortable_table->addField('sortable_field_'.$i, $field, $i);
    }

    $nestable = $form->addField('container', array(
    'type' => 'tag_container',
    'weight' => 1000,
    ))->addField('nestable', array(
    'type' => 'nestable',
    'prefix' => '<br /><br />',
    'suffix' => '<br /><br />',
    ));

    for ($i = 0; $i < 5; $i++) {
        $nestable->addField('nested_val_'.$i, array(
        'type' => 'textfield',
        'default_value' => 'nested '.$i,
        ))->addChild()->addField('nested_child_val_'.$i, array(
        'type' => 'textfield',
        'default_value' => 'nestedchild '.$i,
        ));
    }
  //echo '<pre>';var_dump($nestable);echo '</pre>';

    $form->addField('progressbar', array(
    'title' => 'Progress',
    'type' => 'progressbar',
    'default_value' => '42',
    'show_label' => true,
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

    $form->addField('autocomplete', array(
    'type' => 'autocomplete',
    'title' => 'autocomplete',
    'options' => $elemslist,
    ))
    ->addField('datalist', array(
    'type' => 'datalist',
    'title' => 'datalist',
    'options' => $elemslist,
    ))
    ->addField('multiselect', array(
    'type' => 'multiselect',
    'title' => 'multiselect',
    'size' => 8,
    'options' => $elemslist,
    'default_value' => array(4,5,7),
    ))
    ->getField('container')
    ->addField('checkbox', array(
    'type' => 'checkbox',
    'default_value' => 'checkbox',
    'title' => 'Check me',
    'validate' => array( array('validator'=>'required','error_message'=>'You must check the <strong>%t</strong> checkbox!' ) ),
    ))
    ->addField('actions', array(
    'type' => 'tag_container',
    'tag' => 'div',
    ))
    ->addField('submit', array(
    'type' => 'submit',
    'value' => 'Send',
    ))
    ->addField('submit2', array(
    'type' => 'submit',
    'value' => 'Send2',
    'js_button' => true,
    ))
    ->addField('button', array(
    'type' => 'button',
    'value' => 'Send3',
    ))
    ->addField('image', array(
    'type' => 'image_button',
    'src' => 'https://www.google.it/images/srpr/logo11w.png',
    'attributes' => array(
      'width' => '100',
    ),
    'js_button' => true,
    ))
    ->addField('reset', array(
    'type' => 'reset',
    'value' => 'Reset',
    'js_button' => true,
    ));

    return $form;
}


//############################################################################//
//############################################################################//
//############################################################################//

function nestableform(FAPI\Form $form, &$form_state)
{
    $nestable = $form
    ->addField('nestable', array(
    'type' => 'nestable',
    'maxDepth' => 100,
    'prefix' => '<br /><br />',
    'suffix' => '<br /><br />',
    ))->addField('nested_val_0', array(
    'type' => 'textfield',
    'default_value' => 'nested 0',
    ));

    $nestable2 = $form
    ->addField('nestable2', array(
    'type' => 'nestable',
    'maxDepth' => 100,
    'prefix' => '<br /><br />',
    'suffix' => '<br /><br />',
    ))->addField('nested2_val_0', array(
    'type' => 'textfield',
    'default_value' => 'nested2 0',
    ));

    for ($i = 1; $i <= 5; $i++) {
        $nestable->addChild()->addField('nested_val_'.$i, array(
        'type' => 'value',
        'default_value' => 'nested '.$i,
        'prefix' => 'nested '.$i,
        ))->addChild()->addField('nested_child_val_'.$i, array(
        'type' => 'value',
        'default_value' => 'nestedchild '.$i,
        'prefix' => 'nestedchild '.$i,
        'suffix' => '<a href="#" style="float:right;" onClick="javascript:{alert(\'ciao\'); return false;}">ciao</a>',
        ));

        $nestable2->addChild()->addField('nested2_val_'.$i, array(
        'type' => 'value',
        'default_value' => 'nested2 '.$i,
        'prefix' => 'nested2 '.$i,
        ))->addChild()->addField('nested2_child_val_'.$i, array(
        'type' => 'value',
        'default_value' => 'nestedchild2 '.$i,
        'prefix' => 'nestedchild2 '.$i,
        'suffix' => '<a href="#" style="float:right;" onClick="javascript:{alert(\'ciao\'); return false;}">ciao</a>',
        ));
    }

    $form->addField('submit', array(
    'type' => 'submit',
    'value' => 'Send',
    ));
    return $form;
}

//############################################################################//
//############################################################################//
//############################################################################//


function pluploadform(FAPI\Form $form, &$form_state)
{
  // $form = new FAPI\form(array('form_id' => 'plupload'));
    $form->addField('files_upload', array(
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


    $form->addField('submit', array(
    'type' => 'submit',
    ));

    return $form;
}


//############################################################################//
//############################################################################//
//############################################################################//


function datesform(FAPI\Form $form, &$form_state)
{
  //$form = new FAPI\form(array('form_id' => 'dates'));

    $fieldset = $form->addField('html', array(
    'type' => 'fieldset',
    'title' => 'as html fields',
    ));

    $fieldset->addField('date', array(
    'type' => 'date',
    'title' => 'select date',
    ));

    $fieldset->addField('time', array(
    'type' => 'time',
    'title' => 'time',
    'default_value' => '10:23',
    ));

    $fieldset->addField('datetime', array(
    'type' => 'datetime',
    'title' => 'date time',
    ));

    $fieldset->addField('datepicker', array(
    'type' => 'datepicker',
    'title' => 'date picker',
    ));

    $fieldset = $form->addField('selects', array(
    'type' => 'fieldset',
    'title' => 'as selects',
    ));

    $fieldset->addField('dateselect', array(
    'type' => 'dateselect',
    'title' => 'select date',
    'granularity' => 'day',
    'js_selects' => false,
    ));

    $fieldset->addField('timeselect', array(
    'type' => 'timeselect',
    'title' => 'time',
    'granularity' => 'minutes',
    'default_value' => array('hours'=>10,'minutes'=>23),
    'js_selects' => false,
    ));


    $fieldset->addField('datetimeselect', array(
    'type' => 'datetimeselect',
    'title' => 'date time',
    ));

    $form->addField('submit', array(
    'type' => 'submit',
    ));

    return $form;
}


//############################################################################//
//############################################################################//
//############################################################################//

function eventsform(FAPI\Form $form, &$form_state)
{
  // $form = new FAPI\form(array('form_id' => 'events'));

    $step = 0;

    $form->setAction($_SERVER['PHP_SELF']);

    $fieldset = $form->addField('textfields', array(
    'type' => 'fieldset',
    'id' => 'fieldset-textfields',
    'title' => 'textfields',
    ));

    $num_textfields = isset($form_state['input_form_definition']['fields'][$step]['textfields']['fields']['num_textfields']['value']) ? ($form_state['input_form_definition']['fields'][$step]['textfields']['fields']['num_textfields']['value'] + 1) : 1;

    $fieldset->addField('num_textfields', array(
    'type' => 'hidden',
    'default_value' => $num_textfields,
    ));

    for ($i = 0; $i < $num_textfields; $i++) {
        $fieldset->addField('text_'.$i, array(
        'type' => 'textfield',
        'title' => 'text',
        ));
    }

    if (FAPI\Form::isPartial()) {
        $jsondata = json_decode($form_state['input_values']['jsondata']);
        $callback = $jsondata->callback;
        if (is_callable($callback)) {
          //$target_elem = $callback( $form )->get_field('num_textfields');
          //$fieldset->add_js('console.log(JSON.parse(\''.json_encode( array( 'build_options' => preg_replace("/\\\"|\"|\n/","",serialize($target_elem->get_build_options())),  'id' => $target_elem->get_html_id(), 'value' => $target_elem->get_value()) ).'\'))');
            $fieldset->addJs("\$('input[name=\"{$jsondata->name}\"]').focus();");
        }
      //$fieldset->add_js('alert($("#num_textfields").val())');
      //$fieldset->add_js('console.log($("#num_textfields").val())');
    }

    $form->addField('addmore', array(
    'type' => 'submit',
    'value' => 'Add more',
    'ajax_url' => $_SERVER['PHP_SELF'],
    'event' => array(
      array(
        'event' => 'click',
        'callback' => 'events_form_callback',
        'target' => 'fieldset-textfields',
        'effect' => 'fade',
        'method' => 'replace',
      ),
    ),
    ));

    $form->addField('submit', array(
    'type' => 'submit',
    ));

//var_dump($form->toArray());
    return $form;
}

function events_form_callback(FAPI\Form $form)
{
    return $form->getField('textfields');
}



//############################################################################//
//############################################################################//
//############################################################################//

function batchoperationsform(FAPI\Form $form, &$form_state)
{
    $step = 0;
    $form->setAction($_SERVER['PHP_SELF']);

    $form->addField('progressnum', array(
    'type' => 'value',
    'value' => (isset($form_state['input_form_definition']['fields'][$step]['progressnum']['value']) )? $form_state['input_form_definition']['fields'][$step]['progressnum']['value'] + 20 : 0,
    ));

    $fieldset = $form->addField('fieldset', array(
    'type' => 'tag_container',
    ));

    if (FAPI\Form::isPartial()) {
        $jsondata = json_decode($form_state['input_values']['jsondata']);
        $callback = $jsondata->callback;
        if (isset($form_state['input_form_definition']['fields'][$step]['progressnum']['value']) && $form_state['input_form_definition']['fields'][$step]['progressnum']['value'] >= 100) {
            $fieldset->addField('done', array(
            'type' => 'markup',
            'default_value' => 'finito!',
            ));
        } else {
            if (is_callable($callback)) {
                $fieldset->addJs("setTimeout(function(){ \$('#progress','#{$form->getId()}').trigger('click') },1000);");
            }

            $fieldset->addField('progress', array(
            'type' => 'progressbar',
            'default_value' =>  $form->getField('progressnum')->get_value(),
            'show_label' => true,
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
    $form->addField('file', array(
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

/*  $fieldset->addField('submit', array(
    'type' => 'submit',
  ));
*/
    return $form;
}

function batch_operations_form_callback(FAPI\Form $form)
{
    return $form->getField('fieldset');
}


function _batch_get_progress($filename, $offset = 0, $limit = 20)
{
}




//############################################################################//
//############################################################################//
//############################################################################//

function locationsform(FAPI\Form $form, &$form_state)
{
/*
    google.maps.MapTypeId.HYBRID
    google.maps.MapTypeId.ROADMAP
    google.maps.MapTypeId.SATELLITE
    google.maps.MapTypeId.TERRAIN
*/

    $form->addField('location', array(
    'title' => 'GeoLocation',
    'type' => 'geolocation',
    ))
    ->addField('hr1', array('type'=>'markup','value'=>'<hr />'))
    ->addField('map', array(
    'title' => 'MapLocation',
    'type' => 'gmaplocation',
    'scrollwheel' => true,
    'zoom' => 15,
    'mapheight' => '400px',
    'default_value' => array(
      'latitude' => 45.434332,
      'longitude' => 12.338440,
    ),
    'maptype' => 'google.maps.MapTypeId.TERRAIN',
    'with_current_location' => true,
    ))
    ->addField('hr2', array('type'=>'markup','value'=>'<hr />'))
    ->addField('decode', array(
    'title' => 'GeoDecode',
    'type' => 'gmaplocation',
    'with_geocode' => true,
    'with_reverse' => true,
    'lat_lon_type' => 'textfield',
    'zoom' => 15,
    'default_value' => array(
      'latitude' => 51.48257659999999,
      'longitude' => -0.0076589,
    ),
    ))
    ->addField('hr3', array('type'=>'markup','value'=>'<hr />'))
    ->addField('decode_nomap', array(
    'title' => 'GeoDecode No Map',
    'type' => 'gmaplocation',
    'with_geocode' => true,
    'with_map' => false,
    'with_reverse' => true,
    'with_current_location' => true,
    'lat_lon_type' => 'textfield',
    'default_value' => array(
      'latitude' => 51.48257659999999,
      'longitude' => -0.0076589,
    ),
    ))
    ->addField('hr4', array('type'=>'markup','value'=>'<hr />'))
    ->addField('leafletmap', array(
    'title' => 'LeafletLocation',
    'type' => 'leafletlocation',
    'scrollwheel' => true,
    'zoom' => 15,
    'mapheight' => '400px',
    'default_value' => array(
      'latitude' => 45.434332,
      'longitude' => 12.338440,
    ),
    'maptype' => 'mapbox.light',
    'accessToken' => MAPBOX_API_KEY,
    'lat_lon_type' => 'textfield',
    ))
    ->addField('submit', array(
    'prefix' => '<br /><br />',
    'type' => 'submit',
    ));

    return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//


function repeatableform(FAPI\Form $form, &$form_state)
{
    $form->setInlineErrors(true); //->set_on_dialog(TRUE);

    $form
    ->addField('rep', array(
      'type' => 'repeatable',
      'title' => 'Emails',
    ))
    ->addField('name', array(
      'type' => 'textfield',
      'validate' => array('required'),
      'preprocess' => array('trim'),
      'title' => 'Your name',
    ))
    ->addField('email', array(
      'type' => 'textfield',
      'validate' => array('required', 'email'),
      'title' => 'Your email address',
    ))
    ;


    $form
      ->addField('hr1', array('type'=>'markup','value'=>'<hr />'))
      ->addField('submit', array(
        'type' => 'submit',
      ));

    return $form;
}



//############################################################################//
//############################################################################//
//############################################################################//


function bulkform(FAPI\Form $form, &$form_state)
{
    $bulk = $form->addField('bulk', array(
    'type' => 'bulk_table',
    ));
    $bulk->setTableHeader(array(
    'text',
    'number'
    ));
    $bulk->addOperation('dump', 'dump', 'var_dump');
    $bulk->addOperation('print', 'print', 'printf');

    for ($i = 0; $i < 4; $i++) {
        $bulk->addRow()->addField('text_'.$i, array(
        'type' => 'textfield',
        'default_value' => 'textfield_'.$i,
        ), $i)
        ->addField('number_'.$i, array(
        'type' => 'number',
        'default_value' => ''.$i,
        ), $i);
    }

    $form->addField('submit', array(
    'type' => 'submit',
    ));

    return $form;
}
