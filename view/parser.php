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
}