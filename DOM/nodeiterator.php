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
      $this->position = $this->direction > 0 ? 0 : $this->nodelist->length - 1;
    }
    
    
    public function valid()
    {
      return $this->direction > 0 ? $this->nodelist->length > $this->position : $this->position >= 0;
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
  
    
    public function limit($index = 0, $limit = 100, array &$paginate = [])
    {
      $index = $index - 1;
      $start = ($index * $limit);
      $total = $this->nodelist->length;
      $paginate['total'] = ceil($total/$limit) - 1;
      
      if ($paginate['total'] > $index) {
        $paginate['next'] = $index + 1;
      }
    
      if ($index > 1 && $total > $limit) {
        $paginate['previous'] = $index - 1;
      }
      
      $paginate['index'] = $index + 1;
      $paginate['total'] = ceil($total/$limit);
    
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