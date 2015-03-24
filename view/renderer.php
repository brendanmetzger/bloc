<?php
namespace bloc\view;


/**
 * Renderers: some static functions that might be useful.
 * They return callbacks that can be used in `view->addRenderer`
*/

class Renderer
{
  
  // This Call
  public static function HTML()
  {
    return function($view) {
      foreach ($view->xpath->query('/html/body//style|/html/body//meta|/html/body//link') as $head_node) {
        $view->dom->documentElement->firstChild->appendChild($head_node);
      }
    
      foreach ($view->xpath->query('/html/body//script') as $javascript) {
        $view->dom->documentElement->lastChild->appendChild($javascript);
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
  			$view->dom->documentElement->setAttribute($key, $value);
  		}
    
      $ns = ['math' => 'http://www.w3.org/1998/Math/MathML',
  			     'svg'  => 'http://www.w3.org/2000/svg'
  			    ];
    
      // 1st Loop: Add namespaces. 2nd: move necessary tags to head. 3rd: put javascript on bottom 
  		foreach ($view->xpath->query('//svg|//math') as $ns_elem) {
  			$ns_elem->setAttribute('xmlns', $ns[$ns_elem->nodeName]);
  		}
    };
  }
}