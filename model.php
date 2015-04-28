<?php
namespace bloc;

/**
  * Model Abstraction
  */
  
  abstract class Model implements \ArrayAccess {
    
    public function offsetExists($offset)
    {
      return true;
    }
  
    public function offsetGet($offset)
    {
      return $this->{$offset};
    }
  
    public function offSetSet($offset, $value)
    {
      return null;
    }
  
    public function offsetUnset($offset)
    {
      return null;
    }
    
  }