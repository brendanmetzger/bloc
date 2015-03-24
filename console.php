<?php
namespace bloc;

/**
 * Console
 */

class Console
{
  
  static public function dump() {
    echo "<pre>--------------\n";
    foreach (func_get_args() as $arg) {
       echo "\n --- ";
       print_r($arg);
       echo " --- \n";
    }
    echo "\n--------------</pre>";
  }

  static public function error($exception, $level) {
    self::dump($exception->getMessage());
    
    if ($level > 1) {
      self::dump($exception->getLine());
      self::dump($exception->getFile());
    }
    
    if ($level > 2) {
      $trace = $exception->getTrace();
      $called = $trace[0];
      self::dump("problem in {$called['function']} - line {$called['line']} in {$called['file']}");
    }
  }
}