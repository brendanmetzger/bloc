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
  
  public function GETerror($code, $message)
  {
    Application::instance()->getExchange('response')->addHeader("HTTP/1.0 404 Not Found");
    return sprintf('%d: %s', $code, $message);
  }
  
  public function POSTerror($value='')
  {
    Application::instance()->getExchange('response')->addHeader("HTTP/1.0 404 Not Found");
    return sprintf('%d: %s', $code, $message);
  }
  
  
  public function CLIerror($code, $message)
  {
    return sprintf('%d: %s', $code, $message);
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