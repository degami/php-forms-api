<?php
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

session_start();

function validate_multiple_by($string, $length = 1)
{
    if (!is_numeric($length) || $length == 0) {
        $length = 1;
    }
    return ( strlen("".$string) > 0 && (strlen("".$string)%$length) == 0) ? true : '<strong>%t</strong> length must be multiple of '.$length;
}


function nestableform_submit(&$form)
{
    $form_values = $form->values();
  // var_export($form);
  // get submission triggering element

  // var_dump($form->get_triggering_element());
    return $form_values;
  // Reset the form if you want it to display again.
  // $form->reset();
}

$form = FAPI\FormBuilder::getForm('nestableform');

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example Nestable form</title>

    <?php include "header.php";?>

  <script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
  <script type="text/javascript" src="http://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>
  <script type="text/javascript" src="http://dbushell.github.io/Nestable/jquery.nestable.js"></script>
  <script type="text/javascript"><?php

    // if you wish to have form's js scripts here rather than after the form....
    print $form->generateJs();

    ?></script>
</head>

<body>
  <h1>Example Nestable form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  </div>
  <div id="page">
    <pre style="font-size:10px;"><?php $form->process(); ?></pre>
    <?php if ($form->isSubmitted()) : ?>
      <!-- if the form was reset during the submit handler we would never see this -->
      <p>Thanks for submitting the form.</p>
      <pre><?php var_export($form->getSubmitResults());?></pre>
    <?php else : ?>
      <?php print $form->render(); ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
