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
  
  public function parse($data)
  {
    // cycle through iterators first, looking for <!-- iterate property --> nodes
    foreach ($this->queryCommentNodes('iterate') as $node) {
      $template = $node->nextSibling;
      $property = trim(substr(trim($node->nodeValue), 7));

      try {
        $match = \bloc\registry::getNamespace($property, $data);
        $fragment = $this->mapIterator($template, $match, $property == 'item:thumbnails');
        // replaced does nothing other than hold a reference to the last created element, in case the DOM is trying to reach it for anything
        $replaced = $template->parentNode->replaceChild($fragment, $template);

      } catch (\RuntimeException $e) {
        if ($e->getCode() < 100) {
          \bloc\application::instance()->log($e);
        }
      }
    }
    
    foreach ($this->getSlugs() as $template) {      
      try {
        // this is the string to be operated on "something with $variable in it", trimmed of '[]';
        $slug = substr($template->nodeValue, 1,-1);
        // matche alpha numeric strings prefixed with $ - : is namespace operator in this case 
        preg_match_all('/\$([\@a-z\_\:0-9]+)\b/i', $slug, $matches);
        // Dictionary has a match method that will swap out real data based on the namespace requested
        $replacements = $data->replaceArrayValues(array_combine($matches[0], $matches[1]));
        // using slug, swapout nodevalue with replacements from above
        $template->nodeValue = str_replace(array_keys($replacements), $replacements, $slug);
      } catch (\RuntimeException $e) {
        // If an exception is thrown, it means data is missing. Remove the node.
        $method = $template->nodeType == 2 ? 'removeAttribute' : 'removeChild';
        $object = $template->nodeType == 2 ? $template->nodeName : $template;
        $template->parentNode->{$method}($object);
      }
    }
  }
  
  private function mapIterator(\DOMNode $template, $data, $thumbs)
  {
    $fragment = $this->view->dom->createDocumentFragment();
    
    foreach ($data as $datum) {
      
      if (! $datum instanceof \ArrayAccess) {
        $datum = new \bloc\types\dictionary($datum);  
      }
    
      $view = new \bloc\view($template);
      $view->render($datum);
      $imported_view = $this->view->dom->importNode($view->dom->documentElement, true);
      $fragment->appendChild($imported_view);
    
    }
    
    return $fragment;
  }
  
  public function queryCommentNodes($command)
  {
    $basic = "starts-with(normalize-space(.), '{$command}')";
    $remove_recursive_iterator = ($command == 'iterate') ? " and not(./ancestor::*/preceding-sibling::comment()[{$command}])" : '';
    $expression = "./descendant::comment()[{$basic}{$remove_recursive_iterator}]";
    return $this->view->xpath->query($expression);
  }
  
  public function getSlugs()
  {
    # start with the current element and look for nodes
    $exp = "//descendant-or-self::*[";
    # if starts with the open placeholder
    $exp .= "substring(.,1,1) = '[' and ";
    # and contains the variable symbole key
    $exp .= "contains(., '\$') and ";
    # and ends with the close placeholder
    $exp .= "substring(., string-length(.), 1) = ']' and ";
    # and does not contain any other nodes
    $exp .= "not(*)]";
    # union search for the attribute nodes
    $exp .= "|//descendant-or-self::*/@*[";
    # if it starts with the open placeholder
    $exp .= "substring(.,1,1) = '[' and ";
    # and it contains the variable symbol key
    $exp .= "contains(., '\$') and ";
    # and ends with the close placeholder
    $exp .= "substring(., string-length(.), 1) = ']']";
    
    return $this->view->xpath->query($exp);
  }
}