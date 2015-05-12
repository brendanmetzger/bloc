<?php
namespace bloc;

/**
  * Model Abstraction
  */
  
  abstract class Model implements \ArrayAccess {
    public function get_model()
    {
      return strtolower(array_pop(explode(NS, get_called_class())));
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