<?php
namespace bloc\DOM;

/**
  * Nodelist Iterator 
  */
  
  class NodeIterator implements \Iterator, \ArrayAccess
  {
    use \bloc\types\Map;
    
    private $position = 0,
            $nodelist = null;
            
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
      return $this->position += 1;
    }
    
    public function rewind()
    {
      $this->position = 0;
    }
    
    public function valid()
    {
      return $this->nodelist->length > $this->position;
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
  
    
    public function limit($index = 0, $limit = 100, array &$paginate = [])
    {
      $start = ($index * $limit);
      if ($this->nodelist->length > $start ) {
        $paginate['next'] = $index+1;
      }
    
      if ($index > 0 && $this->nodelist->length > $limit) {
        $paginate['previous'] = $index-1;
      }
    
      return new \LimitIterator($this, $start, $limit);
    }
    
    public function pick($offset = 0)
    {
      return $this->nodelist->item($offset);
    }
    
    public function __toString()
    {
      $current = $this->current();
      return (string)$current;
    }
  }