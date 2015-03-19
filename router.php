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
   * Create Controller Object
   *
   * @param string $controller controller name called by user
   * @return ReflectionClass reflection instance of the controller object
   */
  private function GETcontroller($control)
  {
    return new \ReflectionClass( NS . self::CTRL . NS . $control);
  }

  /**
   * Create Action Object
   *
   * @param ReflectionClass $controller controller class
   * @param string $action action method called by user
   * @return ReflectionMethod relection instance of the action method
   * @see _createController
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