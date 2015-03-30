<?php

namespace bloc;

# shorthand for namespace separator ( \ )
define('NS', '\\');

# the ONLY path constant, thus, aptly named.
define('PATH', realpath('../') . DIRECTORY_SEPARATOR);

require PATH . 'bloc/router.php';
require PATH . 'bloc/registry.php';
require PATH . 'bloc/request.php';
require PATH . 'bloc/controller.php';

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
  public $benchmark;
  private $callbacks = [];

  static public function dump() {
    echo "\n<pre>--------------\n";
    foreach (func_get_args() as $arg) {
       printf("\n---\n%s\n---\n", print_r($arg, true));
    }
    echo "\n--------------\n</pre>";
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
    $this->benchmark = microtime(true);
    set_include_path(get_include_path() . PATH_SEPARATOR . PATH);
    spl_autoload_register([$this, 'autoload']);
    spl_autoload_register([$this, 'failtoload']);
  }

  public function prepare($env, $callback)
  {
    $this->callbacks[$env] = $callback;
  }

  public function execute($env)
  {
    try {
      return call_user_func($this->callbacks[$env], $this);      
    } catch (\RunTimeException $e) {
      Router::error($e);
    } catch (\LogicException $e) {
      application::error($e, 1);
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
      $parts = explode(NS, $class);
      if ($parts[0] == 'controllers') {
        throw new \RunTimeException("{$parts[1]} is not there.", 404);  
      }
      throw new \LogicException("What the hell is this '{$class}' file you are referring to?", 1);  
    }
    
  }
}