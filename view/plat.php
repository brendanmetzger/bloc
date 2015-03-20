<?php

namespace bloc\view;

/**
 * Plat

   This workhorse is an interesting specimen. Feed it a view in the constructor. Should you 
  be interested in mapping data to the node (and you should), pass in a chunk of data that has 
  keys (object or array) as well.

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
            /*
              TODO seriously consider adding this to getSlugs method. would need to execute another loop.. would be nice to have a benchmark.
            */
            if (!property_exists($template, 'slug')) {
              preg_match_all('/\@([a-z\_\:0-9]+)\b/i', substr($template->nodeValue, 1, -1), $matches);
              $template->matches = array_combine($matches[1], $matches[0]);
              ksort($template->matches);
              $template->slug = substr($template->nodeValue, 1,-1);
            }
            
            $template->nodeValue = str_replace($template->matches, array_intersect_key($datum, $template->matches), $template->slug);
          
          }
          
          $node->parentNode->insertBefore($context->cloneNode(true), $node);
        }
      }
      
      
      $node->parentNode->removeChild($node);
      
    }
    
    // find document wide placeholders
    $matched = $this->getSlugs($view, $view->dom->documentElement);
    foreach ($matched as $node) {
      $slug = substr($node->nodeValue, 1,-1);
      // 2 is attribute, 1 is element
      $node->nodeValue = preg_replace_callback('/\@([a-z\_\:0-9]+)\b/i', function($matches) use($data){
        return $data->{$matches[1]};
      }, $slug);
      
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
    
    return $view->xpath->query($exp, $context);
  }
  
  public function __clone()
  {
    $this->node = $this->node->cloneNode(true);
  }
}