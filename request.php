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
  
  public function post($key)
  {
    return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
    // filter_var($a, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
  }
}