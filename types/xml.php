<?php
namespace bloc\types;

class XML extends \SimpleXMLElement implements \ArrayAccess
{
  static public function MAP($iterator, $callback)
  {
    foreach ($iterator as $item) {
      \bloc\application::instance()->log($item);
    }
    
  }
  
  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $match = htmlentities(\bloc\registry::getNamespace($match, $this), ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    }
    return $matches;
  }
  
  public function offsetExists($offset)
  {
    return property_exists($offset, $this);
  }
  
  public function offsetGet($offset)
  {
    return $this->{$offset};
  }
  
  public function offsetSet($property, $value)
  {
    $this->{$property} = $value;
  }
  
  public function offsetUnset($offset)
  {
    unset($this->{$offset});
  }
  
  public function current()
  {
    \bloc\application::instance()->log('and we here');
    return parent::current();
  }
  
}