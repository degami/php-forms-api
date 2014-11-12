<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require '../src/form.php';

function validate_multiple_by($string,$length = 1){
  if(!is_numeric($length) || $length == 0) $length = 1;
  return ( strlen("".$string) > 0 && (strlen("".$string)%$length) == 0) ? TRUE : '<strong>%t</strong> length must be multiple of '.$length;
}

// Generate a simple contact form
$form = new cs_form(array(
  'form_id' => 'contact',
  'inline_errors' => TRUE,
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
  'type' => 'textfield',
  'validate' => array('required', 'email'),
  'title' => 'Your email address',
  'attributes' => array(
    'style' => 'width: 100%',
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
));
$form->get_field('fieldset')->add_field('markup1', array(
  'type' => 'markup',
  'value' => 'aaaa',
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


$form->add_field('hidden1', array(
  'type' => 'hidden',
  'default_value' => 'aaaa',
));


$sortable = $form->add_field('sortable',array(
  'type' => 'sortable',
));

for($i=0;$i<5;$i++){
  $field = array(
    'title' => 'Textfield '.($i+1),
    'type' => 'textfield',
  );
  $sortable->add_field('sortable_field_'.$i,$field);
}

$form->add_field('container', array(
  'type' => 'tag_container',
  'weight' => 1000,
));

$form->add_field('autocomplete', array(
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

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function contact_submit(&$form) {
  $form_values = $form->values();
  // var_export($form);
  // get submission triggering element

  // var_dump($form->get_triggering_element());
  print_r($form_values);
  // Reset the form if you want it to display again.
  // $form->reset();
}

function contact_validate(&$form) {
  $form_values = $form->values();
  if($form_values['fieldset']['name'] == 'aaa' && $form_values['tabs']['slider']==2) return "You shall not pass!!!";

  return TRUE;
}

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Example contact form</title>
	<style>
  body {
    font-family: Arial;
    font-size: 14px;
    background: #c6c6c6;
  }
	label { display: block; }
	span.required { color: red; }
	.form-item.error input,
  .form-item.error select,
  .form-item.error textarea {
    background-color: #FFA07A;
  }

  .form-item.error label {
    color: #ff4f64;
    font-weight: bold;
  }


  .form-item input,
  .form-item select,
  .form-item button,
  .form-item textarea{
    font-size: 12px;
    max-width: 99%;
    border: solid 1px #cecece;
    padding: 4px;
  }
  input[type=text],
  input[type=password],
  input[type=checkbox],
  textarea{
    border: solid 1px #cecece;
    width: auto;
    margin: 0;
  }
  input.spinner{border: 0;}
  fieldset.collapsed{
    border-top-width: 1px;
    border-bottom-width: 0px;
    border-right-width: 0px;
    border-left-width: 0px;
  }

  .fieldset-container,
  .accordion-container,
  .tabs-container,
  .sortable-container,
  .autocomplete-container,
  .div_container-container{
    margin-top: 20px;
  }

  #actions .form-item{
    display: inline-block;
    margin-right: 10px;
  }

  #page{
    width: 78%;
    padding: 1%;
    margin: auto;
    background: #fff;
  }
	</style>
  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
  <script type="text/javascript" src="http://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>
  <script type="text/javascript"><?php
/*
    // if you wish to have form's js scripts here rather than after the form....
    $form->pre_render(); // call all elements pre_render, so they can attach js to the form element
    print $form->generate_js();
*/
  ?></script>
  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.1/themes/flick/jquery-ui.css">
</head>

<body>
<a href="contact.php">Go back</a>
<div id="page">
  <h1>Example Form</h1>

  <pre style="font-size:10px;"><?php $form->process(); ?></pre>
  <?php if ($form->is_submitted()): ?>
    <!-- if the form was reset during the submit handler we would never see this -->
    <p>Thanks for submitting the form.</p>
  <?php else: ?>
    <?php print $form->render(); ?>
  <?php endif; ?>
  <pre style="font-size:10px;"><?php // print_r($form); ?></pre>
</div>
</body>
</html>
