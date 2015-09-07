<?php
namespace bloc;

/**
  * Model Abstraction
  */
  
  abstract class Model implements \ArrayAccess, \IteratorAggregate {
    protected $slug;
    
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
    
    public function getIterator() {
      return new \ArrayIterator($this->slug);
    }
    
    public function __toString()
    {
      return '';
    }
    
  }