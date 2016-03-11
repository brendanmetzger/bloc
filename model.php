<?php
namespace bloc;

/**
  * Model Abstraction
  */

  abstract class Model implements \ArrayAccess, \IteratorAggregate
  {
    static protected $fixture = [];

    public $context = null,
           $errors  = [];

    abstract protected function identify($identity);
    abstract protected function initialize();
    abstract public function save();

    public function __construct($item = null, $data = [])
    {
      if ($item instanceof \DOMElement) {
        $this->context = $item;
      } else if (!$this->context = $this->identify($item)) {
        $this->context = $this->initialize();
        $this->afterCreate();
      }

      if (!empty($data)) {
        try {
          static::$fixture = array_replace_recursive(static::$fixture, $data);
          $this->input(static::$fixture, $this->context);
        } catch (\UnexpectedValueException $e) {
          $this->errors[] = $e->getMessage();
        }
      } else if ($this->context && !$this->context->hasAttributes() && !$this->context->hasChildNodes()){
        $this->input(static::$fixture, $this->context);
      }
    }

    public function input($data, \DOMElement $context)
    {
      $element = key($data);

      $removes = $reorders = [];
      foreach ($data[$element] as $key => $value) {

        if ($key === '@') {
          $this->setAttributes($value, $context);
        } else if ($key === 'CDATA') {
          $method = "set{$element}";
          if (method_exists($this, $method)) {
            $this->{$method}($context, $value);
          } else {
            $context->nodeValue = $value;
          }


        } else if (is_int($key)) {
          // if the key is an integer, we have an array of elements to add/update. If the set(Element)
          // method returns false, we add add the found/created element to a list of nodes to remove at the
          // completion of this routine -- ie. return false to delete the context node.
          $subcontext = $context->parentNode->getFirst($element, $key);

          if ($this->{"set{$element}"}($subcontext, $data[$element][$key]) === false) {
            $removes[] = $subcontext;
         } else {
           // Appending the $subcontext ensures that the order remains the order provided by the input mechanism.
           $reorders[] = $subcontext;
         }
        } else {
          // we have an entire element, that can have elements, attributes, etc, so merge that.
          // Be extremely careful here - this will create an element and add to the document. it's up to
          // you to ensure that if you are going to be inserting an array of elems (see is_int($key) above)
          // that you make sure your array index starts at zero. If you don't, you will probably have an
          // empty node inserted into the document, and this will likely cause a validation error.
          $this->input([$key => $value], $context->getFirst($key));
        }
      }

      if (! $context->hasAttributes() && ! $context->hasChildNodes()) {
        $removes[] = $context;
      }

      foreach ($removes as $element) {
        if ($element->parentNode) {
          $element->parentNode->removeChild($element);
        }
      }

      foreach ($reorders as $element) {
        $element->parentNode->appendChild($element);
      }
    }


    public function __call($method, $arguments)
    {

      $accessor = substr($method, 0, 3); // will be get or set
      $context  = $arguments[0];

      if ($accessor == 'get') {
        return $context[substr($method,3)];
      } else {
        $value = $arguments[1];
        if (strtolower(substr($method, -9)) == 'attribute') {
          $key = substr($method, 3, -9);
          $context->setAttribute($key, $value);
        } else {
          $context->setNodeValue($value);
        }
      }
    }

    public function __get($property)
    {
      $this->{$property} = $this->{"get{$property}"}($this->context);
      return $this->{$property};
    }

    public function setAttributes(array $attributes, \DOMElement $context)
    {
      foreach ($attributes as $property => $value) {
        $this->{"set{$property}Attribute"}($context, $value);
      }
    }

    static public function type() {
      return strtolower(substr(get_called_class(), 7));
    }

    public function get_model()
    {
      return static::type();
    }

    public function offsetExists($offset)
    {
      return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
      return $this->{$offset};
    }

    public function offSetSet($offset, $value)
    {
      return $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
      return null;
    }

    public function getIterator() {
      return new \ArrayIterator(static::$fixture);
    }

    protected function afterCreate()
    {
      // TODO: look into fixture for updated/created attributes
    }

    protected function beforeSave()
    {
      // TODO: look into fixture for updated/created attributes
    }

    public function __toString()
    {
      return (string)$this->context;
    }
  }
