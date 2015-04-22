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
      $match = htmlentities(\bloc\registry::getNamespace($match, $this), ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    }
    return $matches;
  } 
}
