<?php
namespace bloc\types;
/*
 * Useful for Duck-Typing roles
 */
interface authentication {
  public function authenticate($token);
}
