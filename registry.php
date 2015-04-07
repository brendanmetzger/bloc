<?php
namespace bloc;

/**
 * Registry trait. define data storage and a few magic methods.
 *
 */

trait registry {
  
  protected $registry;
  
  public static function getNamespace($path, \ArrayAccess $cursor) {
    $namespaces  = preg_split('/\:+/i',trim($path));
    
    foreach ($namespaces as $namespace) {
      if (substr($namespace, 0, 1) == '@') {
        $cursor = $cursor[substr($namespace, 1)];
        continue;
      } 
      
      
      if (!array_key_exists($namespace, $cursor)) {
        throw new \RunTimeException("{$namespace} is unavailable.", 100);
      }
      

      
      $cursor = is_array($cursor) ? $cursor[$namespace] : $cursor->{$namespace};
      
    }
    return $cursor;
  }
  
  public function __set($key, $value)
  {
    return $this->registry[$key] = $value;
  }
  
  public function __get($key)
  {
    return $this->getProperty($key);
  }
  
  public function getProperty($key)
  {
    return $this->registry[$key];
  }
  
  public function getRegistry()
  {
    return new \bloc\types\dictionary($this->registry);
  }
}