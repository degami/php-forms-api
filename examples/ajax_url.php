<?php
// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();

require_once '../src/form.php';
include "forms.php";

$form = $contactform_ajax;
$form->process();

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function contact_submit(&$form) {
  $form_values = $form->values();
  print_r($form_values);
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  // $form->reset();
}

if ($form->is_submitted()):
  print json_encode( array( 'html' => '<p>Thanks for submitting the form.</p> <pre>'.var_export($form->get_submit_results('contact_submit'),TRUE).'</pre>', 'js' => '' , 'is_submitted' => TRUE ) );
else:
  print $form->render(/* 'json' */);
endif;
