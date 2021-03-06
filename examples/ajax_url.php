<?php
ob_start();
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

session_start();

$form = FAPI\FormBuilder::getForm('contactform_ajax');
$form->process();

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function contactform_ajax_submit(&$form)
{
    $form_values = $form->getValues();
    return $form_values;
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  // $form->reset();
}

if ($form->isSubmitted()) :
    print json_encode(array( 'html' => '<p>Thanks for submitting the form.</p> <pre>'.print_r($form->getSubmitResults('contactform_ajax_submit'), true).'</pre>', 'js' => '' , 'is_submitted' => true ));
else :
    print $form->render(/* 'json' */);
endif;
