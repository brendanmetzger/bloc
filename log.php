<?php
namespace bloc;

/**
 * Log
 * Usage: \bloc\log::write('message');
 *        \bloc\log::read();
 */
  class Log
  {
    private $messages = [];
    
    public function write($message)
    {
      static $instance;
      
      if ($instance === null) {
        $instance = new static($config);
      }
      
      $instance->messages[] = $message;
    }
    
    public function read()
    {
      return $this->messages;
    }
    
  }