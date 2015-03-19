<?php
namespace bloc;

/**
 * Router
 */

class Router
{
  const CTRL = 'controllers';

  public $request;

  function __construct($request)
  {
    $this->request = $request;
  }

  /**
   *Define controller
   * @param string $controller
   * @return ReflectionClass
   */
  private function GETcontroller($control)
  {
    return new \ReflectionClass( NS . self::CTRL . NS . $control);
  }

  /**
   * Define Action
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
    $control = $this->GETcontroller($this->request->controller ?: $controller);
    $action  = $this->GETAction($control, $this->request->action ?: $action);

    if ( $action->isPublic() ) {
      
      $instance = $control->newInstance();
      
      $action->invokeArgs($instance, $this->request->params);

    } else if ( $action->isProtected() ) {
      
      // instantiate this as by switching visibility of action method from
      // protected to public.
      
    } else {
      throw new \BadMethodCallException('The area you are attempting to access is restricted. This has been logged to the system.');
    }
  }
}