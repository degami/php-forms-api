<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function pluploadform_submit(&$form) {
  $form_values = $form->values();
  if(is_array($form_values['files_upload']) && count($form_values['files_upload'])>0){
    print $value->temppath . " => ".getcwd() . DIRECTORY_SEPARATOR . $value->name."\n";
    rename($value->temppath, getcwd() . DIRECTORY_SEPARATOR . $value->name);
  }
}

$form = cs_form_builder::get_form('pluploadform');


?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>PLUpload Form</title>
  <style>
  body {
    font-family: Arial;
    font-size: 14px;
    background: #c6c6c6;
  }
  label { display: block; }
  span.required { color: red; }
  .form-item.has-errors input,
  .form-item.has-errors select,
  .form-item.has-errors textarea {
    background-color: #FFA07A;
  }

  .form-item.has-errors label {
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

  #page{
    width: 78%;
    padding: 1%;
    margin: auto;
    background: #fff;
  }
  </style>

  <link type="text/css" rel="stylesheet" href="http://www.plupload.com/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.1/themes/flick/jquery-ui.css">
  <link href="http://www.plupload.com/plupload//js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" rel="stylesheet" media="screen">

  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
  <script type="text/javascript" src="http://www.plupload.com/plupload/js/plupload.full.min.js" charset="UTF-8"></script>
  <script type="text/javascript" src="http://www.plupload.com/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js" charset="UTF-8"></script>
  <script type="text/javascript" src="https://raw.githubusercontent.com/moxiecode/plupload/master/src/jquery.plupload.queue/jquery.plupload.queue.js" charset="UTF-8"></script>
</head>

<body>
  <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  <div id="page">
  <pre style="font-size:10px;"><?php $form->process(); ?></pre>
  <h1>PLUpload Form</h1>
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
