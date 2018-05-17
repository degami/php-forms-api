<?php
require_once '../vendor/autoload.php';
include_once "forms.php";
use Degami\PHPFormsApi as FAPI;
session_start();

// function my_textfield_field_alter(&$options, &$name){
//   $name = 'my_'.$name;
//   $options['attributes'] = ['style' => 'border: solid 1px #f00;'];
// }
// function my_textfield_field_render_output_alter(&$html){
//   $html = 'aaa'.$html;
// }
// function __($str){
//   return "__ $str __";
// }

// Submit function to call when the form is submitted and passes validation.
// This is where you would send the email (using PHP mail function)
// as this is not a real example I'm just outputting the values for now.
function contactform_submit(&$form) {
  $form_values = $form->values();

  $out = [];
  foreach ($form_values->fieldset as $key => $value) {
    $out[$key] = $value;
  }

  return $out;

  return implode(' - ',
    [
      $form_values->fieldset->name,
      $form_values->fieldset->email,
      $form_values->fieldset->message,
    ]);

  return $form_values;
  $form->add_highlight('Message sent!');
  print_r($form_values);
  //var_dump($form->get_triggering_element());
  // Reset the form if you want it to display again.
  $form->reset();
}

// function my_contactform_form_alter($form){
//  $form->get_field('fieldset')->remove_field('message');
// }

$form = FAPI\form_builder::get_form('contactform');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example contact form</title>
  <?php include "header.php";?>
  <script type="text/javascript" src='http://cdn.tinymce.com/4/tinymce.min.js'></script>
  <style>
    input[type=text],textarea{
      width: 100%;
    }
  </style>
</head>

<body>
  <h1>Example Form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
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
