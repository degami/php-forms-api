<?php
@require_once('googlekeys.php');
if(!defined('GOOGLE_API_KEY')){
  define('GOOGLE_API_KEY', '<google maps key here>');
}
// if sessions are enabled then the form uses a token for extra security against CSRF
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;
session_start();

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function locationsform_submit(&$form) {
  $form_values = $form->values();
  return $form_values;
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  // $form->reset();
}

$form = FAPI\form_builder::get_form('locationsform');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example locations form</title>
  <?php include "header.php";?>

  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.1/themes/ui-lightness/jquery-ui.css">
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp&amp;libraries=geometry,places&amp;key=<?php print GOOGLE_API_KEY;?>"></script>
</head>

<body>
  <h1>Locations Form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  </div>
  <div id="page">
    <pre style="font-size:10px;"><?php $form->process(); ?></pre>
    <?php if ($form->is_submitted()): ?>
      <!-- if the form was reset during the submit handler we would never see this -->
      <pre><?php var_export($form->get_submit_results());?></pre>
      <p>Thanks for submitting the form.</p>
    <?php else: ?>
      <?php print $form; ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
