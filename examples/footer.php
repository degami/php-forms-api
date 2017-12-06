<?php
use Degami\PHPFormsApi as FAPI;

?>

    <pre style="font-size:10px;"><?php // print_r($form); ?></pre>

<?php
    $has_session = FAPI\form_builder::session_present();
    if ($has_session) : ?>
    <h3>Session Info</h3>
    <pre class="sessioninfo"><?php print_r($_SESSION); ?></pre>
<?php endif; ?>

    <div class="functiontitle">function body</div>
    <pre class="functionbody"><?php print $form->get_definition_body();?></pre>

<?php
