<?php
namespace bloc;

/**
* Request
*/
class Request
{
  use \bloc\registry;

  private $params = [];
  public $type, $redirect, $format;

  static public $HTTP = true;
  static public $data;
  public function __construct($data, $parse = true)
  {
    $this->type     = self::$HTTP ? @$_SERVER['REQUEST_METHOD'] : 'CLI';
    $this->redirect = self::$HTTP ? @$_SERVER['REDIRECT_URL'] : false;
    $this->format   = $data['content-type'] ?: 'html';

    if ($parse) {
      $data['params'] = array_filter(explode('/', $data['params']), 'strlen');
    }

    self::$data = $data;
    $this->registry = $data;
  }

  public function post($key)
  {
    return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
    // filter_var($a, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
  }
}
