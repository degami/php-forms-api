<?php
// if sessions are enabled then the form uses a token for extra security against CSRF
@session_start();
require_once '../vendor/autoload.php';
include_once "forms.php";

if(isset($_GET['clearsession'])){
  session_destroy();
  session_start();
}
?><style>
  a{
      color: #888;
      text-decoration: none;
  }
  a:hover{
      text-decoration: underline;
  }
  a:active,a:visited{
      color: 555;
  }

  /*body {
    font-size: 20px;
    line-height: 1.2em;
    font-family: Arial;
    font-size: 14px;
    background: #c6c6c6;
  }*/

  body{
      font-family: Lato;
      font-size: 16px;
      line-height: 1.2em;
      padding: 30px;
      min-width: 940px;
      border: solid 1px #cecece;
  }

  h1{
      color: #fff;
      text-transform: uppercase;
      background-color: #888;
      padding: 30px;
      line-height: 1em;
      margin: -30px;
      margin-bottom: 20px;
      text-shadow: 1px 1px 3px #333;
  }
  ul{
      list-style-type: square;
      margin: 0;
      padding: 10px;
  }
  li{
      padding: 2px 0;
  }
  li.sep{
      list-style-type: none;
  }
  li.sep hr{
      border: 0;
      border-top: dotted 1px #cecece;
  }

  label { display: block; }
  span.required { color: red; }
  .form-item.has-errors input,
  .form-item.has-errors select,
  .form-item.has-errors textarea {
    background-color: #FFA07A;
  }

  .form-item.has-errors label {
    color: #ff4f64;
    font-weight: bold;
  }


  .form-item input,
  .form-item select,
  .form-item button,
  .form-item textarea{
    font-size: inherit;
    max-width: 99%;
    border: solid 1px #cecece;
    padding: 4px;
  }
  input[type=text],
  input[type=password],
  input[type=checkbox],
  textarea{
    border: solid 1px #cecece;
    width: auto;
    margin: 0;
  }
  input.spinner{border: 0;}
  fieldset.collapsed{
    border-top-width: 1px;
    border-bottom-width: 0px;
    border-right-width: 0px;
    border-left-width: 0px;
  }

  .fieldset-container,
  .accordion-container,
  .tabs-container,
  .sortable-container,
  .autocomplete-container,
  .div_container-container{
    margin-top: 20px;
  }

  #actions .form-item{
    display: inline-block;
    margin-right: 10px;
  }

  #page{
    width: 78%;
    padding: 1%;
    margin: auto;
    background: #fff;
  }

  .sessiontitle,
  .functiontitle{
    border: solid 1px #c6c6c6;
    background-color: #c6c6c6;
    padding: 3px;
    text-transform: uppercase;
    color: #fff;
    text-shadow: 1px 1px #666;
    margin: 0;
    margin-top: 10px;
    font-weight: bold;
  }

  .sessionbody,
  .functionbody{
    max-height: 200px;
    overflow-y: auto;
    border: solid 1px #c6c6c6;
    padding: 10px;
    margin: 0;
    font-size: 11px;
  }

  .gmaplocation .reverse,
  .gmaplocation .gmap{
    width: 100%;
    margin: 10px 0;
  }

  .errorsbox{
    position: relative;
  }

  .errorsbox .ui-icon{
    position: absolute;
    top: 5px;
    left: 5px;
  }

  .errorsbox li{
    margin-left: 30px;
    list-style-type: none;
  }

  #multiselect-table{
    width:50%;
  }

  .ui-widget{
    font-size: inherit !important;
    font-family: inherit !important;
  }

  </style>
  <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
  <link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!--  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.0/themes/eggplant/jquery-ui.css"> -->
<script type="text/javascript">
  (function(){
    // var _z = console;
    // Object.defineProperty( window, "console", {
    //   get : function(){
    //     throw "Sorry, Can't execute scripts!";
    //     return _z;
    //   },
    //   set : function(val){
    //     _z = val;
    //   }
    // });

    // document.onkeypress = function (event) {
    //     event = (event || window.event);
    //     if (event.keyCode == 123) {
    //        //alert('No F-12');
    //         return false;
    //     }
    // }
    // document.onmousedown = function (event) {
    //     event = (event || window.event);
    //     if (event.keyCode == 123) {
    //         //alert('No F-keys');
    //         return false;
    //     }
    // }
    // document.onkeydown = function (event) {
    //     event = (event || window.event);
    //     if (event.keyCode == 123) {
    //         //alert('No F-keys');
    //         return false;
    //     }
    // }
  })();

</script>
