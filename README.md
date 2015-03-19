# bloc

Brendan's Load of Code

## Basic usage

Create a file with something like this in it, index.php would be a good choice, obviously a web accessible spot.

    namespace bloc;

    #1. Frow where index.php is located, load the application file. Notice the bloc directory is outside of the web directory - you can of course decide on your own structure, but a swell idea to keep it outside of your document root.
    require_once  '../bloc/application.php';


    #2. Create an instance of the application
    $app = new application;

    #3. All code is executed in a callback. You can have a queue of things go off according to certain situations. Here http-request is the only callback specified. 
    $app->queue('http-request', function($app) {
      // routes and requests
      $router  = new router('controllers', new request($_REQUEST));
      // default controller and action as arguments, in case nothin doin in the request
      $router->delegate('some_controller', 'some_action');
    });


    #4. Run the app. Nothing happens w/o this. Can call different stuff from the queue.
    $app->run('http-request');
 

## Rewrites for cleaner urls.
For advanced page routing, the framework expects variables like 'controller', 'action', and 'params'. Use those, or for a cleaner look, parse the request string them with a rewrite, such as the apache one below:

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^([a-zA-Z]*)\/?([a-zA-Z]*)\/?(.*)?$ index.php?controller=$1&action=$2&params=$3 [B,QSA,L]