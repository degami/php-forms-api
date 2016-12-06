<html>
<head>
<style>
body{
    font-family: Arial;
    font-size: 20px;
    line-height: 1.2em;
    padding: 30px;
    min-width: 940px;
    border: solid 1px #cecece;
}
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
label{
  width: 200px;
  font-weight: bold;
  display: inline-block;
  text-transform: uppercase;
}
</style>
<body>
<h1>php-forms-api</h1>
<ul>
<?php
/*if( $d = opendir(dirname(__FILE__)) ){
  while($dirent = readdir($d)){
    if($dirent[0] == '.' || !preg_match("/.*?\.(html?|php)$/i", $dirent)) continue;
    if( $dirent == basename(__FILE__) ) continue;
    echo '<li><a href="'.$dirent.'" target="_blank">'.$dirent.'</a></li>';
  }
}*/
$ignored_files = array(
    basename(__FILE__),
    'forms.php',
    'header.php',
    'footer.php',
    'file_plupload.php',
    'recaptchalib.php',
    'ajax_url.php',
);
foreach ( glob('*.php') as $dirent) {
    if($dirent[0] == '.' || !preg_match("/.*?\.(html?|php)$/i", $dirent)) continue;
    if( in_array($dirent, $ignored_files) ) continue;
    echo '<li><a href="'.$dirent.'" target="_blank">'.$dirent.'</a></li>';
}

?>
</ul>
</body>
</html>
