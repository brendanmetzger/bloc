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
    $data['params'] = explode('/', ($data['params']));
    $this->params = $data;

  }
  
  public function __get($key)
  {
    return $this->params[$key];
  }
}