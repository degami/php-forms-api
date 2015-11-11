<?php

session_start();
if(isset($_GET['clearsession'])){
  session_destroy();
  session_start();
}

require_once '../src/form.php';
include "forms.php";
require 'recaptchalib.php';
define('RECAPTCHA_PUBLIC_KEY','');
define('RECAPTCHA_PRIVATE_KEY','');


function multistepform_submit(&$form) {
  $form_values = $form->values();
  // var_export($form);
  // get submission triggering element
  //var_dump($form->get_triggering_element());
  print_r($form_values);
  // Reset the form if you want it to display again.
  // $form->reset();
}

$form = cs_form_builder::get_form('multistepform');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example multistep form</title>
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

  .sessioninfo{
    font-size: 10px;
    line-height: 12px;
    padding: 10px;
    background: #FFDD3E;
    border: solid 3px #5E5900;
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
<a href="multistep.php?clearsession=1">Go back</a>
<div id="page">
  <h1>Example Multistep Form</h1>

  <pre style="font-size:10px;"><?php $form->process(); ?></pre>
  <?php if ($form->is_submitted()): ?>
    <!-- if the form was reset during the submit handler we would never see this -->
    <p>Thanks for submitting the form.</p>
    <pre><?php var_export($form->get_submit_results());?></pre>
  <?php else: ?>
    <?php print $form->render(); ?>
  <?php endif; ?>
  <h3>Session Info</h3>
  <pre class="sessioninfo"><?php print_r($_SESSION); ?></pre>
</div>
</body>
</html>
