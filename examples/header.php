<style>
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

  body {
    font-size: 20px;
    line-height: 1.2em;
    font-family: Arial;
    font-size: 14px;
    background: #c6c6c6;
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
    font-size: 12px;
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

  .functiontitle{
    border: solid 1px #c6c6c6;
    background-color: #c6c6c6;
    padding: 3px;
    text-transform: uppercase;
    color: #fff;
    text-shadow: 1px 1px #666;
    margin: 0;
    font-weight: bold;
  }

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
  </style>
  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.1/themes/flick/jquery-ui.css">
