<?php

namespace bloc\view;

/**
* Parser
*/

class Parser
{
  private $view;
  
  public function __construct(\bloc\view $view)
  {
    $this->view = $view;
  }
  
  
  public function parse(\ArrayAccess $data)
  {
    // cycle through iterators and see to their needs first
    foreach ($this->queryCommentNodes('iterate') as $key => $node) {

      $context = $node->parentNode->removeChild($node->nextSibling);
      $matched = $this->getSlugs($context);
        
        foreach ($data->{$key} as $datum) {
          foreach ($matched as $template) {
            $template->nodeValue = str_replace($template->matches, array_intersect_key($datum, $template->matches), $template->slug);
          }
          $node->parentNode->insertBefore($context->cloneNode(true), $node);
        }
        $node->parentNode->removeChild($node);      
    }
    
    // find document wide placeholders
    foreach ($this->getSlugs($this->view->dom->documentElement) as $template) {
      $datum = $data->intersection($template->matches);
      ksort($datum);
      $template->nodeValue = str_replace($template->matches, $datum, $template->slug);
    }
  }
  
  public function queryCommentNodes($command)
  {
    $output      = [];
    $length      = strlen($command);
    $expression  = sprintf("//descendant::comment()[starts-with(normalize-space(.), '%s')]", $command);
    
    foreach ($this->view->xpath->query($expression) as $node) {
      $key = trim(substr(trim($node->nodeValue),$length));
      $output[$key] = $node;
    }

    return $output;
  }
  
  public function getSlugs($context)
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
    
    $nodes = $this->view->xpath->query($exp, $context);
    
    foreach ($nodes as $template) {
      preg_match_all('/\@([a-z\_\:0-9]+)\b/i', substr($template->nodeValue, 1, -1), $matches);
      $template->matches = array_combine($matches[1], $matches[0]);
      ksort($template->matches);
      $template->slug = substr($template->nodeValue, 1,-1);
    }
    return $nodes;
  }
}