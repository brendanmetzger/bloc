<?php
namespace bloc;
/**
 * Console
 */

class Console
{
  
  static public function dump($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
  }

  static public function error($data) {
    self::dump($data);
  }
}