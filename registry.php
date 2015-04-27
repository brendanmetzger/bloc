<?php
namespace bloc;

/**
 * Registry trait. define data storage and a few magic methods.
 *
 */

trait registry {
  
  protected $registry;
  
  /*
    TODO Consider a recursive function here instead of a loop
  */
  public static function getNamespace($path, \ArrayAccess $cursor) {
    $namespaces  = preg_split('/\:+/i',trim($path));
    
    foreach ($namespaces as $namespace) {
      if (!method_exists($cursor, 'offsetGet')) {
        /*
          TODO this should be an instance of something in the bloc namespace
        */
        if ($cursor instanceof \models\model) {
          return $cursor->{$namespace};
        }
      }
      $cursor = $cursor->offsetGet($namespace);
    }
    return $cursor;
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