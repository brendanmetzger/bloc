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
  
  public function limit($index = 0, $limit = 100, array &$paginate = [])
  {
    $start = ($index * $limit);
    if ($this->count() > $start ) {
      $paginate['next'] = $index+1;
    }
    
    if ($index > 0 && $this->count() > $limit) {
      $paginate['previous'] = $index-1;
    }
    
    return new \LimitIterator($this, $start, $limit);
  }
}