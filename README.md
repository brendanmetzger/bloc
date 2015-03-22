# bloc

b's Lode of Code


## Basic usage

Create a file with something like this in it, index.php would be a good choice, obviously a web accessible spot if the intention is to create a computer internet website.

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
      $router->delegate('foo', 'bar');
    });


    #4. Run the app. Nothing happens w/o this. Can call different stuff from the queue.
    $app->run('http-request');
    
## Some conventions.

Part of my motivation in creating this framework is to avoid any/all dependencies and to create an very small and comprehensible system without any superfluity of features. In an effort to make code elegant enough to just read, I'm avoiding boilerplate docblocks and configuration. Keeping inheritance and interfaces as modest as possible to avoid scavenger hunts to find where simple variable is coming from and why it magically has some property that you have no idea how it came into being. So a few moments of careful study should do the trick. With that, here are some of my idiosyncratic conventions:

- Anything particular, novel, or in any way configured will have to - nay, SHOULD BE - done in a model or controller.
- a method starting with *rig* as in `rigThisThing` will always be returning a new instance of some object. Unlike a factory method, it will always provide you with the exact same object type back, but perhaps configured differently due to method arguments or environmental variables. **If you see a `$variable = $obj->rigMe();` the $variable will be an object!** 
 
## Templates
Still under development, but they are all HTML and data is supplied via simple tagging with `[@var]`. The rules for tagging must be:

### Syntax
- the entire node must start with `[` and end with `]` and somewhere inside must contain an alphanumeric key bounded by the `@` symbol and a whitespace character. Remember, an attribute is a node as well, so same rules apply.
- if the key does not exist the entire node will be deleted - this is a good thing, as it allows you to avoid the most common bastardization of separating logic from the view, which is peppering your template with conditions to show/hide things based on your data.
- if you don't want to delete a node because you *might* have data (input example below), make sure your data has that key, but make the value `null`
- While Element or Attribute nodes must begin and end with `[` and `]` respectively, the @key can be anywhere. This allows you to not do extra data parsing (also handy in loops as you will see).

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
 
 

## Usage of a URL
For advanced page routing, the framework expects variables like 'controller', 'action', and 'params'. Inside your controller action the params will be provided to you as arguments in the order received. For the clean look, a simple apache rewrite will clean things up. Here are examples:

    // This is the expected query string format:
    http://example.com/?controller=foo&action=bar&params=whatever/you/need
    
    // an apache rewrite:
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^([a-zA-Z]*)\/?([a-zA-Z]*)\/?(.*)?$ index.php?controller=$1&action=$2&params=$3 [B,QSA,L]
    
    // Will let you use this:
    http://example.com/foo/bar/whatever/you/want
    
## Controllers
Moving on from the immediate example above, you should have a controller class named foo, with a method named bar. Here is what it would probably look like.

    class foo
    {
      public function bar($querystring, $params, $exploded)
      {
        printf('OUTPUT: "%s" "%s" "%s" ', $querystring, $params, $exploded);
        // OUTPUT "whatever" "you" "need"
      }
    }
    
    