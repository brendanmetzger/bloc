<?php

namespace bloc;

# shorthand for namespace separator ( \ )
define('NS', '\\');

# shorthand for directory separator ( / )
define('DS',DIRECTORY_SEPARATOR);

# the ONLY path constant, thus, aptly named.
define('PATH', realpath('../') . DS);


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
      call_user_func($this->_callbacks[$env], $this);
    } catch (\LogicException $e) {
      console::error($e, 1);
    } catch (\RunTimeException $e) {
      console::error($e, 2);
    } catch (\Exception $e) {
      console::error($e, 3);
    }
  }

  private function autoload($class)
  { 
    @include str_replace(NS, DS, $class) . '.php';
  }

  public function failtoload($class)
  {
    if (! file_exists(PATH . str_replace(NS, DS, $class) . '.php')) {
      throw new \LogicException("What the hell is this '{$class}' file you are referring to?", 1);  
    }
    
  }
}