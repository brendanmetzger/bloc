<?php
namespace bloc\types;

class String
{
  
  public function rotate($string, $n = 13) {
      $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
      $n = (int)$n % 26;
      if (!$n) return $string;
      if ($n < 0) $n += 26;
      if ($n == 13) return str_rot13($string);
      $rep = substr($letters, $n * 2) . substr($letters, 0, $n * 2);
      return strtr($string, $letters, $rep);
  }
  
}