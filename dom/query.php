<?php
namespace bloc\dom;

/**
 * DOM Element Extension
*/
class Query
{
  private $dom,
          $xpath;

  public  $expression = '/';

  public function __construct(\DOMDocument $dom)
  {
    $this->dom = $dom;
  }

  public function path($expression)
  {
    $this->expression .= $expression;
    return $this;
  }

  public function find($expression = '', $context = null)
  {
    if ($this->xpath === null) {
      $this->xpath = new \DOMXpath($this->dom);
    }

    // $this->path($expression);

    return new Iterator($this->xpath->query($this->expression . $expression, $context ?: $this->dom->documentElement));
  }

  public function pick($expression = '', $offset = 0)
  {
    return $this->find($expression)->pick($offset);
  }

}
