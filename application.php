<?php

namespace bloc;


# shorthand for namespace separator ( \ )
define('NS', '\\');

# shorthand for directory separator ( / )
define('DS',DIRECTORY_SEPARATOR);

# shorthand for path separator ( : )
define('PS', PATH_SEPARATOR);

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
 * @copyright Copyright (c) 2008, Brendan Metzger <brendan.metzger@gmail.com>
 */
 
class Application
{  
  const  WEBSITE = 1,
         COMMAND = 2,
         SERVICE = 3;

  private $_callbacks = [];
  
  public function __construct()
  {
    set_include_path(get_include_path() . PS . PATH);
    spl_autoload_register(array($this,'autoload'));
    spl_autoload_register(array($this,'failtoload'));
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

      console::error($e->getMessage());

    } catch (\RunTimeException $e) {

      console::error($e->getMessage());
      console::error($e->getLine());
      console::error($e->getFile());
      console::error($e->getTrace());

    } catch (\Exception $e) {

      console::error($e->getMessage());
      console::error($e->getLine());
      console::error($e->getFile());
      // called in:
      $trace = $e->getTrace();
      $called = $trace[0];
      console::error("problem in {$called['function']} - line {$called['line']} in {$called['file']}");

    }
  }

  private function autoload($class)
  { 
    @include str_replace(NS, DS, $class) . '.php';
  }

  public function failtoload($class)
  {
    if (! file_exists(PATH . str_replace(NS, DS, $class) . '.php')) {
      throw new \LogicException("What the hell is this _{$class}_ file you are referring to?", 1);  
    }
    
  }
}

