<?php
namespace bloc\DOM;

/**
  * Nodelist Iterator 
  */
  
  class NodeIterator implements \Iterator, \ArrayAccess
  {
    use \bloc\types\Map;
    
    private $position  = 0,
            $direction = 1,
            $nodelist  = null;
            
    public function __construct(\DOMNodelist $nodelist)
    {
      $this->nodelist = $nodelist;
    }
    
    public function current()
    {
      $item = $this->nodelist->item($this->position);
      if ($this->callback) {
        return call_user_func($this->callback, $item);
      }
      
      return $item;
    }
    
    public function next()
    {
      return $this->position += $this->direction;
    }
    
    public function rewind()
    {
      $this->position = $this->direction > 0 ? 0 : $this->count() - 1;
    }
    
    
    public function valid()
    {
      return ($this->direction > 0) ? ($this->count() > $this->position) : ($this->position >= 0);
    }
    
    public function key()
    {
      return $this->position;
    }
    
    public function offsetExists($offset)
    {
      return true;
    }
  
    public function offsetGet($offset)
    {
      return $this->current()->offsetGet($offset);
    }
  
    public function offSetSet($offset, $value)
    {
      return null;
    }
  
    public function offsetUnset($offset)
    {
      return null;
    }
    
    public function reverse()
    {
      $this->direction *= -1;
      return $this;
    }
    
    public function count()
    {
      return $this->nodelist->length;
    }
    
    public function pick($offset = 0)
    {
      return $this->nodelist->item($offset);
    }
    
    public function sort(callable $callback)
    {
      $dict = new \bloc\types\Dictionary(iterator_to_array($this, false));
      $dict->uasort($callback);
      return $dict;
    }
    
    public function __toString()
    {
      $current = $this->current();
      return (string)$current;
    }
  }