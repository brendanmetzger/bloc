<?php
namespace bloc\types;

/**
 *
 */
class Error extends \Exception {}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
  if ( E_RECOVERABLE_ERROR === $errno && strpos($errstr, 'must be an instance of') > 0) {
    \bloc\application::instance()->log($errfile . ':' . $errline);
    throw new Error($errstr, $errno);
  } else if (E_NOTICE === $errno) {
    \bloc\Application::instance()->log($errstr . "\nFile: " . $errfile . "\nLine: " . $errline);
    return true;
  }
  return false;
});
