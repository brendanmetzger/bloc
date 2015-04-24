<?php
namespace bloc;

/**
* Response
*/
class Response
{
  use \bloc\registry;
  
  private $body, $headers = [];
  public $type;

  
  
  public function __construct($request, $output = 'Well?')
  {
    $this->type = $request->format; 
    $this->addHeader([
      'html' => 'Content-Type: text/html; charset=utf-8',
      'json' => 'Content-Type: application/javascript; charset=utf-8',
      'xml'  => 'Content-Type: text/xml; charset=utf-8',
      'svg'  => 'Content-Type: image/svg+xml; charset=utf-8',
    ][$this->type]);
    
    $this->setBody($output);
  }
  
  public function getBody()
  {
    return $this->body;
  }
  
  public function setBody($body)
  {
    $this->body = $body;
  }
  
  public function addHeader($header)
  {
    $this->headers[] = $header;
  }
  
  public function __toString()
  {
    foreach ($this->headers as $header) {
      header($header);
    }
    return (string) $this->body;
  }
}