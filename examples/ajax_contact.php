<?php

// if sessions are enabled then the form uses a token for extra security against CSRF
session_start();
require_once '../src/form.php';
include "forms.php";

$form = $contactform_ajax;

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example contact form</title>
  <style>
  body {
    font-family: Arial;
    font-size: 14px;
    background: #c6c6c6;
  }
  label { display: block; }
  span.required { color: red; }
  .form-item.error input,
  .form-item.error select,
  .form-item.error textarea {
    background-color: #FFA07A;
  }

  .form-item.error label {
    color: #ff4f64;
    font-weight: bold;
  }


  .form-item input,
  .form-item select,
  .form-item button,
  .form-item textarea{
    font-size: 12px;
    max-width: 99%;
    width: 400px;
    border: solid 1px #cecece;
    padding: 4px;
  }
  input[type=text],
  input[type=password],
  input[type=checkbox],
  textarea{
    border: solid 1px #cecece;
    margin: 0;
  }

  input[type=submit]{
    width: auto;
  }

  input.spinner{border: 0;}
  fieldset.collapsed{
    border-top-width: 1px;
    border-bottom-width: 0px;
    border-right-width: 0px;
    border-left-width: 0px;
  }

  #page{
    width: 78%;
    padding: 1%;
    margin: auto;
    background: #fff;
  }
  </style>

  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.1/themes/flick/jquery-ui.css">

</head>

<body>
  <a href="ajax_contact.php">Go back</a>
  <div id="page">
  <h1>Example Form</h1>
  <script type="text/javascript">
  (function($){
    $(document).ready(function(){

      var attachFormBehaviours = function (){
        $('#<?php print $form->get_id();?>').submit(function(evt){
          evt.preventDefault();
          $.post( "<?php print $form->get_ajax_url();?>", $('#<?php print $form->get_id();?>').serialize(), function( data ) {
            var response = $.parseJSON(data);
            $('#formcontainer').html('');

            $(response.html).appendTo( $('#formcontainer') );
            if( $.trim(response.js) != '' ){
              eval( response.js );
            };
            attachFormBehaviours();
          });
          return false;
        });
      }

      $.getJSON('<?php print $form->get_ajax_url();?>',function(response){
        $(response.html).appendTo( $('#formcontainer') );
        if( $.trim(response.js) != '' ){
          eval( response.js );
        };
        attachFormBehaviours();
      });
    });
  })(jQuery);
  </script>
  <div id="formcontainer"></div>
  </div>
</body>
</html>
