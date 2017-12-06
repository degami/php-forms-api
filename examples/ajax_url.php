<?php
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;
session_start();

$form = FAPI\form_builder::get_form('contactform_ajax');
$form->process();

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function contactform_ajax_submit(&$form) {
  $form_values = $form->values();
  return $form_values;
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  // $form->reset();
}

if ($form->is_submitted()):
  print json_encode( array( 'html' => '<p>Thanks for submitting the form.</p> <pre>'.var_export($form->get_submit_results('contactform_ajax_submit'),TRUE).'</pre>', 'js' => '' , 'is_submitted' => TRUE ) );
else:
  print $form->render(/* 'json' */);
endif;
