<?php
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

session_start();

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function datesform_submit(&$form)
{
    $form_values = $form->getValues();
    return $form_values->toArray();
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  // $form->reset();
}

$form = FAPI\FormBuilder::getForm('datesform');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example dates form</title>
    <?php include "header.php";?>
</head>

<body>
  <h1>Dates Form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  </div>
  <div id="page">
    <pre style="font-size:10px;"><?php $form->process(); ?></pre>
    <?php if ($form->isSubmitted()) : ?>
      <!-- if the form was reset during the submit handler we would never see this -->
      <pre><?php print_r($form->getSubmitResults());?></pre>
      <p>Thanks for submitting the form.</p>
    <?php else : ?>
      <?php print $form->render(); ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
