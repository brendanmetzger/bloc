<?php
namespace bloc\types;

class XML extends \SimpleXMLIterator implements \ArrayAccess
{
  private static $callback = null;
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
  
  public function map(callable $callback)
  {
    self::$callback = $callback;
  }
  
  public function findOne($path, $offset = 0)
  {
    return $this->find($path)[$offset];
  }
  
  public function find($path)
  {
    return $this->xpath($path);
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
    if (self::$callback) {
      return call_user_func(self::$callback, parent::current());
    }
    return parent::current();
  }
  
}