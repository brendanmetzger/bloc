<?php
namespace bloc;

class File
{
  static public $mimes = [
    'png'  => 'image/png',
    'jpeg' => 'image/jpeg',
    'jpg'  => 'image/jpeg',
    'webp' => 'image/webp',
    'ico'  => 'image/vnd',
    'woff2'=> 'font/woff2',
    'pdf'  => 'application/pdf',
    'mp4'  => 'video/mp4',
    'mp3'  => 'audio/mp3',
    'xml'  => 'application/xml',
    'html' => 'text/html',
    'js'   => 'application/javascript',
    'json' => 'application/json',
    'css'  => 'text/css',
    'svg'  => 'image/svg+xml',
    'zip'  => 'application/zip',
  ];
  
  public $uri, $url, $type, $info, $mime, $body = '';

  public function __construct(string $path, ?string $type = null)
  {
    $this->uri  = $path;
    $this->info = pathinfo($path);
    $this->type = $type ?: $this->info['extension'];
    $this->url  = ($this->type == 'gz' ? 'compress.zlib' : 'file') . '://' . realpath($this->uri);
    $this->mime = self::$mimes[$this->type];
  }
  
  static public function load($path)
  {
    $instance = new static($path);
    if (! $instance->body = file_get_contents($instance->url))
      throw new \InvalidArgumentException('Bad path: ' . $path);
    return $instance;
  }
  
  public function verify(string $hash, string $algo = 'md5') {
    return hash($algo, $this->body) === trim($hash, '"');
  }
}


class Sprite extends File
{
  public $bg = [0,0,0], $index, $cell, $row, $col;
  
  static private $sprites = [];
  
  # A singleton-esque static constructor insures even if thousands of individual 'cells'
  ##  are getting updated, the main sprite only needs to be instantiated once
  static private function instance($path)
  {
    return self::$sprites[$path] ??= new self($path);
  }
  
  private function __construct($path)
  {
    parent::__construct($path);
    [$this->index, $this->cell, $this->row, $this->col] = explode('.', $this->info['filename']);

    $x = $this->cell * $this->row;
    $y = $this->cell * $this->col;

    $this->target = (object)['width' => $this->cell, 'height' => $this->cell];

    if (file_exists($path)) {
      $this->composite = imagecreatefromjpeg($path);
    } else {
      
      $this->composite = imagecreatetruecolor($x, $y);
      // set a white-ish background color
      $this->fill([0,0,$x, $y], $this->bg);
    }
  }
    
  public function __destruct()
  {
    imagejpeg($this->composite, $this->uri, 100);
    imagedestroy($this->composite);
    echo "...saved <{$this->uri}>\n";
    
  }
  
  public function export(string $type, int $resolution)
  {
    $method = "image{$type}";
    ob_start(); // start output buffering; convert/format image; flush buffer, assign response body
    $method($this->composite, null, $resolution);
    return $this->body = ob_get_clean();
  }
  
  
  public function fill(array $bbox, array $rgb)
  {
    array_push($bbox, imagecolorallocate($this->composite, ...$rgb));
    imagefilledrectangle($this->composite, ...$bbox);
  }
  
  public function set($index, Image $image)
  {
    // as each sprite has a capacity, the index is scaled to map to the correct position on a sprite
    $index       = $index - ($this->capacity * $this->index);
    $destination = clone $this->target;
  
    if ($image->aspect > 1)
      $destination->height /= $image->aspect;
    else
      $destination->width *= $image->aspect;
  
    
    // The destination image is a checkboard, with square cells. The copy image may be any aspect, so
    // compute offset of row/column, then add the offset according to the aspect of the copy image
    $cell = [
      'x' =>      ($index % $this->col) * $this->target->width,
      'y' => floor($index / $this->row) * $this->target->height,
    ];
    
    $destination->x = $cell['x'] + round(($this->target->width  - $destination->width)  / 2);
    $destination->y = $cell['y'] + round(($this->target->height - $destination->height) / 2); 
    
    $copy = [
      'destination'        => $this->composite,
           'source'        => $image->resource,
      'destination_x'      => $destination->x,
      'destination_y'      => $destination->y,
           'source_x'      => 0,
           'source_y'      => 0,
      'destination_width'  => $destination->width,
      'destination_height' => $destination->height,
           'source_width'  => $image->width,
           'source_height' => $image->height,
    ];
    
    
    $cell['offset_width']  = $cell['x'] + $this->cell;
    $cell['offset_height'] = $cell['y'] + $this->cell;

    // Paint over cell spot with bg color, incase the image has changed
    $this->fill(array_values($cell), $this->bg);
    
    imagecopyresampled(...array_values($copy));
  }
  
  
  static public function update(int $cellsize, array $boardsize, $images)
  {
    $area = array_product($boardsize);
    
    // TODO deal with path config here

    foreach ($images as $counter => $image) {
      $path = sprintf('data/media/%s.%s.%s.%s.jpg', max(ceil($image['idx'] / $area) - 1, 0), $cellsize, $boardsize[0], $boardsize[1]);
      self::instance($path)->set($image['idx'], new Image($image['path']));
      echo "processing {$image['path']}\n";
    }
    
    return self::$sprites;
  }
  
}

class Image {
  
  public function __construct(string $url)
  {
    $this->filename = 'data/media/thumbs/' . pathinfo(parse_url($url)['path'])['basename'];
    $this->resource  = imagecreatefromjpeg($url);
    $this->width     = imagesx($this->resource);
    $this->height    = imagesy($this->resource);
    $this->aspect    = $this->width / $this->height;
  }
  
  public function __destruct() {
    imagedestroy($this->resource);
  }
}