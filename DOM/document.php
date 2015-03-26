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
  
  private $options = [
    'encoding' => 'UTF-8',
    'preserveWhiteSpace' => false,
    'validateOnParse' => false,
    'formatOutput' => true,
  ];
     
  function __construct($data = false, $options = [], $flag = 1)
  {
    parent::__construct('1.0', 'UTF-8');
    
    foreach (array_merge($this->options, $options) as $prop => $value) {
      $this->{$prop} = $value;
    }
    
    $this->registerNodeClass('\\DOMElement', 'DOM\\Element');
    
    if ($data && $flag === self::FILE) {
      $this->load(PATH."{$data}.xml");
    }
  }
}