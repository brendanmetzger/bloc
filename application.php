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
  private $callbacks = [], $config = [], $log = [];
  
  public function session($name, array $data = [])
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
  
  static public function instance($config = [])
  {
    static $instance = null;
    
    if ($instance === null) {
      $instance = new static($config);
    }

    return $instance;
  }
  
  private function __construct($config)
  {
    $this->benchmark = microtime(true);
    set_include_path(get_include_path() . PATH_SEPARATOR . PATH);
    spl_autoload_register([$this, 'autoload']);
    spl_autoload_register([$this, 'vendor']);
    spl_autoload_register([$this, 'failtoload']);
  }
  
  public function log() {
    foreach (func_get_args() as $arg) {
       $this->log[] = $arg;
    }
    return $this->log;
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
    } catch (\Exception $e) {
      $this->log($e);
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