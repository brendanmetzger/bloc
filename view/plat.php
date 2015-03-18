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
  
  public function __construct($node)
  {
    $this->node = $node;
  }

  
  
  public function __clone()
  {
    $this->node = $this->node->cloneNode(true);
  }
}