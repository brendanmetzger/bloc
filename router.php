<?php
namespace bloc;

/**
 * A router simply loads classes from a particular directory according to the 
 * tried and true controller.action method. The directory to look in is supplied via 
 * the `namespace` argument. The Request .
 */

class Router
{
  public $request;
  private $namespace;
  
  function __construct($namespace, $request)
  {
    $this->namespace = NS . $namespace . NS;
    $this->request   = $request;
  }

  /**
   * @param string $controller
   * @return ReflectionClass
   */
  private function GETcontroller($control)
  {
    return new \ReflectionClass( $this->namespace . $control);
  }

  /**
   * @param ReflectionClass $controller
   * @param string $action 
   * @return ReflectionMethod
   */
  private function GETaction(\ReflectionClass $control, $action)
  {    
    if ( $control->hasMethod($action) ) {
      return $control->getMethod($action);
    } else {
      throw new \RuntimeException(sprintf('could not find %s', $action));
    }
  }

  public function delegate($controller, $action)
  {
    $control  = $this->GETcontroller($this->request->controller ?: $controller);
    $instance = $control->newInstance();

    try {
      $action  = $this->GETAction($control, $this->request->action ?: $action);
      // here would be where we dump in some session variable relating to login
      if ( $action->isProtected() ) {
        $action->setAccessible(false);        
      }
      
      $action->invokeArgs($instance, $this->request->params);
      
    } catch (\ReflectionException $e) {
      $this->GETAction($control, 'login')->invoke($instance);
    }
  }
}