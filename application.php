<?php

namespace bloc;

# shorthand for namespace separator ( \ )
define('NS', '\\');

# the ONLY path constant, thus, aptly named.
define('PATH', realpath('../') . DIRECTORY_SEPARATOR);

require PATH . 'bloc/router.php';
require PATH . 'bloc/request.php';


/**
 * bloc
 *
 * LICENSE
 *
 * This source file and all files under the bloc namespace are subject 
 * to the license that is bundled with this package in the file LICENSE
 *
 * Application Whizbang
 *
 * @category bloc
 * @copyright Copyright (c) 2008-present, Brendan Metzger <brendan.metzger@gmail.com>
 */
 
class Application
{  
  private $_callbacks = [];
  
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
  
  public function __construct()
  {
    set_include_path(get_include_path() . PATH_SEPARATOR . PATH);
    spl_autoload_register([$this, 'autoload']);
    spl_autoload_register([$this, 'failtoload']);
  }

  public function queue($env, $callback)
  {
    $this->_callbacks[$env] = $callback;
  }

  public function run($env)
  {
    try {
      return call_user_func($this->_callbacks[$env], $this);      
    } catch (\LogicException $e) {
      application::error($e, 1);
    } catch (\RunTimeException $e) {
      application::error($e, 2);
    } catch (\Exception $e) {
      application::error($e, 3);
    }
  }

  private function autoload($class)
  { 
    @include str_replace(NS, DIRECTORY_SEPARATOR, $class) . '.php';
  }

  public function failtoload($class)
  {
    if (! file_exists(PATH . str_replace(NS, DIRECTORY_SEPARATOR, $class) . '.php')) {
      throw new \LogicException("What the hell is this '{$class}' file you are referring to?", 1);  
    }
    
  }
}