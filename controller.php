<?php
namespace bloc;


/**
 * Controller
 */

class Controller
{
  use \bloc\registry;
  
  protected $partials = [];
  
  public function setPartial($key, $value)
  {
    $this->partials[$key] = $value;
  }
  
  public function getPartials()
  {
    return $this->partials;
  }
  
  public function error($code, $message)
  {
    /*
      TODO headers should be passed in a queue somewhere else.. before sending output.
    */
    header("HTTP/1.0 404 Not Found");
    printf('%d: %s', $code, $message);
  }
  
  public function GETlogin($redirect)
  {
    $this->error(501, 'Nothin Doin');
  }
  
  public function GETlogout()
  {
    session_destroy();
    header("Location: /");
  }
  
  public function __invoke()
  {
    return $this->getRegistry();
  }
}