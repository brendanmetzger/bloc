<?php
namespace bloc\types;

/**
 *
 */
class Error extends \Exception {}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
  if ( E_RECOVERABLE_ERROR === $errno && strpos($errstr, 'must be an instance of') > 0) {
    throw new Error($errstr, $errno);
  }
  return false;
});
