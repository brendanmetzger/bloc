<?php
namespace bloc\model;


/**
 * Dictionary
 */

class Dictionary extends \ArrayIterator
{  
  function __construct($data = [])
  {
    parent::__construct($data);
  }
  
  public function __set($key, $value)
  {
    $this->storage[$key] = $value;
  }
  
  public function __get($key)
  {
    if (!$this->hasKey($key)) {
      return [];
    }
    return is_array($this[$key]) ? new Dictionary($this[$key]) : $this[$key];
  }
  
  public function push(array $item)
  {
    $this->storage[] = $item;
  }
  
  public function current()
  {
    $item = parent::current();

    if (is_array($item)) {

      ksort($item);
    }
    return $item;
  }
  
  public function hasKey($key)
  {
    return array_key_exists($key, $this);
  }
    
  public function intersection($keys)
  {
    return array_intersect_key((array)$this, $keys);
  }
  
  # Method(s) I'd maybe like to see:
  // flatten(int $level)
  // serialize
}