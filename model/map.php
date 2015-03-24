<?php

namespace bloc\model;

/**
* Map
*/

class Map extends Dictionary
{
  private $callback;
  public function __construct($array, $callback)
  {
    parent::__construct($array);
    $this->callback = $callback;
  }
  
  public function current()
  {
    $item = call_user_func($this->callback, parent::current());
    ksort($item);
    return $item;
  }
}
