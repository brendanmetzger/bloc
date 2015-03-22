<?php
namespace bloc;

/**
 * A view represents the object that will mash any XML based data together, recursively.
 */
class View
{
  public $dom;
  public $xpath;
  public $parser;
	  
  public function __construct($template)
  {
    $this->dom  = new \DomDocument('1.0', 'UTF-8');
    $this->dom->encoding           = 'UTF-8';
    $this->dom->preserveWhiteSpace = false;
    $this->dom->formatOutput       = true;
    $this->dom->load(PATH.$template, LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOXMLDECL|LIBXML_NOENT);
    
    $this->xpath  = new \DomXpath($this->dom);
    $this->parser = new view\parser($this);
    
    foreach ($this->parser->queryCommentNodes('insert') as $path => $node) {
      $element = $this->dom->importNode((new view($path))->dom->documentElement, true);
      $node->parentNode->replaceChild($element, $node);
    }
  }
  
  public function __set($key, $path)
  {
    foreach ($this->parser->queryCommentNodes("replace {$key}") as $adjacency => $stub) {
      $element = $this->dom->importNode((new view($path))->dom->documentElement, true);
      $stub->parentNode->replaceChild($element, $stub->{$adjacency});

      // remove the original
      $stub->parentNode->removeChild($stub);
    }
  }

	public function render($data = false)
	{    
    $this->parser->parse($data ?: new \bloc\model\dictionary);
    
    $ns = ['math' => 'http://www.w3.org/1998/Math/MathML',
			     'svg'  => 'http://www.w3.org/2000/svg'
			    ];
    
    // 1st Loop: Add namespaces. 2nd: move necessary tags to head. 3rd: put javascript on bottom 
		foreach ($this->xpath->query('/html/body//svg|/html/body//math') as $ns_elem) {
			$ns_elem->setAttribute('xmlns', $ns[$ns_elem->nodeName]);
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
			'version'    => 'XHTML+RDFa 1.0',
			'xml:lang'   => 'en',
    ];
		
		foreach ($attrs as $key => $value) {
			$this->dom->documentElement->setAttribute($key, $value);
		}
    
    return $this->dom->saveXML();
	}
}