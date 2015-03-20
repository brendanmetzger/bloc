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
        foreach ($matched as $sub_node) {
          $slug = substr($sub_node->nodeValue, 2,-1);
          $template = $sub_node;
          foreach ($data->{$key} as $datum) {
            $template->nodeValue = $datum[$slug];
            $node->parentNode->insertBefore($context->cloneNode(true), $node);
          }
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