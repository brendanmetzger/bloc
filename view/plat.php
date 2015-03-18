<?php

namespace bloc\view;

/**
 * Plat

   This workhorse is an interesting specimen. Feed it a node in the constructor. Should you 
  be interested in mapping data to the node (and you should), pass in a chunk of data that has 
  keys (object or array) to the `plat->addData()` method.

 */

class Plat
{
  
  public function __construct($view, $data)
  {
    foreach ($view->parser->queryCommentNodes('iterate') as $key => $node) {
      $matched = $this->map($view, $node->nextSibling);
      
      $nodelist = substr($matched->item(0)->nodeValue, 2,-1);
        
      foreach ($data->{$key} as $datum) {
        \bloc\console::dump($datum);
      }
    }
  }
  
  public function map($view, $context)
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
    
    \bloc\console::dump($exp);
    return $view->xpath->query($exp, $context);
  }
  
  public function __clone()
  {
    $this->node = $this->node->cloneNode(true);
  }
}