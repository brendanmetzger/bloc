<?php
namespace bloc\DOM;

/**
 * DOM Document Extension
 */

class Document extends \DOMDocument
{
  const NODE = 0;
  const FILE = 1;
  const TEXT = 2;
  
  private $xpath = null,
          $options = [
            'encoding' => 'UTF-8',
            'preserveWhiteSpace' => false,
            'validateOnParse' => false,
            'formatOutput' => true,
          ];

  
  function __construct($data = false, $options = [], $flag = 1)
  {
    libxml_use_internal_errors(true);
    parent::__construct('1.0', 'UTF-8');
    
    foreach (array_merge($this->options, $options) as $prop => $value) {
      $this->{$prop} = $value;
    }
    
    $this->registerNodeClass('\\DOMElement', '\\bloc\\DOM\\Element');
    
    if ($data) {
      switch ($flag) {
        case self::NODE:
          $this->appendChild($this->importNode($data, true));
          break;
        case self::FILE:
          $this->load(PATH."{$data}.xml" , LIBXML_NOENT|LIBXML_COMPACT);
          break;
        case self::TEXT:
          $this->loadXML($data);
          break;
        default:
          throw new \InvalidArgumentException("Type not recognized", 1);
          break;
      }
    }
  }
  
  public function find($expression, $context = null)
  {
    if ($this->xpath === null) {
      $this->xpath = new \DOMXpath($this);
    }
    return new NodeIterator($this->xpath->query($expression, $context ?: $this->documentElement));
  }
    
  public function pick($expression, $offset = 0)
  {
    return $this->find($expression)->pick($offset);
  }
  
  public function errors()
  {
    return libxml_get_errors();
  }
}