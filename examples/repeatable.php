<?php
@require_once('googlekeys.php');

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../vendor/autoload.php';
include "forms.php";

if(isset($_GET['clearsession'])){
  session_destroy();
  session_start();
}

use Degami\PHPFormsApi as FAPI;

// function __($str){
//   return "__ $str __";
// }

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function repeatableform_submit(&$form) {
  $form_values = $form->values()->toArray();
  return $form_values;
}

// function my_contactform_form_alter($form){
//  $form->get_field('fieldset')->remove_field('message');
// }

$form = FAPI\form_builder::get_form('repeatableform');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example contact form</title>
  <?php include "header.php";?>
  <script type="text/javascript" src='http://cdn.tinymce.com/4/tinymce.min.js'></script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp&amp;libraries=geometry,places&amp;key=<?php print GOOGLE_API_KEY;?>"></script>

  <style>
    input[type=text],textarea{
      width: 100%;
    }
  </style>
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
      <pre><?php var_export($form->values());?></pre>
    <?php else: ?>
      <?php print $form->render(); ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
