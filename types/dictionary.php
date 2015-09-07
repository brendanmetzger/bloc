<?php
namespace bloc\types;


/**
 * Dictionary
 */

class Dictionary extends \ArrayIterator
{  
  use Map;
  
  function __construct($data = [])
  {
    if (is_array($data) || is_object($data)) {
      parent::__construct($data);
    }
  }
  
  public function __set($key, $value)
  {
    $this->offsetSet($key, $value);
  }
  
  public function __get($key)
  {
    return $this->offsetGet($key);
  }
  

  
  public function offsetGet($offset)
  {
    if (!$this->offsetExists($offset)) {
      throw new \RunTimeException("{$offset} is unavailable.", 404); 
    }
    
    $data = parent::offsetGet($offset);
    return is_array($data) ? new Dictionary($data) : $data;
  }
  
  public function sort(callable $callback)
  {
    $this->uasort($callback);
    return $this;
  }
  
}