<?php

namespace bloc\types;

/**
* Map Trait
* Motivation is to not modify variables of an array to suit a template.
*/

trait Map
{
  private static $callbacks = [];
  private $obj_id = null;
  public function map(callable $callback)
  {
    $this->obj_id = spl_object_hash($this);
    $this::$callbacks[(string)$this->obj_id] = $callback;
    return $this;
  }
    
  public function current()
  {
    if ($this->obj_id) {
      return call_user_func(self::$callbacks[(string)$this->obj_id], parent::current());
    }
    return parent::current();
  }
  
  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $match = htmlentities(\bloc\registry::getNamespace($match, $this), ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    }
    return $matches;
  } 
}
