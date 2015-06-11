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
  
  public function getFirst($nodeName, $offset = 0)
  {
    $result = $this->getElementsByTagName($nodeName);
      
    return $offset >= 0 && $result->length > $offset ? $result->item($offset) : $this->appendChild(new self($nodeName, null));
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
    if (empty($string)) return;
    $this->nodeValue = htmlentities($string, ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    return $this;
  }
  
  public function offsetUnset($offset)
  {
    return null;
  }
  
  public function getIndex()
  {
    $index = (int)preg_replace('/[^0-9]*([0-9]+)/', '$1', substr($this->getNodePath(), strlen($this->parentNode->getNodePath()), -1));
    return $index === 0 ? $index : $index - 1;
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
  
  
  public function write($logging = false)
  {
    $output = $this->ownerDocument->saveXML($this);
    return $logging ? htmlentities($output) : $output;
  }
}