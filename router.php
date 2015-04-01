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
  
  static public function error(\Exception $e)
  {
    $control = new \ReflectionClass('\\bloc\controller');
    $instance = $control->newInstance();
    $action   = $control->getMethod('error');
    $action->invoke($instance, $e->getCode(), $e->getMessage());
  }
  
  static public function redirect($location_url, $code = 302)
  {
    // {http://{$_SERVER['HTTP_HOST']}
    header("Location: {$location_url}}", false, $code);
    exit();
  }
  
  
  public function __construct($namespace, \bloc\request $request = null)
  {
    $this->namespace = NS . $namespace . NS;
    $this->request   = $request ?: new \bloc\request(['controller' => null, 'action' => null]);
  }
  
  # Returns a http://php.net/reflectionmethod
  private function rigAction(\ReflectionClass $control, $action)
  {    
    if ( $control->hasMethod($action) ) {
      return $control->getMethod($action);
    } else {
      throw new \RuntimeException(sprintf('Could not find %s', $action), 404);
    }
  }

  public function delegate($controller, $action)
  {
    $controller = $this->namespace . ($this->request->controller ?: $controller);
    $control    = new \ReflectionClass($controller);
    
    try {
      $action  = $this->rigAction($control, $this->request->action ?: $action);
      $instance = $control->newInstance($this->request);
      
      if ( $action->isProtected() ) {
        $action->setAccessible($instance->authenticated);        
      }
      
      return $action->invokeArgs($instance, $this->request->params);
      
    } catch (\ReflectionException $e) {
      return $this->rigAction($control, 'login')->invoke($instance, $_SERVER['REDIRECT_URL'], $this->request);
    }
  }
}