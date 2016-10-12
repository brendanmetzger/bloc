<?php
namespace bloc\dom;

/**
 * DOM Document Extension
 */

class Document extends \DOMDocument
{
  const NODE = 0;
  const FILE = 1;
  const TEXT = 2;
  const PATH = 3;

  private $xpath = null,
          $filepath = null,
          $options = [
            'encoding'           => 'UTF-8',
            'preserveWhiteSpace' => false,
            'validateOnParse'    => false,
            'formatOutput'       => true,
            'resolveExternals'   => true,
          ];

  function __construct($data = false, $options = [], $flag = 1)
  {
    libxml_use_internal_errors(true);
    parent::__construct('1.0', 'UTF-8');

    foreach (array_merge($this->options, $options) as $prop => $value) {
      $this->{$prop} = $value;
    }

    $this->registerNodeClass('\DOMElement', '\bloc\DOM\Element');

    if ($data) {
      switch ($flag) {
        case self::NODE:
          $this->appendChild($this->importNode($data, true));
          break;
        case self::FILE:
          $this->filepath = PATH."{$data}.xml";
          $this->load($this->filepath , LIBXML_NOENT|LIBXML_COMPACT);
          break;
        case self::PATH:
          $this->filepath = $data;
          $this->load($this->filepath , LIBXML_NOENT|LIBXML_COMPACT);
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

  public function save($path = null)
  {
    return parent::save($path ?: $this->filepath);
  }

	static public function ELEM($text)
	{
    return (new self(trim($text), [], self::TEXT))->documentElement;
	}

  public function find($expression, $context = null)
  {
    if ($this->xpath === null) {
      $this->xpath = new \DOMXpath($this);
    }
    return new Iterator($this->xpath->query($expression, $context ?: $this->documentElement));
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
