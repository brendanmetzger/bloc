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
  
  public function GETerror($message, $code = 404)
  {
    $codes = [
      400 => "400 Bad Request",
      404 => "404 Not Found",
      500 => "500 Internal Server Error",
      501 => "501 Not Implemented", 
    ];
    Application::instance()->getExchange('response')->addHeader("HTTP/1.0 {$codes[$code]}");
    return $message;
  }
  
  public function POSTerror($message, $code = 404)
  {
    Application::instance()->getExchange('response')->addHeader("HTTP/1.0 404 Not Found");
    return sprintf('%d: %s', $code, $message);
  }
  
  
  public function CLIerror($message, $code)
  {
    return sprintf('%d: %s', $code, $message);
  }
  
  
  public function GETlogin($redirect)
  {
    $this->error('Nothin Doin', 501);
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