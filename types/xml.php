<?php
namespace bloc\types;

class XML extends \SimpleXMLIterator implements \ArrayAccess
{
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