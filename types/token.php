<?php
namespace bloc\types;

/**
 * Token
 */

class Token
{
  static public function generate($key, $secret)
  {
    return sha1($key . date('m') . $_SERVER['REMOTE_ADDR'] . $secret);
  }
}
