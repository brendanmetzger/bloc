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
      
  public function offsetExists($offset, $attribute = false)
  {
    if ($attribute) {
      return ! is_null($this[$offset]);
    } else {
      return ! is_null($this->{$offset});
    }
    
  }
  
  public function offsetGet($offset)
  {

    if (substr($offset, 0, 1) == '@') {
      $attribute = true;
      $offset = substr($offset, 1);
    } else {
      $attribute = false;
    } 
    
    if (! $this->offsetExists($offset, $attribute)) {
      throw new \RunTimeException("{$offset} is unavailable.", 100);
    }
    
    return $attribute ? $this[$offset] : $this->{$offset};
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