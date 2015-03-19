<?php
namespace bloc;

/**
 * A view represents the object that will mash any XML based data together.
 */

class View
{
  public $dom;
  public $xpath;
  public $parser;
	
  /**
   * Document Creator
   *
   * @param string $file
   * @return \DomDocument object
   */
  private static function DOM($file)
  {
    $document = new \DomDocument('1.0', 'UTF-8');

    $document->encoding           = 'UTF-8';
    $document->preserveWhiteSpace = false;
    $document->formatOutput       = true;
    $document->load(PATH.$file, LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOXMLDECL|LIBXML_NOENT);

    return $document;
  }
  
  public function __construct($template = '')
  {
    $this->dom    = self::DOM($template); 
    $this->xpath  = new \DomXpath($this->dom);
    $this->parser = new view\parser($this);
  }
  
  public function __set($key, $value)
  {
    foreach ($this->parser->queryCommentNodes("replace {$key}") as $adjacency => $node) {
      $this->setPage($value, $node->{$adjacency});
      $node->parentNode->removeChild($node);
    }
  }
	
	public function setPage($page, $swap)
	{
    $element = $this->dom->importNode(self::DOM($page)->documentElement, true);
		$swap->parentNode->replaceChild($element, $swap);
        
    foreach ($this->parser->queryCommentNodes('insert') as $path => $node) {
      $this->setPage($path, $node);
    }
	}
	
	public function render()
	{
		$root = $this->dom->documentElement;
    
		$ns = ['math' => 'http://www.w3.org/1998/Math/MathML',
			     'svg'  => 'http://www.w3.org/2000/svg'
			    ];
    
    // Add namespaces
		foreach ($this->xpath->query('/html/body//svg|/html/body//math') as $ns_elem) {
			$ns_elem->setAttribute('xmlns', $ns[$ns_elem->nodeName]);
		}
    
    // Add links, meta, and style tags to the very top
    foreach ($this->xpath->query('/html/body//style|/html/body//meta|/html/body//link') as $head_node) {
      $root->firstChild->appendChild($head_node);
    }
    
    // Put all Javascripts right at the bottom.
    $script = $this->dom->createElement('script');
    $script->setAttribute('type', 'text/javascript');
    foreach ($this->xpath->query('/html/body//script') as $javascript) {
      // $script->appendChild($this->dom->createTextNode($javascript->nodeValue));
      $root->lastChild->appendChild($javascript);
      // $javascript->parentNode->removeChild($javascript);
    }
    // $root->lastChild->appendChild($script);
		
		$attrs = [
			'xmlns'      => 'http://www.w3.org/1999/xhtml',
			'xmlns:foaf' => 'http://xmlns.com/foaf/0.1/',
			'xmlns:dc'   => 'http://purl.org/dc/elements/1.1/',
			'xmlns:svg'  => 'http://www.w3.org/2000/svg',
			'version'    => 'XHTML+RDFa 1.0',
			'xml:lang'   => 'en',
    ];
		
		foreach ($attrs as $key => $value) {
			$root->setAttribute($key, $value);
		}
    
    return $this->dom->saveXML();
	}
}