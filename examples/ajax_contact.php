<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../vendor/autoload.php';
include "forms.php";

use Degami\PHPFormsApi as FAPI;

$form = FAPI\form_builder::get_form('contactform_ajax');

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
    <?php print $form->render('html');?>

    <?php include "footer.php";?>
  </div>
</body>
</html>
