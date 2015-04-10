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
    parent::__construct($data);
  }
  
  public function __set($key, $value)
  {
    $this[$key] = $value;
  }
  
  public function __get($key)
  {
    if (!$this->hasKey($key)) {
      return [];
    }
    return is_array($this[$key]) ? new Dictionary($this[$key]) : $this[$key];
  }
  
  public function hasKey($key)
  {
    return array_key_exists($key, $this);
  }
}