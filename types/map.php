<?php

namespace bloc\types;

/**
* Map
* Motivation is to not modify variables of an array to suit a template.
* Instead, pass a callback that returns the modified version. The gain is
* twofold in that you don't have to loop through something twice, nor do
* you experience any performance hit of changing the value of a looped array
*/

class Map extends Dictionary
{
  private $callback;
  public function __construct($array, callable $callback)
  {
    parent::__construct($array);
    $this->callback = $callback;
  }
    
  public function current()
  {
    return call_user_func($this->callback, parent::current());
  }
  
}
