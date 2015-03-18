<?php
namespace bloc;

/**
 * Console
 */

class Console
{
  
  static public function dump($data) {
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
  }

  static public function error($exception, $level) {
    self::dump($exception->getMessage());
    
    if ($level > 1) {
      self::dump($exception->getLine());
      self::dump($exception->getFile());
    }
    
    if ($level > 2) {
      $trace = $e->getTrace();
      $called = $trace[0];
      self::dump("problem in {$called['function']} - line {$called['line']} in {$called['file']}");
    }
  }
}