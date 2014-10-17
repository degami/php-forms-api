<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require '../src/form.php';

// Generate a simple contact form
$form = new cs_form(array(
  'form_id' => 'contact',
//  'attributes'=>array('enctype'=>'multipart/form-data')
));

$object = new stdClass;
$object->val1='val1';

$form->add_field('object',array(
  'type'=>'value',
  'value' => $object,
));

$fieldset = new cs_fieldset(
  array('attributes'=>array(
    'style' => 'width: 500px;padding: 10px 10px 10px 5px;',
  ),
  'collapsible' => true,
  'title' => 'my fieldset',
),'fieldset');

$fieldset2 = new cs_fieldset(
  array('attributes'=>array(
    'style' => 'width: 500px;padding: 10px 10px 10px 5px;',
  ),
  'collapsible' => true,
  'collapsed' => true,
  'title' => 'my fieldset 2',
),'fieldset2');

$fieldset->add_field('name', array(
  'type' => 'textfield',
  'validate' => array('required'),
  'preprocess' => array('trim'),
  'title' => 'Your name',
  'attributes' => array(
    'style' => 'width: 100%',
   ),
));
$fieldset->add_field('email', array(
  'type' => 'textfield',
  'validate' => array('required', 'email'),
  'title' => 'Your email address',
  'attributes' => array(
    'style' => 'width: 100%',
   ),
));
$fieldset2->add_field('message', array(
  'type' => 'textarea',
  'postprocess' => array('xss'),
  'title' => 'Your message',
  'rows' => 10,
  'attributes' => array(
    'placeholder' => 'Type your message',
    'style' => 'width: 100%',
   ),
));

$fieldset->add_field('markup1', array(
  'type' => 'markup',
  'value' => 'aaaa',
  'weight' => -10,
));
$form->add_field($fieldset->get_name(), $fieldset);
$form->add_field($fieldset2->get_name(), $fieldset2);

$tabs = new cs_tabs(array('attributes'=>array(
  'style' => 'width: 500px',
)),'tabs');

$tabs->add_tab('tab1');
$tabs->add_tab('tab2');


$accordion = new cs_accordion(array('attributes'=>array(
  'style' => 'width: 500px',
)),'accordion');

$accordion->add_tab('accordion1');
$accordion->add_tab('accordion2');

$accordion->add_field('spinner', array(
  'type' => 'spinner',
  'title' => 'Select a value',
),0);

$accordion->add_field('date', array(
  'type' => 'date',
),1);

$tabs->add_field('markup2',array(
  'type' => 'markup',
  'value' => 'bbb',
),0);
$tabs->add_field('markup3',array(
  'type' => 'markup',
  'value' => 'ccc',
),1);
$tabs->add_field('checkboxes', array(
  'type' => 'checkboxes',
  'options' => array(0=>'zero',1=>'one',2=>'two'),
  'default_value' => 1,
));
$tabs->add_field('file', array(
  'type' => 'file',
  'destination' => dirname(__FILE__),
),1);

$tabs->add_field('select', array(
  'type' => 'select',
  'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
  'default_value' => '2',
),1);

$tabs->add_field('slider', array(
  'type' => 'slider',
  'options' => array('1'=>'one','2'=>'two','3'=>'three','four'=>array('5'=>'five','6'=>'six','7'=>'seven'),'8'=>'eight'),
  'default_value' => '2',
),1);

$form->add_field($accordion->get_name(), $accordion);
$form->add_field($tabs->get_name(), $tabs);

$form->add_field('hidden1', array(
  'type' => 'hidden',
  'default_value' => 'aaaa',
));

$form->add_field('checkbox', array(
  'type' => 'checkbox',
  'default_value' => 'checkbox',
  'title' => 'Check me',
  'validate' => array( array('validator'=>'required','error_message'=>'You must check the <strong>%t</strong> checkbox!' ) ),
));

$form->add_field('submit', array(
  'type' => 'submit',
  'value' => 'Send',
));

$form->add_field('reset', array(
  'type' => 'reset',
  'value' => 'Reset',
));


// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function contact_submit(&$form) {
  $form_values = $form->values();
  print_r($form_values);
  // Reset the form if you want it to display again.
  // $form->reset();
}


?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Example contact form</title>
	<style>
	label { display: block; }
	span.required { color: red; }
	.error { background: #FFA07A; }
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
	</style>
  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
</head>

<body>
<a href="contact.php">Go back</a>
<pre style="font-size:10px;"><?php $form->process(); ?></pre>
<h1>Example Form</h1>
<?php if ($form->is_submitted()): ?>
  <!-- if the form was reset during the submit handler we would never see this -->
  <p>Thanks for submitting the form.</p>
<?php else: ?>
  <?php print $form->render(); ?>
<?php endif; ?>
<pre style="font-size:10px;"><?php // print_r($form); ?></pre>
</body>
</html>
