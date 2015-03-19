<?php
namespace bloc;

/**
* Request
*/
class Request
{
  private $params = [];
  public function __construct($data)
  {
    \bloc\console::dump($data);
    $this->params = $data;
  }
  
  public function __get($key)
  {
    return $this->params[$key];
  }
}