<?php
namespace bloc\types;

class XML extends \SimpleXMLElement implements \ArrayAccess
{
  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $match = \bloc\registry::getNamespace($match, $this);
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
  
}