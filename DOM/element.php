<?php
namespace bloc\DOM;

/**
 * DOM Element Extension
*/
class Element extends \DOMElement
{
  public function insert(\DOMNode $parent)
  {
    $parent->appendChild($this);
    return $this;
  }
  
  public function grab(\DOMNode $child, $where = null)
  {
    $this->appendChild($this);
    return $this;
  }
}