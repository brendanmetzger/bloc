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
    // cycle through iterators first, looking for <!-- iterate property --> nodes
    foreach ($this->queryCommentNodes('iterate') as $node) {
      $template = $node->parentNode->removeChild($node->nextSibling);
      $property = trim(substr(trim($node->nodeValue), 7));

      try {
        $this->mapIterator($template, $node, $data->{$property});
      } catch (\RuntimeException $e) {
       \bloc\application::error($e, 2);
      }
    }

    foreach ($this->getSlugs() as $template) {
      $slug = substr($template->nodeValue, 1,-1);
      preg_match_all('/\@([a-z\_\:0-9]+)\b/i', $slug, $matches);
      $matches = array_combine($matches[1], $matches[0]);
      ksort($matches);
      $template->nodeValue = str_replace($matches, $data->intersection($matches), $slug);
    }
  }
  
  private function mapIterator(\DOMNode $template, \DOMNode $placeholder, $data)
  {
    foreach ($data as $datum) {
      $view = new \bloc\view($template);
      $view->render(new \bloc\model\dictionary($datum));
      $imported_view = $this->view->dom->importNode($view->dom->documentElement, true);
      $placeholder->parentNode->insertBefore($imported_view, $placeholder);
    }
    $placeholder->parentNode->removeChild($placeholder);
  }
  
  public function queryCommentNodes($command)
  {
    $command = "starts-with(normalize-space(.), '{$command}')";
    $expression = "./descendant::comment()[{$command} and not(./ancestor::*/preceding-sibling::comment()[{$command}])]";
    
    return $this->view->xpath->query($expression);
  }
  
  public function getSlugs()
  {
    # start with the current element and look for nodes
    $exp = "//descendant-or-self::*[";
    # if starts with the open placeholder
    $exp .= "substring(.,1,1) = '[' and ";
    # and contains the variable symbole key
    $exp .= "contains(., '@') and ";
    # and ends with the close placeholder
    $exp .= "substring(., string-length(.), 1) = ']' and ";
    # and does not contain any other nodes
    $exp .= "not(*)]";
    # union search for the attribute nodes
    $exp .= "|//descendant-or-self::*/@*[";
    # if it starts with the open placeholder
    $exp .= "substring(.,1,1) = '[' and ";
    # and it contains the variable symbol key
    $exp .= "contains(., '@') and ";
    # and ends with the close placeholder
    $exp .= "substring(., string-length(.), 1) = ']']";
    
    return $this->view->xpath->query($exp);
  }
}