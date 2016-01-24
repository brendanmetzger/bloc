<?php
namespace bloc;

# shorthand for namespace separator ( \ )
define('NS', '\\');

# the ONLY path constant, thus, aptly named.
define('PATH', realpath('../') . DIRECTORY_SEPARATOR);

require PATH . 'bloc/types/error.php';
require PATH . 'bloc/router.php';
require PATH . 'bloc/registry.php';
require PATH . 'bloc/request.php';
require PATH . 'bloc/response.php';
require PATH . 'bloc/controller.php';

/**
 * Maybe
 * Functor that sets a value of an associative array via
 * callback function if that value does not exist. If it does, value returned.
 */
class Maybe
{
  private $memo, $key = false;
  public function __construct(array $memo, $key = null)
  {
    $this->memo = $key ? $memo[$key] : $memo;
  }

  public function __invoke($key)
  {
    $this->key = $key;
    return array_key_exists($key, $this->memo) ? new self($this->memo, $key) : $this;
  }

  public function get(callable $callback)
  {
    return $this->key ? $this->memo[$this->key] = $callback($this->key) : $this->memo;
  }
}

/**
 * bloc
 * Copyright (c) 2008-present, Brendan Metzger <brendan.metzger@gmail.com>
 */
class Application
{
  public $benchmark;
  private $callbacks = [], $config = [], $log = [], $exchanges = ['request' => null, 'response' => 'null'];

  public function session($name, array $data = [])
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_name($name);
      session_start();
      $_SESSION['last-active'] = time();
    } else {
      session_regenerate_id();
    }
    foreach ($data as $key => $value) {
      $_SESSION[$key] = $value;
    }

    return new Maybe($_SESSION);
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

  public function getExchange($key)
  {
    return $this->exchanges[$key];
  }

  public function setExchanges(Request $request, Response $response)
  {
    $this->exchanges['request']  = $request;
    $this->exchanges['response'] = $response;
  }

  public function log() {
    foreach (func_get_args() as $arg) {
       $this->log[] = $arg;
    }
    return $this->log;
  }

  public function prepare($env, callable $callback)
  {
    $this->callbacks[$env] = $callback;
  }

  public function execute($env, $param = null)
  {
    try {
      return call_user_func($this->callbacks[$env], $this, $param);
    } catch (\Exception $e) {
      echo $e->getMessage() . ': Line ' . $e->getLine() . ' of ' . $e->getFile();
    }
  }

  private function autoload($class)
  {
    @include str_replace(NS, DIRECTORY_SEPARATOR, strtolower($class)) . '.php';
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
      throw new \LogicException("Could not load '{$class}' file.", 1);
    }
  }
}
