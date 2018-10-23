<?php
ob_start();
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

session_start();

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function pluploadform_submit(&$form)
{
    $form_values = $form->getValues();
    if (is_array($form_values['files_upload']) && count($form_values['files_upload'])>0) {
        print $value->temppath . " => ".getcwd() . DIRECTORY_SEPARATOR . $value->name."\n";
        rename($value->temppath, getcwd() . DIRECTORY_SEPARATOR . $value->name);
    }
}

$form = FAPI\FormBuilder::getForm('pluploadform');


?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>PLUpload Form</title>
    <?php include "header.php";?>
  <link type="text/css" rel="stylesheet" href="http://www.plupload.com/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" media="screen" />
  <link href="http://www.plupload.com/plupload//js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" rel="stylesheet" media="screen">
  <script type="text/javascript" src="http://www.plupload.com/plupload/js/plupload.full.min.js" charset="UTF-8"></script>
  <script type="text/javascript" src="http://www.plupload.com/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js" charset="UTF-8"></script>
  <script type="text/javascript" src="https://raw.githubusercontent.com/moxiecode/plupload/master/src/jquery.plupload.queue/jquery.plupload.queue.js" charset="UTF-8"></script>
</head>

<body>
  <h1>PLUpload Form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  </div>
  <div id="page">
    <pre style="font-size:10px;"><?php $form->process(); ?></pre>
    <?php if ($form->isSubmitted()) : ?>
      <!-- if the form was reset during the submit handler we would never see this -->
      <p>Thanks for submitting the form.</p>
    <?php else : ?>
      <?php print $form->render(); ?>
    <?php endif; ?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
