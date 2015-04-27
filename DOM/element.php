<?php
namespace bloc\DOM;

/**
 * DOM Element Extension
*/
class Element extends \DOMElement implements \ArrayAccess
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
  
  public function getFirst($nodeName)
  {
    $result = $this->getElementsByTagName($nodeName);
    return $result->length > 0 ? $result->item(0) : null;
  }
  
  
  public function offsetExists($offset)
  {
    return true;
  }
  
  public function offsetGet($offset)
  {
    if (substr($offset, 0,1) === '@') {
      return $this->getAttribute(substr($offset, 1));
    } else {
      return new NodeIterator($this->getElementsByTagName($offset));
    }
  }
  
  public function offSetSet($offset, $value)
  {
    if (substr($offset, 0,1) === '@') {
      return $this->setAttribute(substr($offset, 1), $value);
    } else {
      return $this->getFirst($offset)->setNodeValue($value);
    }
  }
  
  public function setNodeValue($string)
  {
    $this->nodeValue = htmlentities($string, ENT_COMPAT|ENT_XML1, 'UTF-8', false);
  }
  
  public function offsetUnset($offset)
  {
    return null;
  }
  
  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $match = htmlentities(\bloc\registry::getNamespace($match, $this), ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    }
    return $matches;
  }
  
  public function __toString()
  {
    return $this->nodeValue;
  } 
}