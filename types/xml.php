<?php
namespace bloc\types;

class XML extends \SimpleXMLIterator implements \ArrayAccess
{
  use Map;
  
  static public function load($file, $compressed = false)
  {
    static $instance = [];

    if (! array_key_exists($file, $instance)) {
      if ($compressed) {
        $instance[$file] = simplexml_load_string(gzdecode(file_get_contents(PATH.$file)), __CLASS__, LIBXML_COMPACT);;
      } else {
        $instance[$file] = simplexml_load_string(file_get_contents(PATH.$file.'.xml'), __CLASS__, LIBXML_COMPACT);;
      }
    }
    return $instance[$file];
  }
  
  public function findOne($path, $offset = 0)
  {
    $result = $this->find($path);
    
    return count($result) > 0 ? $result[$offset] : null;
  }
  
  public function find($path)
  {
    return new Dictionary($this->xpath($path));
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