<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function contactform_submit(&$form) {
  $form_values = $form->values();
  $form->add_highlight('Message sent!');
  print_r($form_values);
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  $form->reset();
}

// function my_contactform_form_alter($form){
//   $form->get_field('fieldset')->remove_field('message');
// }

$form = cs_form_builder::get_form('contactform');

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example contact form</title>
  <?php include "header.php";?>
</head>

<body>
  <h1>Example Form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  </div>
  <div id="page">
    <pre style="font-size:10px;"><?php $form->process(); ?></pre>
    <?php if ($form->is_submitted()): ?>
      <!-- if the form was reset during the submit handler we would never see this -->
      <p>Thanks for submitting the form.</p>
      <pre><?php var_export($form->get_submit_results());?></pre>
    <?php else: ?>
      <?php print $form->render(); ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
