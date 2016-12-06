<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

if(isset($_GET['clearsession'])){
  session_destroy();
  session_start();
}

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function eventsform_submit(&$form) {
  $form_values = $form->values();
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  // $form->reset();
  return $form_values;
}

$form = cs_form_builder::get_form('eventsform');

if( isset($_REQUEST['partial']) ){
  print $form->render();
}else{
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example contact form</title>

  <?php include "header.php";?>
</head>

<body>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>?clearsession=1">Go back</a>
  </div>
  <div id="page">
    <pre style="font-size:10px;"><?php $form->process(); ?></pre>
    <h1>Events Form</h1>
    <?php if ($form->is_submitted()): ?>
      <!-- if the form was reset during the submit handler we would never see this -->
      <pre><?php var_dump($form->get_submit_results());?></pre>
      <p>Thanks for submitting the form.</p>
    <?php else: ?>
      <?php print $form->render(); ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
<?php
}
