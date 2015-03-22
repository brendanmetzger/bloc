<?php
namespace bloc\model;


/**
 * Dictionary
 */

class Dictionary extends \ArrayIterator
{  
  private $table = [];
  private $callback;
  function __construct($data = [])
  {
    parent::__construct($data);

    foreach ($data as $key => $value) {
      $this->{$key} = $value;
    }
  }
  
  public function __set($key, $value)
  {
    $this->table[$key] = is_array($value) ? new Dictionary($value) : $value;
  }
  
  public function __get($key)
  {
    return $this->offsetExists($key) ? $this->table[$key] : [];
  }
  
  public function push(array $item)
  {
    $this->table[] = $item;
  }
  
  public function current()
  {
    $item = parent::current();
    if (is_array($item)) {
      ksort($item);
    }
    return $item;
  }
    
  public function intersection($keys)
  {
    return array_intersect_key($this->table, $keys);
  }
  
  # Method(s) I'd maybe like to see:
  // flatten(int $level)
  // serialize
}