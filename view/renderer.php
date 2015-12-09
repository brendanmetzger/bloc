<?php
namespace bloc\view;


/**
 * Renderers: some static functions that might be useful.
 * They return callbacks that can be used in `view->addRenderer`
*/

class Renderer
{

  // This Call
  public static function HTML($controller = null)
  {
    return function($view) {
      if (strtolower($view->dom->documentElement->nodeName) != 'html') return;
      // move style, meta, and link tags up to the head.
      foreach ($view->xpath->query('/html/body//style|/html/body//meta|/html/body//link') as $head_node) {
        $view->dom->documentElement->firstChild->appendChild($head_node);
      }

      // move all script tags down to the bottom.
      foreach ($view->xpath->query('/html/body//script') as $javascript) {
        $view->dom->documentElement->lastChild->appendChild($javascript);
      }

      // remove expunged items from view
      foreach ($view->xpath->query('/html/body//*[@data-updated="expunged"]') as $remove) {
        $remove->parentNode->removeChild($remove);
      }

  		$attrs = [
        'xmlns'      => 'http://www.w3.org/1999/xhtml',
        'xmlns:foaf' => 'http://xmlns.com/foaf/0.1/',
        'xmlns:dc'   => 'http://purl.org/dc/elements/1.1/',
        'xmlns:svg'  => 'http://www.w3.org/2000/svg',
        'version'    => 'XHTML+RDFa 1.0',
        'xml:lang'   => 'en',
      ];

      // and some handy namespaces to the <html> elements
  		foreach ($attrs as $key => $value) {

  			$view->dom->documentElement->setAttribute($key, $value);
  		}

      $ns = ['math' => 'http://www.w3.org/1998/Math/MathML',
  			     'svg'  => 'http://www.w3.org/2000/svg'
  			    ];

      // Add namespaces to svg and math
  		foreach ($view->xpath->query('//svg|//math') as $ns_elem) {
  			$ns_elem->setAttribute('xmlns', $ns[$ns_elem->nodeName]);
  		}
    };
  }

  public static function addPartials(\bloc\controller $controller) {
    return function ($view) use ($controller) {

      $partials = $controller->getPartials();

      foreach ($partials as $property => $path) {
        $view->{$property} = $path;
        unset($partials->$property);
      }
    };
  }
}
