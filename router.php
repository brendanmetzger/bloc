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
  
  
  static public function redirect($location_url, $code = 302)
  {
    $location = "Location: http://{$_SERVER['HTTP_HOST']}{$location_url}";
    header($location, false, $code);
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
    $this->request->action = $action;
    $method = $this->request->type . $action;
    if ( $control->hasMethod($method) ) {
      return $control->getMethod($method);
    } else {
      throw new \RuntimeException(sprintf("Unable to %s '%s'", strtolower($this->request->type), $action), 404);
    }
  }
  
  public function delegate($default_controller, $default_action)
  {
    $this->request->controller = ($this->request->controller ?: $default_controller);
    
    try {
      $controller = new \ReflectionClass($this->namespace . $this->request->controller);
      $action     = $this->rigAction($controller, $this->request->action ?: $default_action);
      $instance   = $controller->newInstance($this->request);
      
      if ( $action->isProtected() ) {
        $action->setAccessible($instance->authenticated);        
      }
      $params = $this->request->params;
      
      if ($this->request->type === "POST") {
        array_unshift($params, $this->request);
      }
      
      return $action->invokeArgs($instance, $params);
      
    } catch (\ReflectionException $e) {
      return $this->rigAction($controller, 'login')->invoke($instance, $this->request->redirect);
    } catch (\RunTimeException $e) {
      $controller = new \ReflectionClass($this->namespace . $default_controller);
      $instance   =  $controller->newInstance($this->request);
      return $this->rigAction($controller, 'error')->invoke($instance, $e->getMessage(), $e->getCode());
    }
  }
}