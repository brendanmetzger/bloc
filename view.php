<?php
namespace bloc;

/**
 * View
 */

class View
{
  public $xpath;
  
  private $_dom;
  private $_page;
	
  function __construct($template = '')
  {
    $this->_dom = $this->makeNewDoc($template);
     
    // For searching the document. Used in `$view->render` as well
    $this->xpath = new \DomXpath($this->_dom);
  }
  
	
	public function setPage($where, $page)
	{
    $page = $this->makeNewDoc($page);
    $element = $this->_dom->importNode($page->documentElement, true);
    $swap = $this->xpath->query($where)->item(0);
		$swap->parentNode->replaceChild($element, $swap);
		
	}
	
	public function render()
	{
		// add tags to svg elements, math elemens, and any other ns shit
    // xpath style|link|meta
		$root = $this->_dom->documentElement;
    
    $add_ns = '/html/body//svg|/html/body//math';
    
		
		$ns = array('math' => 'http://www.w3.org/1998/Math/MathML',
			          'svg'  => 'http://www.w3.org/2000/svg'
			          );
    
		foreach ($this->xpath->query($add_ns) as $ns_elem) {
			$ns_elem->setAttribute('xmlns', $ns[$ns_elem->nodeName]);
		}
    
    // Add links, meta, and style tags to the very top
    
    foreach ($this->xpath->query('/html/body//style|/html/body//meta|/html/body//link') as $head_node) {
      $root->firstChild->appendChild($head_node);
    }
    
    // Put all Javascripts right at the bottom.
    $script = $this->_dom->createElement('script');
    $script->setAttribute('type', 'text/javascript');
    foreach ($this->xpath->query('/html/body//script') as $javascript) {
      $script->appendChild($this->_dom->createTextNode($javascript->nodeValue));
      $javascript->parentNode->removeChild($javascript);
    }
    
    
    
    $root->lastChild->appendChild($script);
		
		$attrs = array(
			'xmlns'      => 'http://www.w3.org/1999/xhtml',
			'xmlns:foaf' => 'http://xmlns.com/foaf/0.1/',
			'xmlns:dc'   => 'http://purl.org/dc/elements/1.1/',
			'xmlns:svg'  => 'http://www.w3.org/2000/svg',
			'version'    => 'XHTML+RDFa 1.0',
			'xml:lang'   => 'en',
		);
    
		
		
		foreach ($attrs as $key => $value) {
			$root->setAttribute($key, $value);
		}
    
    return $this->_dom->saveXML();
	}
  
  private function makeNewDoc($file)
  {
    $document = new \DomDocument('1.0', 'UTF-8');

    $document->encoding           = 'UTF-8';
    $document->preserveWhiteSpace = false;
    $document->formatOutput       = true;

    $document->load(PATH.$file, LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOXMLDECL|LIBXML_NOENT);

    return $document;
  }
}