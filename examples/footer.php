<?php
use Degami\PHPFormsApi as FAPI;

?>

    <pre style="font-size:10px;"><?php // print_r($form); ?></pre>

<?php
    $has_session = FAPI\FormBuilder::sessionPresent();
if ($has_session) : ?>
    <div class="sessioninfo">
      <div class="sessiontitle">Session Info</div>
      <pre class="sessionbody"><?php print_r($_SESSION); ?></pre>
    </div>
<?php endif; ?>

    <div class="functiontitle">function body</div>
    <pre class="functionbody"><?php print $form->getDefinitionBody();?></pre>

<?php
