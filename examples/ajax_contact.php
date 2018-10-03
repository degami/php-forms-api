<?php
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

session_start();

$form = FAPI\FormBuilder::getForm('contactform_ajax');

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
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  </div>
  <div id="page">
    <?php print $form->render('html');?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
