<?php
namespace bloc;

/**
* Request
*/
class Request
{
  use \bloc\registry;
  
  private $params = [];
  public function __construct($data)
  {
    $data['params'] = array_filter(explode('/', ($data['params'])));
    $this->registry = $data;
  }
}