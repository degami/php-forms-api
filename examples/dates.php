<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function dates_submit(&$form) {
  $form_values = $form->values();
  print_r($form_values);
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  // $form->reset();
}

$form = $dates_form;

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
    width: 400px;
    border: solid 1px #cecece;
    padding: 4px;
  }
  input[type=text],
  input[type=password],
  input[type=checkbox],
  textarea{
    border: solid 1px #cecece;
    margin: 0;
  }

  input[type=submit]{
    width: auto;
  }

  input.spinner{border: 0;}
  fieldset.collapsed{
    border-top-width: 1px;
    border-bottom-width: 0px;
    border-right-width: 0px;
    border-left-width: 0px;
  }

  .form-item select{
    width: auto;
  }

  #page{
    width: 78%;
    padding: 1%;
    margin: auto;
    background: #fff;
  }
  </style>
</head>

<body>
  <a href="contact.php">Go back</a>
  <div id="page">
  <pre style="font-size:10px;"><?php $form->process(); ?></pre>
  <h1>Dates Form</h1>
  <?php if ($form->is_submitted()): ?>
    <!-- if the form was reset during the submit handler we would never see this -->
    <pre><?php var_export($form->get_submit_results());?></pre>
    <p>Thanks for submitting the form.</p>
  <?php else: ?>
    <?php print $form->render(); ?>
  <?php endif; ?>
  <pre style="font-size:10px;"><?php // print_r($form); ?></pre>
  </div>
</body>
</html>