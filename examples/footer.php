<?php
use Degami\PHPFormsApi as FAPI;

?>

<?php
    $has_session = FAPI\FormBuilder::sessionPresent();
if ($has_session) : ?>
    <div class="sessioninfo">
      <div class="sessiontitle">Session Info</div>
      <pre class="sessionbody"><?php print_r(FAPI\FormBuilder::getSessionBag(true)->toArray());// print_r($form); ?><?php print_r($_SESSION); ?></pre>
    </div>
<?php endif; ?>

    <div class="functiontitle">function body</div>
    <pre class="functionbody"><?php print $form->getDefinitionBody();?></pre>

<?php
