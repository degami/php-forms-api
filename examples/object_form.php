<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

if(isset($_GET['clearsession'])){
  session_destroy();
  session_start();
}

use Degami\PHPFormsApi as FAPI;

function myclass_submit(&$form) {
  $form_values = $form->values();
  $form->add_highlight('Object submitted.');
  return $form_values;
}

class MyClass{

  public $name;
  public $surname;
  public $birthday;
  public $number;

  function __construct( $name, $surname, $birthday, $number)
  {
    $this->name = $name;
    $this->surname = $surname;
    $this->birthday = $birthday;
    $this->number = $number;
  }
}

$classObject = new MyClass('Mirko','De Grandis',new \DateTime('1980-01-12'),1);
$form = FAPI\form_builder::object_form($classObject);

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example object form</title>
  <?php include "header.php";?>
</head>

<body>
  <h1>Example objectForm</h1>
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
