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
    printf('%d: %s', $code, $message);
  }
  
  public function login($redirect_url, $post_data)
  {
    $this->error(501, 'Nothin Doin');
  }
  
  protected function logout()
  {
    session_destroy();
    header("Location: /");
  }
  
  public function __invoke()
  {
    return $this->getRegistry();
  }
}