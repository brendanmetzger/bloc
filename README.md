# bloc

B's Lode of Code


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
    
## Some conventions.

Part of my motivation in creating a framework is to (1) Avoid having a single dependency and (2) to create an entire system that you can feel like you comprehend. Part of getting there involves writing code that need not be explained via documentation, so I am avoiding docblocks and anything else that should be explainable by syntax and a few moments of careful study. To aid in parsing code, here are some of my idiosyncratic conventions:

- a method starting with *rig* as in `rigThisThing` will always be returning a new instance of some object. Unlike a factory method, it will always provide you with the exact same object type back, but perhaps configured differently due to method arguments or environmental variables. **If you see a `$variable = $obj->rigMe();` the $variable will be an object!** 
 
## Templates
Still under development, but they are all HTML and data is supplied via simple tagging with `[@var]`. The rules for tagging must be:

### Syntax
- the entire node must start with `[` and end with `]` and somewhere inside must contain an alphanumeric key bounded by the `@` symbol and a whitespace character. Remember, an attribute is a node as well, so same rules apply.
- if the key does not exist the entire node will be deleted - this is a good thing, as it allows you to replicate the most common use case for conditions; showing and hiding data.
- if you don't want to delete a node but have no data, make sure your data has that key, but make the value `null`
- While Element or Attribute nodes must begin and end with `[` and `]` respectively, the @key can be anywhere. This allows you to not do extra data parsing (and v. handy in loops as you will see).

Here is an example:
    
    // Given the markup
    <p>[My favorite condiment is @condiment and I use it on everything]</p>
    <input type="text" name="example" value="[@sticky]"/>
    <p>[Welcome, @name]</p>
    
    // and the data
    $data = ['condiment' => 'sauce', 'sticky' => null];

    // after render, you'd have
    <p>My favorite condiment is sauce and I use it on everything</p>
    <input type="text" name="example" value=""/>
    
    // note that <p> has been removed because there is no @name varible
    

### Partials
### Data Iteration 
 
 

## Rewrites for cleaner urls.
For advanced page routing, the framework expects variables like 'controller', 'action', and 'params'. Use those by requesting urls like `http://example.com/?controller=foo&action=bar&params=whatever/you/want`. Inside your controller action the params will be provided to you as arguments in the order received (exploded by backslash). For a cleaner look, parse the request string them with a rewrite, such as the apache one below:

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^([a-zA-Z]*)\/?([a-zA-Z]*)\/?(.*)?$ index.php?controller=$1&action=$2&params=$3 [B,QSA,L]