<?php
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;

session_start();

function myclass_submit(&$form)
{
    $form_values = $form->values()->toArray();
    $form->addHighlight('Object submitted.');
    return $form_values;
}

class MyClass
{

    public $id;
    public $name;
    public $surname;
    public $birthday;
    public $number;

    function __construct($name, $surname, $birthday, $number)
    {
        $this->id = 1;
        $this->name = $name;
        $this->surname = $surname;
        $this->birthday = $birthday;
        $this->number = $number;
    }
}

$classObject = new MyClass('Mirko', 'De Grandis', new \DateTime('1980-01-12'), 1);
$form = FAPI\FormBuilder::objectForm($classObject);

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
