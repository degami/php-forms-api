<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

function validate_multiple_by($string,$length = 1){
  if(!is_numeric($length) || $length == 0) $length = 1;
  return ( strlen("".$string) > 0 && (strlen("".$string)%$length) == 0) ? TRUE : '<strong>%t</strong> length must be multiple of '.$length;
}


function showall_submit(&$form) {
  $form_values = $form->values();
  // var_export($form);
  // get submission triggering element

  // var_dump($form->get_triggering_element());
  return $form_values;
  // Reset the form if you want it to display again.
  // $form->reset();
}

function showall_validate(&$form) {
  $form_values = $form->values();
  if($form_values['fieldset']['name'] == 'aaa' && $form_values['tabs']['slider']==2) return "You shall not pass!!!";

  return TRUE;
}
$form = cs_form_builder::get_form('showallform');

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example Show them all form</title>
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
  <script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
  <script type="text/javascript" src="http://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>
  <script type="text/javascript" src="http://dbushell.github.io/Nestable/jquery.nestable.js"></script>
  <script type="text/javascript"><?php

    // if you wish to have form's js scripts here rather than after the form....
    print $form->generate_js();

  ?></script>
  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.1/themes/flick/jquery-ui.css">
</head>

<body>
<a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
<div id="page">
  <h1>Example Show them all form</h1>

  <pre style="font-size:10px;"><?php $form->process(); ?></pre>
  <?php if ($form->is_submitted()): ?>
    <!-- if the form was reset during the submit handler we would never see this -->
    <p>Thanks for submitting the form.</p>
    <pre><?php var_export($form->get_submit_results());?></pre>
  <?php else: ?>
    <?php print $form->render(); ?>
  <?php endif; ?>
  <pre style="font-size:10px;"><?php // print_r($form); ?></pre>
</div>
</body>
</html>
