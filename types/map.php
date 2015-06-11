<?php

namespace bloc\types;

/**
* Map Trait
* Motivation is to not modify variables of an array to suit a template.
*/

trait Map
{
  protected $callback = null;

  public function map(callable $callback)
  {
    
    $this->callback = $callback;
    return $this;
  }
    
  public function current()
  {
    $object = parent::current();
    
    if ($this->callback) {
      return call_user_func($this->callback, $object);
    }
    return $object;
  }
  
  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $data = \bloc\registry::getNamespace($match, $this);
      $match = htmlentities($data, ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    }
    return $matches;
  }
  
  public function limit($index = 0, $limit = 100, array &$paginate = [])
  {
    $index = $index - 1;
    $start = ($index * $limit);
    $total = $this->count();
    $paginate['total'] = ceil($total/$limit) - 1;
    
    if ($paginate['total'] > $index) {
      $paginate['next'] = $index + 2;
    }
    if ($index > 0 && $total > $limit) {
      $paginate['previous'] = $index;
    }
    
    $paginate['index'] = $index + 1;
    $paginate['total'] = ceil($total/$limit);
  
    return new \LimitIterator($this, $start, $limit);
  }
}
