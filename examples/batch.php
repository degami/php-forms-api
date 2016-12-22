<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

$form = cs_form_builder::get_form('batchoperationsform');

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
  <h1>Batch Operation Form</h1>
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
<?php } ?>
