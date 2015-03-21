<?php
namespace bloc;

/**
* callbacker
*/

class DictionaryIterator extends \ArrayIterator
{
  private $callback;
  public function __construct($array, $callback)
  {

    parent::__construct($array);
    $this->callback = $callback;
  }
  
  public function current()
  {
    $item = parent::current();
    if ($this->callback) {
      $item = call_user_func($this->callback, $item);
    }
    ksort($item);
    return $item;
  }
}

/**
 * Dictionary
 */

class Dictionary implements \ArrayAccess, \IteratorAggregate
{  
  private $table = [];
  private $callback;
  function __construct($data, $callback = false)
  {
    /*
      TODO  I'd like to eliminate the need for formatting data before displaying it, as it unnecessarily calls a loop twice. The
    performance gain on this alone should mitigate the complication of necessitating a dictionary object as opposed to some default
    iterable data structure.
    */

    $this->callback = $callback;

    foreach ($data as $key => $value) {
      $this->{$key} = $value;
    }
  }
  
  public function __set($key, $value)
  {
    $this->table[$key] = is_array($value) ? new Dictionary($value) : $value;
  }
  
  public function __get($key)
  {
    return $this->offsetExists($key) ? $this->table[$key] : [];
  }
  
  public function push(array $item)
  {
    $this->table[] = $item;
  }
  
  public function getIterator()
  {
    return new DictionaryIterator($this->table, $this->callback);
  }
  
  public function offsetSet($key, $value) {
    $this->table[$key] = $value;
  }

  public function offsetExists($key) {
    return array_key_exists($key, $this->table);
  }

  public function offsetUnset($key) {
    unset($this->table[$key]);
  }

  public function offsetGet($key) {
    return $this->table[$key] ?: null;
  }
    
  public function intersection($keys)
  {
    return array_intersect_key($this->table, $keys);
  }
  
  # Method(s) I'd maybe like to see:
  // flatten
  // serialize
}