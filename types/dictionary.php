<?php
namespace bloc\types;


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

  
  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $match = \bloc\registry::getNamespace($match, $this);
    }
    return $matches;
  }

  # Method(s) I'd maybe like to see:
  // flatten(int $level)
  // serialize
}