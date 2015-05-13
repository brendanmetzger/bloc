<?php
namespace bloc;

/**
 * Registry trait. define data storage and a few magic methods.
 *
 */

trait registry {
  
  protected $registry;
  
  /*
    TODO Consider a recursive function here instead of a loop to ensure ArrayAccess
  */
  public static function getNamespace($path, \ArrayAccess $cursor) {
    $namespaces  = preg_split('/\:+/i',trim($path));
    foreach ($namespaces as $namespace) {
      if (!method_exists($cursor, 'offsetGet')) {
        throw new \Exception("Implement Array Access - cannot reach {$namespace}", 1);

      }
      $cursor = $cursor->offsetGet($namespace);
    }
    return $cursor;
  }
  
  static public function index()
  {
    static $index = 0;
    return $index++;
  }
  
  public function __set($key, $value)
  {
    return $this->setProperty($key, $value);
  }
  
  public function __get($key)
  {
    return $this->getProperty($key);
  }
  
  public function getProperty($key)
  {
    return $this->registry[$key];
  }
  
  public function &setProperty($key, $value)
  {
    $this->registry[$key] = $value;
    return $this->registry[$key];
  }
  
  public function getRegistry()
  {
    return new \bloc\types\dictionary($this->registry);
  }
}