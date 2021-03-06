<?php

namespace bloc\types;

/**
* Map Trait
* Motivation is to not modify variables of an array to suit a template.
*/

trait Map
{
  protected $callback = null;

  public function map(callable $callback)
  {

    $this->callback = $callback;
    return $this;
  }

  public function current()
  {
    $object = parent::current();
    if ($this->callback) {
      return call_user_func($this->callback, $object, parent::key());
    }
    return $object;
  }

  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $match = \bloc\registry::getNamespace($match, $this);
    }
    return $matches;
  }

  public function limit($index = 0, $limit = 100, array &$paginate = [])
  {
    $index = $index - 1;
    $start = ($index * $limit);
    $paginate['total'] = $this->count();


    $paginate['pages'] = ceil($paginate['total']/$limit) - 1;

    if ($paginate['pages'] > $index) {
      $paginate['next'] = $index + 2;
    }
    if ($index > 0 && $paginate['total'] > $limit) {
      $paginate['previous'] = $index;
    }

    $paginate['index'] = $index + 1;
    $paginate['pages'] = ceil($paginate['total']/$limit);

    return new \LimitIterator($this, $start, $limit);
  }

  public function filter(callable $callback)
  {
    return new \CallbackFilterIterator($this, $callback);
  }
}
