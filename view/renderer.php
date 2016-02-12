<?php
namespace bloc\view;


/**
 * Renderers: some static functions that might be useful.
 * They return callbacks that can be used in `view->addRenderer`
*/

class Renderer
{

  // This Call
  static public function HTML($controller = null)
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
    };
  }

  static public function PREVIEWS()
  {
    $find = new \bloc\Maybe([]);
    return function($view) use (&$find) {
      foreach ($view->parser->queryCommentNodes('preview') as $stub) {
        $path = trim(substr(trim($stub->nodeValue), 8));
        // this will parse a document based on text
        $expression = '/([\/a-z0-9\-\_]+\.[a-z]{2,4})\s([0-9]+)\.\.([0-9]+)/i';
        preg_match($expression, $path, $r);
        $file = $find(PATH.$r[1])->get('file');
        $start  = $r[2]-1;
        $output = array_slice($file, $start, $r[3] - $start);
        $text   = "\n";
        $whitespace = strlen($output[0]) - strlen(preg_replace('/^\s*/', '', $output[0]));
        foreach ($output as $line) {
          $text .= substr($line, $whitespace);
        }
        $stub->parentNode->replaceChild($view->dom->createTextNode($text), $stub);
      }
    };
  }

  static public function REVIEW()
  {
    $find = new \bloc\Maybe([]);
    return function($view) use (&$find) {
      foreach ($view->parser->queryCommentNodes('review') as $stub) {
        $path = trim(substr(trim($stub->nodeValue), 7));
        // this will parse a document based on text

        $expression = '/([\/a-z0-9\-\_]+\.[a-z]{2,4})\s([a-z0-9\s]+)/i';
        preg_match($expression, $path, $r);
        $file = $find(PATH.$r[1])->get(function ($filename) {
          $text = file_get_contents($filename);
          $keywords = '/\/\*\s*([a-z0-9\s]+)\b\s*\*\/\n(.*)\n\/\*\s*end\s*\1\s*\*\//is';
          preg_match_all($keywords, $text, $r);
          return array_combine($r[1], $r[2]);
        });
        $key = trim($r[2]);
        $stub->parentNode->replaceChild($view->dom->createTextNode($file[$key]), $stub);
      }
    };
  }

  static public function PARTIAL($property = null, $path = null)
  {
    static $partials = [];
    if ($property && $path) {
      $partials[$property] = $path;
    } else {
      return function($view) use (&$partials){
        foreach ($partials as $property => $path) {
          $view->{$property} = $path;
          unset($partials[$property]);
        }
      };
    }
  }
}
