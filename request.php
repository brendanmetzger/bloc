<?php
namespace bloc;

/**
* Request
*/
class Request
{
  use \bloc\registry;
  
  private $params = [];
  public $type, $redirect;
  public function __construct($data, $parse = true)
  {
    $this->type     = $_SERVER['REQUEST_METHOD'];
    $this->redirect = $_SERVER['REDIRECT_URL'];
    
    if ($parse) {
      $data['params'] = array_filter(explode('/', ($data['params'])));
    }
    $this->registry = $data;
  }
  
  public function post($key)
  {
    return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
    // filter_var($a, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
  }
}