<?php
require_once '../src/form.php';

// Generate a simple contact form
$contactform = new cs_form(array(
  'form_id' => 'contact',
));
$contactform->add_field('name', array(
  'type' => 'textfield',
  'validate' => array('required'),
  'preprocess' => array('trim'),
  'title' => 'Your name',
));
$contactform->add_field('email', array(
  'type' => 'textfield',
  'validate' => array('required', 'email'),
  'title' => 'Your email address',
));
$contactform->add_field('message', array(
  'type' => 'textarea',
  'postprocess' => array('xss'),
  'title' => 'Your message',
));
$contactform->add_field('submit', array(
  'type' => 'submit',
));



//############################################################################//
//############################################################################//
//############################################################################//



// Generate a simple contact form
$contactform_ajax = new cs_form(array(
  'form_id' => 'contact',
  'ajax_submit_url' => 'ajax_url.php',
));
$contactform_ajax->add_field('name', array(
  'type' => 'textfield',
  'validate' => array('required'),
  'preprocess' => array('trim'),
  'title' => 'Your name',
));
$contactform_ajax->add_field('email', array(
  'type' => 'textfield',
  'validate' => array('required', 'email'),
  'title' => 'Your email address',
));
$contactform_ajax->add_field('message', array(
  'type' => 'textarea',
  'postprocess' => array('xss'),
  'title' => 'Your message',
));
$contactform_ajax->add_field('submit', array(
  'type' => 'submit',
));
$contactform_ajax->add_field('message2', array(
  'type' => 'textarea',
  'postprocess' => array('xss'),
  'title' => 'Your message 2',
),1);
$contactform_ajax->add_field('submit2', array(
  'type' => 'submit',
),1);


//############################################################################//
//############################################################################//
//############################################################################//



$multistepform = new cs_form(array(
  'form_id' => 'multistep',
  'action' => 'multistep.php',
));

// add to step 0
$multistepform
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
$multistepform
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




//############################################################################//
//############################################################################//
//############################################################################//



// Generate a simple contact form
$showallform = new cs_form(array(
  'form_id' => 'showall',
  'inline_errors' => TRUE,
//  'attributes'=>array('enctype'=>'multipart/form-data')
));

$object = new stdClass;
$object->val1='val1';

$showallform->add_field('object',array(
  'type'=>'value',
  'value' => $object,
  'my_evil_option' => 'evil_value',
));

// var_dump( isset($showallform->get_field('object')->my_evil_option) ); // evil option is not contained

$showallform->add_field('fieldset', array(
  'type' => 'fieldset',
  'attributes'=>array(
    //'style' => 'width: 500px;padding: 10px 10px 10px 5px;',
  ),
  'collapsible' => true,
  'title' => 'my fieldset',
));

$showallform->get_field('fieldset')->add_field('name', array(
  'type' => 'textfield',
  'validate' => array('multiple_by[3]','ReQuired'), // will be reordered and normalized
  'preprocess' => array('trim'),
  'title' => 'Your name',
  'tooltip' => TRUE,
  'attributes' => array(
    'style' => 'width: 100%',
   ),
));
$showallform->get_field('fieldset')->add_field('email', array(
  'type' => 'textfield',
  'validate' => array('required', 'email'),
  'title' => 'Your email address',
  'attributes' => array(
    'style' => 'width: 100%',
   ),
));
$showallform->get_field('fieldset')->add_field('password', array(
  'type' => 'password',
  // 'validate' => array('required'),
  'title' => 'Your Password',
  'attributes' => array(
    'style' => 'width: 100%',
   ),
  'with_confirm' => TRUE,
));
$showallform->get_field('fieldset')->add_field('markup1', array(
  'type' => 'markup',
  'value' => 'aaaa',
  'weight' => -10,
));


$showallform->add_field('fieldset2',array(
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
->add_field('masked',array(
  'title' => 'Phone',
  'type' => 'maskedfield',
  'mask'=>'0000-0000',
));


$accordion = new cs_accordion(array('attributes'=>array(
  // 'style' => 'width: 500px',
)),'accordion');

$accordion->add_tab('accordion1');
$accordion->add_tab('accordion2');

$accordion->add_field('spinner', array(
  'type' => 'spinner',
  'title' => 'Select a value',
),0);

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
),1);
$showallform->add_field($accordion->get_name(), $accordion);


$showallform->add_field('tabs',array(
  'type' => 'tabs',
  'attributes'=>array(
    // 'style' => 'width: 500px',
  ),
))
->add_tab('tab1') //index 0
->add_tab('tab2') //index 1
->add_field('markup2',array(
  'type' => 'markup',
  'value' => 'bbb',
),0) //to tab 0
->add_field('markup3',array(
  'type' => 'markup',
  'value' => 'ccc',
),1) //to tab 1
->add_field('checkboxes', array(
  'type' => 'checkboxes',
  'options' => array(0=>'zero',1=>'one',2=>'two'),
  'default_value' => 1,
)) //to tab 0
->add_field('file', array(
  'type' => 'file',
  'destination' => dirname(__FILE__),
  // 'validate' => array('required'),
),1) //to tab 1
->add_field('selectmenu', array(
  'type' => 'selectmenu',
  'title' => 'select a number',
  'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
  'default_value' => '2',
),1) //to tab 1
->add_field('slider', array(
  'type' => 'slider',
  'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
  'default_value' => '2',
),1); //to tab 1


$showallform->add_field('hidden1', array(
  'type' => 'hidden',
  'default_value' => 'aaaa',
));


$sortable = $showallform->add_field('sortable',array(
  'type' => 'sortable',
));

for($i=0;$i<5;$i++){
  $field = array(
    'title' => 'Textfield '.($i+1),
    'type' => 'textfield',
  );
  $sortable->add_field('sortable_field_'.$i,$field);
}

$showallform->add_field('container', array(
  'type' => 'tag_container',
  'weight' => 1000,
));

$showallform->add_field('autocomplete', array(
  'type' => 'autocomplete',
  'title' => 'autocomplete',
  'options' => array(
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
  ),
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


//############################################################################//
//############################################################################//
//############################################################################//



$plupload_form = new cs_form(array('form_id' => 'plupload'));
$plupload_form->add_field('files_upload', array(
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


$plupload_form->add_field('submit', array(
  'type' => 'submit',
));
