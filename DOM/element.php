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
}