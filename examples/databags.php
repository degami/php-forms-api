<?php
require_once '../vendor/autoload.php';
include_once "forms.php";

use Degami\PHPFormsApi as FAPI;
use Degami\PHPFormsApi\Accessories\SessionBag;

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Example session bag</title>
    <?php include "header.php";?>
</head>

<body>
  <h1>Example Form</h1>
  <div>
    <a href="<?php print dirname($_SERVER['PHP_SELF']);?>?clearsession=1">To list</a> |
    <a href="<?php print $_SERVER['PHP_SELF'];?>">Go back</a>
  </div>
  <div id="page">
<pre><?php
var_dump($_SESSION);

$val = array_combine(
    range('a', 'f'),
    array_map(function ($e) {
        return range(0, ord($e)-ord('A'));
    }, range('A', 'F'))
);
if (!isset($_GET['add'])) {
    $bag = new SessionBag($val);
} else {
    $bag = new SessionBag;
}
//$bag->add(
//    $val
//);
if (isset($_GET['add'])) {
    $bag->counter = isset($bag->counter) ? $bag->counter+1 : 0;
    $bag->more = ['datas' => 0, 'load2' => 2, 'load' => ['count' => null] ];
    $bag['more']['datas']=5;
    $bag['more']['load']['count']=22;
}
//var_dump($val);
var_dump($bag->toArray());
?>
</pre>
  </div>
</body>
</html>
