<?php
namespace bloc;


/**
 * Controller
 */

class controller
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
  
  public function login($redirect_url, $post_data)
  {
    $this->error(501, 'Nothin Doin');
  }
  
  public function logout()
  {
    session_destroy();
    header("Location: /");
  }
  
  public function __invoke()
  {
    return $this->getRegistry();
  }
}