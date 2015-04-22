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
    } else {
      print_r($data);
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
      throw new \RunTimeException("{$offset} is unavailable.", 100); 
    }
    
    $data = parent::offsetGet($offset);
    return is_array($data) ? new Dictionary($data) : $data;
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