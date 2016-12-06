    <pre style="font-size:10px;"><?php // print_r($form); ?></pre>

    <h3>Session Info</h3>
    <pre class="sessioninfo"><?php print_r($_SESSION['form_token']); ?></pre>

    <div class="functiontitle">function body</div>
    <pre class="functionbody"><?php print $form->get_definition_body();?></pre>

<?php
