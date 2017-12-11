<?php

class mirko{
  public static $ciao = ['aaa' => 1000, 'bbb' => 2];
  //private static $ciaone = self::$ciao['aaa'];

  public static function ciaone(){
    return self::$ciao['aaa'];
  }
}

var_dump( mirko::ciaone() );