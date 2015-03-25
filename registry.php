<?php
namespace bloc;

/**
 * Registry trait. define data storage and a few magic methods.
 *
 */

trait registry {
  
  protected $registry;
  
  public function __set($key, $value)
  {
    return $this->registry[$key] = $value;
  }
  
  public function __get($key)
  {
    return $this->registry[$key];
  }
}