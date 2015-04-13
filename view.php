<?php
namespace bloc;

/**
 * A view represents the object that will mash any XML based data together, recursively.
 */
class View
{
  public $dom, $xpath, $parser, $clone, $context = null;
  
  private static $renderer = [
    'before'    => [],
    'after'     => [],
    'preflight' => [],
  ];
	  
  public function __construct($document_element)
  {
    $this->dom = new DOM\Document;
        
    if (is_string($document_element)) {
      $this->dom->load(PATH.$document_element, LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOXMLDECL|LIBXML_NOENT);
    } else if ($document_element instanceof \DOMNode) {
      $this->dom->appendChild($this->dom->importNode($document_element, true));
    }
        
    $this->xpath  = new \DomXpath($this->dom);
    $this->parser = new view\parser($this);
    
    foreach ($this->parser->queryCommentNodes('insert') as $stub) {
      $path = trim(substr(trim($stub->nodeValue), 6));
      $element = $this->dom->importNode((new view($path))->dom->documentElement, true);
      $stub->parentNode->replaceChild($element, $stub);
    }
  }
  
  
  public function __set($key, $view)
  {
    $command = "replace {$key}";
    if (! $view instanceof view) {
      $view = new view($view);
    }
    
    foreach ($this->parser->queryCommentNodes($command) as $stub) {
      $adjacency = trim(substr(trim($stub->nodeValue),strlen($command)));
      $element = $this->dom->importNode($view->dom->documentElement, true);
      if (empty($adjacency)) {
        $stub->parentNode->replaceChild($element, $stub);
      } else {
        $stub->parentNode->replaceChild($element, $stub->{$adjacency});
        $stub->parentNode->removeChild($stub);
      }
    }
  }
  
  static public function addRenderer($when, callable $callback)
  {
    self::$renderer[$when][] = $callback;
  }
  
  public function getRenderers($key)
  {
    return self::$renderer[$key];
  }
  
	public function render($data = [])
	{    
    foreach ($this->getRenderers('before') as $callback) {
      call_user_func($callback, $this);
    }
    
    $this->parser->parse($data);
    
    foreach ($this->getRenderers('after') as $callback) {
      call_user_func($callback, $this);
    }
    

    return $this;
	}
  
  public function __toString()
  {
    foreach ($this->getRenderers('preflight') as $callback) {
      call_user_func($callback, $this);
    }
    return $this->dom->saveXML($this->context);
  }
}