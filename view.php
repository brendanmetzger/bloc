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
    $this->_dom = new \DomDocument('1.0', 'UTF-8');

		$this->_dom->encoding           = 'UTF-8';
    $this->_dom->preserveWhiteSpace = false;
    $this->_dom->formatOutput        = true;

    $this->_dom->load(PATH.$template, LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOXMLDECL|LIBXML_NOENT);
   
    
     
    // For searching the document. Used in `$view->render` as well
    $this->xpath = new \DomXpath($this->_dom);
  }
	
	public function setPage($where, $page)
	{
    // $fragment = $this->_dom->createDocumentFragment();
    // $fragment->appendXML(trim(file_get_contents(PATH.$page)));
    // $fragment->normalize();

    $this->_page = new \DomDocument('1.0', 'UTF-8');

    $this->_page->encoding          = 'UTF-8';
    $this->_page->preserveWhiteSpace = false;
    $this->_page->formatOutput       = false;

    $this->_page->load(PATH.$page);
    $element = $this->_dom->importNode($this->_page->documentElement, true);
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
}