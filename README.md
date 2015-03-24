# bloc

*B's lode of code*


## Basic usage

Create a file with something like this in it, index.php would be a good choice, obviously a web accessible spot if the intention is to create a computer internet website.

```PHP

    namespace bloc;

    #1. Frow where index.php is located, load the application file. 
    // Notice the bloc directory is outside of the web directory - you can of course 
    // decide on your own structure, but a swell idea to keep it outside of your document root.

    require_once  '../bloc/application.php';


    #2. Create an instance of the application
    $app = new application;

    #3. All code is executed in a callback. 
    // You can have a queue of things go off according to certain situations. 
    // Here http-request is the only callback specified. 
    
    $app->queue('http-request', function($app) {
      // routes and requests
      $router  = new router('controllers', new request($_REQUEST));
      // default controller and action as arguments, in case nothin doin in the request
      $router->delegate('foo', 'bar');
    });


    #4. Run the app. Nothing happens w/o this. Can call different stuff from the queue.
    $app->run('http-request');
    
```
    
## Some conventions.

Part of my motivation in creating this framework is to avoid any/all dependencies and to create a small and comprehensible system without any superfluity of features. In an effort to make code easy enough to read, I'm avoiding boilerplate docblocks. Keeping inheritance and interfaces as modest as possible to avoid scavenger hunts to find where simple variable is coming from and why it magically has some property that you have no idea how it came into being. So a few moments of careful study should do the trick. With that, here are some of my idiosyncratic conventions:

- Anything particular, novel, or in any way configured will have to - nay, SHOULD BE - done in a model or controller.

 
## Templates

#### Goals
- eliminate ugly looping structure
- eliminate conditions
- enforce perfect structure (with nicely formatted output as a byproduct)
- absolutely no logic in the view files
- recursive (partials insert partials) with no additional flags or programming needed.

Still under development, but so far, templates are 100% valid markup and data is supplied via simple tagging with `[@var]`.

### Syntax
- the entire node must start with `[` and end with `]` and somewhere inside must contain an alphanumeric key preceded with an `@` symbol. Remember, an attribute is a node as well, so same rules apply.
- if the key does not exist the entire node will be deleted - this is a good thing, as it allows you to avoid the most common bastardization of separating logic from the view, which is peppering your template with conditions to show/hide things based on your data.
- if you don't want to delete a node because you *might* have data (input example below), make sure your data has that key, but make the value `null` or an empty string.
- While Element or Attribute nodes must begin and end with `[` and `]` respectively, the @key can be anywhere. This allows you to not do extra data parsing (also handy in loops as you will see).

Here is an example:

    
```HTML

    <!-- Given the markup -->
    <p>[My favorite condiment is @condiment and I use it on everything]</p>
    <input type="text" name="example" value="[@sticky]" id="[@name]"/>
    <p>[Welcome, @name]</p>

```    

```PHP

    // and the data
    $data = ['condiment' => 'sauce', 'sticky' => null];

```

```HTML

    <!-- after render, you'd have -->
    <p>My favorite condiment is sauce and I use it on everything</p>
    <input type="text" name="example" value=""/>

    <!-- note that <p> and input#id hav been removed because there is no @name varible -->

```

### Partials

#### Inserting 

Insert any html file by inserting a regular HTML comment node that looks like this:

```HTML

    <body>
      <!-- insert path/to/wherever/the/file/is.ext -->`
    </body>

```

The application will limit the files to the root of the directory the application resides in. That seems to make sense, but it could, of course, be changed on a whim.

#### Replacing

There are times you may want to replace a default node, most likely in the main layout, or perhaps if you want someone to see something different if they have a session started. That can be accomplished by this:

```HTML

    <!-- replace propertyName nextSibling -->
    <div>
      <p>Welcome Strange Guest</p>
    </div>

```

And to facilitate that change, you will need an instance of `\bloc\view` where you simple set a property with a url.

```PHP

    $view = new \bloc\view('some/path/to/layout.html');
    $view->propertyName = 'some/path/to/special/greeting.html';
    $view->render(new \bloc\model\dictionary([name => 'Guillermo']));

```
    
And greeting.html might have something like above:
```HTML

    <div>
      <p>[Welcome, @name]</p>
    </div>
    
```

So noting the Dictionary object passed to the `view::render` above, you actually get:

```HTML

    <div>
      <p>Welcome, Guillermo</p>
    </div>
    
```  

### Data Iteration
The Most daunting aspect, by far, is getting rid of all the ugly {{}} and %% iterators commonly found in template systems. Also, enforcing purity is really nice - with this markup based template, you literally cannot add any logic to the view files - if you want to sort, filter or modify content in any way you **must** specify those changes either directly in the data, in the controller, in the model, or through a `Map` callback that can be run when the template parser iterates the nodes. Without further adieu:

```HTML

    <!-- iterate calendars -->
    <div>
      <h2>[@month]</h2>
      <-- iterate days -->
      <div>[@day]</div>
      <!-- iterate dates -->
      <span>[@date]</span>
    </div>

```

This is a nested for loop, so you'll need some hierarchical data, so let's pretend we have a calendar object that gives us what we want:
   
```PHP 

    $calendar = new \MadeUp\Calendar();
    
    $data = new \bloc\dictionary([
      'calendars => [
        'month' => 'Jan',
        'days'  => new \bloc\Map(['m','t','w','t','f','s','s,], function($day) {return ['day' => $day];}),
        'date'  => $calendar->daysInJan(),
      ]
    ]);
    
    $view->render($data);
    
    // you get the gist...
    
```    

## Usage of a URL
For advanced page routing, the framework expects variables like 'controller', 'action', and 'params'. Inside your controller action the params will be provided to you as arguments in the order received. For the clean look, a simple apache rewrite will clean things up. Here are examples:

    // This is the expected query string format:
    http://example.com/?controller=foo&action=bar&params=whatever/you/need
    
```ApacheConf

    # an apache rewrite:
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^([a-zA-Z]*)\/?([a-zA-Z]*)\/?(.*)?$ index.php?controller=$1&action=$2&params=$3 [B,QSA,L]
   
``` 
   
    // Will let you use this:
    http://example.com/foo/bar/whatever/you/want
    
## Controllers
Moving on from the immediate example above, you should have a controller class named foo, with a method named bar. Here is what it would probably look like.

```PHP

    class foo
    {
      public function bar($querystring, $params, $exploded)
      {
        printf('OUTPUT: "%s" "%s" "%s" ', $querystring, $params, $exploded);
        // OUTPUT "whatever" "you" "need"
      }
    }
    
```    