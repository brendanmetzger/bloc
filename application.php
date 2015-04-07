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
 * Copyright (c) 2008-present, Brendan Metzger <brendan.metzger@gmail.com>
 */
class Application
{  
  public $benchmark;
  private $callbacks = [], $config = [];
  private static $log = [];

  static public function log() {
    foreach (func_get_args() as $arg) {
       self::$log[] = print_r($arg, true);
    }
    return self::$log;
  }

  static public function error($exception, $level) {
    print_r($exception->getMessage());
    if ($level > 1) {
      print_r($exception->getLine(), $exception->getFile());
    }
    if ($level > 2) {
      $called = $exception->getTrace()[0];
      echo "Problem in {$called['function']} - line {$called['line']} in {$called['file']}";
    }
  }
  
  static public function session($name, array $data = [])
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_name($name);
      session_start();
    } else {
      // if we are setting a session again, login has been attempted and we will regenerate id
      session_regenerate_id();
    }
    
    if (!empty($data)) {
      foreach ($data as $key => $value) {
        $_SESSION[$key] = $value;
      }
    }
    return $_SESSION;
  }
  
  public function __construct($config = [])
  {
    $this->benchmark = microtime(true);
    set_include_path(get_include_path() . PATH_SEPARATOR . PATH);
    spl_autoload_register([$this, 'autoload']);
    spl_autoload_register([$this, 'vendor']);
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
  
  public function vendor($class)
  {
    @include PATH . 'vendor' . DIRECTORY_SEPARATOR . str_replace(NS, DIRECTORY_SEPARATOR, $class) . '.php';
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