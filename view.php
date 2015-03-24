<?php
namespace bloc;

/**
 * A view represents the object that will mash any XML based data together, recursively.
 */
class View
{
  public $dom, $xpath, $parser;
	  
  static public function DOM() {
    $document  = new \DomDocument('1.0', 'UTF-8');
    $document->encoding           = 'UTF-8';
    $document->preserveWhiteSpace = false;
    $document->formatOutput       = true;
    
    return $document;
  }
  
  public function __construct($document_element)
  {
    $this->dom  = self::DOM();
    
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
  
  public function __set($key, $path)
  {
    $command = "replace {$key}";
    foreach ($this->parser->queryCommentNodes($command) as $stub) {
      $adjacency = trim(substr(trim($stub->nodeValue),strlen($command)));
      $element = $this->dom->importNode((new view($path))->dom->documentElement, true);
      $stub->parentNode->replaceChild($element, $stub->{$adjacency});

      // remove the original
      $stub->parentNode->removeChild($stub);
    }
  }
  
	public function render($data = false, $finish = true)
	{    
    $this->parser->parse($data ?: new \bloc\model\dictionary);
    
    if (!$finish) {
      return;
    }
    
    foreach ($this->xpath->query('/html/body//style|/html/body//meta|/html/body//link') as $head_node) {
      $this->dom->documentElement->firstChild->appendChild($head_node);
    }
    
    foreach ($this->xpath->query('/html/body//script') as $javascript) {
      $this->dom->documentElement->lastChild->appendChild($javascript);
    }
    
		$attrs = [
      'xmlns'      => 'http://www.w3.org/1999/xhtml',
      'xmlns:foaf' => 'http://xmlns.com/foaf/0.1/',
      'xmlns:dc'   => 'http://purl.org/dc/elements/1.1/',
      'xmlns:svg'  => 'http://www.w3.org/2000/svg',
      'version'    => 'XHTML+RDFa 1.0',
      'xml:lang'   => 'en',
    ];
		
		foreach ($attrs as $key => $value) {
			$this->dom->documentElement->setAttribute($key, $value);
		}
    
    $ns = ['math' => 'http://www.w3.org/1998/Math/MathML',
			     'svg'  => 'http://www.w3.org/2000/svg'
			    ];
    
    // 1st Loop: Add namespaces. 2nd: move necessary tags to head. 3rd: put javascript on bottom 
		foreach ($this->xpath->query('//svg|//math') as $ns_elem) {
			$ns_elem->setAttribute('xmlns', $ns[$ns_elem->nodeName]);
		}
		
    return $this->dom->saveXML();
	}
}