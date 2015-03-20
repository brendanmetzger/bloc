<?php

namespace bloc\view;

/**
 * Plat
 * This workhorse is an interesting specimen. Feed it a view in the constructor. Should you 
 * be interested in mapping data to the node (and you should), pass in a chunk of data (object or array) that has 
 * matching keys in [the @novel template format] as well.
 */

class Plat
{  
  public function __construct($view, $data)
  {

    // cycle through iterators and see to their needs first
    foreach ($view->parser->queryCommentNodes('iterate') as $key => $node) {

      $context = $node->parentNode->removeChild($node->nextSibling);
      $matched = $this->getSlugs($view, $context);

      if (property_exists($data, $key)) {
        
        foreach ($data->{$key} as $datum) {
          ksort($datum);
          
          foreach ($matched as $template) {
            $template->nodeValue = str_replace($template->matches, array_intersect_key($datum, $template->matches), $template->slug);
          }
          $node->parentNode->insertBefore($context->cloneNode(true), $node);
        }
      }
      $node->parentNode->removeChild($node);      
    }
    
    // find document wide placeholders
    foreach ($this->getSlugs($view, $view->dom->documentElement) as $template) {
      $template->nodeValue = str_replace($template->matches, array_intersect_key((array)$data, $template->matches), $template->slug);
    }
  }
  
  public function getSlugs($view, $context)
  {
    # start with the current element and look for nodes
    $exp = "./descendant-or-self::*[";
    # if starts with the open placeholder
    $exp .= "substring(.,1,1) = '[' and ";
    # and contains the variable symbole key
    $exp .= "contains(., '@') and ";
    # and ends with the close placeholder
    $exp .= "substring(., string-length(.), 1) = ']' and ";
    # and does not contain any other nodes
    $exp .= "not(*)]";
    # union search for the attribute nodes
    $exp .= "|./descendant-or-self::*/@*[";
    # if it starts with the open placeholder
    $exp .= "substring(.,1,1) = '[' and ";
    # and it contains the variable symbol key
    $exp .= "contains(., '@') and ";
    # and ends with the close placeholder
    $exp .= "substring(., string-length(.), 1) = ']']";
    
    $nodes = $view->xpath->query($exp, $context);
    
    foreach ($nodes as $template) {
      preg_match_all('/\@([a-z\_\:0-9]+)\b/i', substr($template->nodeValue, 1, -1), $matches);
      $template->matches = array_combine($matches[1], $matches[0]);
      ksort($template->matches);
      $template->slug = substr($template->nodeValue, 1,-1);
    }
    return $nodes;
  }
}