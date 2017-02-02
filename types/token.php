<?php
namespace bloc\types;

/**
 * Token
 */

class Token
{
  const TOKEN_RESET  = 1110;
  const TOKEN_EXPIRE = 1100;
  const TOKEN_SOURCE = 1000;
  
  private $now, $exp, $iss, $scopes;
  
  public function __construct($issuer, $scopes = [])
  {
    $this->now = time();
    $this->exp = $this->now + (60 * 60 * 24 * 7 * 15); // 15 weeks
    $this->iss = $issuer;
    $this->scopes = $scopes;
  }
  
  public function generate($key, $secret)
  {
    $header = json_encode([
      'typ' => 'JWT',
      'alg' => 'HMACSHA256'
    ]);
    
    $payload = json_encode([
      'iat'    => $this->now,
      'exp'    => $this->exp,
      'scopes' => $this->scopes,
      'iss'    => $this->iss,
      'sub'    => $key,
    ]);
    
    $encoded = urlencode(base64_encode($header)) . '.' . urlencode(base64_encode($payload));
    return $encoded . '.' . hash_hmac('sha256', $encoded, $secret);
  }
  
  public function validate($token, $secret)
  {
    $parts = explode('.', $token);
    if ($parts[2] === hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", $secret)) {
      $header  = json_decode(base64_decode(urldecode($parts[0])));
      $payload = json_decode(base64_decode(urldecode($parts[1])));
      if ($payload->iss === $this->iss) {
        if ($payload->exp > $this->now) {
          return $payload;
        }
      }
    }
    // make sure to destroy anything that happened cookie.
    $this->destroy();
    throw new \InvalidArgumentException('This token is not valid', 401);
  }
  
  
  public function save($value, $expiration)
  {
    $secure = true;
    setcookie('token', $value, $expiration, '/', '', $secure, true);
  }
  
  public function destroy()
  {
    setcookie('token', '', time()-3600, '/');
  }
    
}
