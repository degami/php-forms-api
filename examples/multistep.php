<?php
ob_start();
define('RECAPTCHA_PUBLIC_KEY', '');
define('RECAPTCHA_PRIVATE_KEY', '');

require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

session_start();

function multistepform_submit(&$form)
{
    $form_values = $form->getValues()->toArray();
  // print_r($form);
  // get submission triggering element
  //var_dump($form->get_triggering_element());
    return $form_values;
  // Reset the form if you want it to display again.
  // $form->reset();
}

$form = FAPI\FormBuilder::getForm('multistepform');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example multistep form</title>

    <?php include "header.php";?>

  <script type="text/javascript" src="http://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>
  <script type="text/javascript"><?php
/*
    // if you wish to have form's js scripts here rather than after the form....
    $form->pre_render(); // call all elements pre_render, so they can attach js to the form element
    print $form->generate_js();
*/
    ?></script>
</head>

<body>
  <h1>Example Multistep Form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>?clearsession=1">Go back</a>
  </div>
  <div id="page">
    <pre style="font-size:10px;"><?php $form->process(); ?></pre>
    <?php if ($form->isSubmitted()) : ?>
      <!-- if the form was reset during the submit handler we would never see this -->
      <p>Thanks for submitting the form.</p>
      <pre><?php print_r($form->getSubmitResults());?></pre>
    <?php else : ?>
      <?php print $form->render(); ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
