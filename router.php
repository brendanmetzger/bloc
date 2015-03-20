<?php
namespace bloc;

/**
 * A router simply loads classes from a particular directory according to the 
 * tried and true controller.action method. The directory to look in is supplied via 
 * the `namespace` argument. 
 * Understand reflection: http://en.wikipedia.org/wiki/Reflection_(computer_programming)
 * Get familiar with PHP's take on reflection: http://php.net/reflection
 */

class Router
{
  public $request;
  private $namespace;
  
  public function __construct($namespace, $request)
  {
    $this->namespace = NS . $namespace . NS;
    $this->request   = $request;
  }
  
  # Returns a http://php.net/reflectionmethod
  private function rigAction(\ReflectionClass $control, $action)
  {    
    if ( $control->hasMethod($action) ) {
      return $control->getMethod($action);
    } else {
      throw new \RuntimeException(sprintf('Could not find %s', $action));
    }
  }

  public function delegate($controller, $action)
  {
    $controller = $this->namespace . ($this->request->controller ?: $controller);

    $control  = new \ReflectionClass($controller);
    $instance = $control->newInstance();

    try {
      $action  = $this->rigAction($control, $this->request->action ?: $action);

      if ( $action->isProtected() ) {
        session_start();
        $action->setAccessible(array_key_exists('user', $_SESSION));        
      }
      
      $action->invokeArgs($instance, $this->request->params);
      
    } catch (\ReflectionException $e) {
      $this->rigAction($control, 'login')->invoke($instance, $_SERVER['REDIRECT_URL'], $_POST);
    }
  }
}