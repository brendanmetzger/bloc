<?php
namespace bloc;

/**
  * Model Abstraction
  */
  
  abstract class Model implements \ArrayAccess {
    const NAME = null;
    
    public function name()
    {
      return static::NAME;
    }
    
    public function offsetExists($offset)
    {
      return property_exists($this, $offset);
    }
  
    public function offsetGet($offset)
    {
      return $this->{$offset};
    }
  
    public function offSetSet($offset, $value)
    {
      return $this->{$offset} = $value;
    }
  
    public function offsetUnset($offset)
    {
      return null;
    }
    
  }