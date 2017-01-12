# *bloc*

- [ ] Make it so you can get a numerical index from a Dictionary object in template view

## License

Copyright (C) Brendan A. Metzger - All Rights Reserved  
**Unauthorized copying of this file, and all files en this repository, via any medium is strictly prohibited**  
Proprietary and confidential  
Written by Brendan Metzger <brendan.metzger@gmail.com>, September 2011-Current

This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License. To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/4.0/.



## motivation
Part of my motivation in creating my own framework has been experiment in creating a simple but powerful tool that can truly avoid *all* dependencies while remaining a small and comprehensible system. In an effort to make code easy enough to read, I'm avoiding boilerplate docblocks and keeping inheritance and interfaces as modest as possible, helping avoid scavenger hunts through dozens of files to find out how or why some magical property exists. With a few moments of careful study things should make sense, and one can read this entire repository front to back in less than an hour.

## Requirements

### For the computer

- php 5.5

### For the human

- PHP
- Website stuff (HTML,CSS, Javascript), but especially XML and the whole <node/> landscape.
- the [Document Object Model](http://en.wikipedia.org/wiki/Document_Object_Model)
- Object Oriented Basics
- Reflection (PHP's obviously)
- General Model-view-controller implementations


## Basic Usage

Create a file with something like this in it, index.php would be a good choice, obviously a web accessible spot if the intention is to create a computer internet website.

```PHP

    namespace bloc;

    #1. Frow where index.php is located, load the application file.
    // Notice the bloc directory is outside of the web directory - you can of course
    // decide on your own structure, but a decent idea to keep it outside of your document root.
    // In my applications, I generally use apache and set my document root to a folder called `views` for my index.php

    require_once  '../bloc/application.php';


    #2. Create an instance of the application
    $app = new application;

    #3. All code is executed in a callback.
    // You can have a queue of things go off according to certain situations.
    // Here http-request is the only callback specified.

    $app->prepare('http-request', function ($app, $params) {

      $request  = new Request($params);
      $response = new Response($request);

      $app->setExchanges($request, $response);


      // Provide a namespace (also a directory) to load objects that can respond to controller->action
      $router  = new Router('controllers', $request);

      try {
        $output = $router->delegate('explore', 'index');

      } catch (\Exception $e) {
        \bloc\application::instance()->log($e->getTrace());
        $view = new View('views/layout.html');
        $view->content = 'views/layouts/error.html';
        $output = $view->render(['message' => $e->getMessage()]);
      }

      // default controller and action as arguments, in case nothin doin in the request
      echo $response->setBody($output);
    });


    #4. Run the app. Nothing happens w/o this. Can call different stuff from the queue.
    $app->execute('http-request'); // (this returns 'done')

```

## Templates

#### Goals
- templates contain only one non-programmable grammar (and can thus be validated and never contain application logic)
- simple variable syntax
- no open/close looping structures or blocks of any sort
- thus no conditions or function calls can be made from a template
- input/output can be validated and formatted
- recursive (partials insert partials) with no additional flags or programming needed.


### Syntax
- templates are plain HTML (actually XML) files, and there must always be a root node. ie. `<b/><c/>` = bad `<a><b/><c/></a>` = good
- the entire body of a node that will be parsed and replaced with data must start with `[` and end with `]` and somewhere inside must contain an alphanumeric key preceded with an `$` symbol. Remember, an attribute is a node as well, so same rules apply.
- if the key does not exist the entire node will be deleted - this is a good thing, as it allows you to avoid the most common bastardization of separating logic from the view, which is peppering your template with conditions to show/hide things based on your data.
- if you don't want to delete a node because you *might* have data (input example below), make sure your data has that key, but make the value `null` or an empty string.
- While Element or Attribute nodes must begin and end with `[` and `]` respectively, the @key can be anywhere. This allows you to not do extra data parsing (also handy in loops as you will see).

Here is an example:


```HTML

    <!-- Given the markup -->
    <p>[My favorite condiment is $condiment and I use it on everything]</p>
    <input type="text" name="example" value="[$sticky]" id="[@name]"/>
    <p>[Welcome, $name]</p>

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

And to facilitate that change, you will need an instance of `\bloc\view` where you simply set a property with a url.

```PHP

    $view = new \bloc\view('some/path/to/layout.html');
    $view->propertyName = 'some/path/to/special/greeting.html';
    $view->render(new \bloc\types\dictionary([name => 'Guillermo']));

```

And greeting.html might have something like above:
```HTML

    <div>
      <p>[Welcome, $name]</p>
    </div>

```

So noting the Dictionary object passed to the `view::render` above, you actually get:

```HTML

    <div>
      <p>Welcome, Guillermo</p>
    </div>

```  

### Data Iteration
The Most daunting aspect, by far, is getting rid of all the {{}} and %% iterators commonly found in template systems. Also, enforcing purity is really nice - with this markup based template, you literally cannot add any logic to the view files - if you want to sort, filter or modify content in any way you **must** specify those changes either directly in the data, in the controller, in the model, or through a `Map` callback that can be run when the template parser iterates the nodes. Without further adieu:

```HTML

    <!-- iterate calendars -->
    <div>
      <h2>[$month]</h2>
      <-- iterate days -->
      <div>[$letter]</div>
      <!-- iterate dates -->
      <span>[$date]</span>
    </div>

```

This is a nested for loop, so you'll need some hierarchical data, so let's pretend we have a calendar object that gives us what we want:

```PHP

    $calendar = new \MadeUp\Calendar();

    $data = new \bloc\types\dictionary([
      'calendars => [
        [
          'month' => 'Jan',
          'days'  => new \bloc\Map(['m','t','w','t','f','s','s,], function($day) {
            return ['letter' => $day];
          }),
          'date'  => $calendar->daysInJan(), // assume that this returns an array [['date'=>1],['date'=>2]...]
        ],
        [
          'month'=> 'Feb',
          'days' ...
        ]
      ]
    ]);

    $view->render($data);

    // you get the gist...

```


#### Dictionaries

To insure data can be mapped, filtered, and limited by the iterator, the template requires that your data object has an `ArrayAccess` interface, and the best way to do that is **always** pass it wrapped within the `\bloc\types\dictionary` object. In fact, consider this required.


#### Nesting

You can force the parser in to accessing a nested variable like so:

`$view->render(new \bloc\types\dictionary(['sports' => ['favorite' => 'ping-pong']]));`

And get by passing it to a dictionary and the render method, it is available like so:

`<p>[My favorite sport is $sports:favorite]</p>`

Producing:

`<p>My favorite sport is ping pong</p>`

#### Scope

Sometimes while in a loop, you need access to an item not within that data structure. This is much like scope in a a programming language, and you can trick the parser into going up the foodchain by allowing it to be parsed twice.

Given:

```
    $this->genre = [
      'name' => 'Jazz',
      'types' => [
        ['sub' => 'acid'],
        ['sub' => 'cool'],
      ]
    ];

```

You could get at that like this:

```

    <!-- iterate genre:types -->
    <li>[[$$name - $sub]]</li>

```

And you would get:


```

    <li>Jazz - acid</li>
    <li>Jazz - cool</li>

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

### Raison d'être
To benefit as many use cases as possible (an app could serve different content types, for example) I generally have controllers return some object as opposed to render some output. you can determine what to do with that object (print, send elsewhere, etc) within the `$app->execute` callback that is responsible for the routing and delegation of this controller.


### Example
Using the 'Usage of a URL' example above, you would have a controller class named foo, with a method named bar. Here is what it would probably look like.

```PHP

    class foo
    {
      public function bar($querystring, $params_are, $exploded_as_args)
      {
        return srintf('OUTPUT: "%s" "%s" "%s" ', $querystring, $params_are, $exploded_as_args);
        // returns a string that says  'OUTPUT: "whatever" "you" "want"'
      }
    }

```

### Authentication
Controllers must have an authenticate method, which can be inherited from a parent or supplied in a trait, but it is mandated by the interface. Using the accessor keyword `protected` will check against that authentication method which must return a user. The user is then passed as a first argument to the controller method. This varibale can be typechecked, and thus a very simple form or role-based authentication can be designed using traits, inheritance, and different classes for users, otherwise a typeerror exception is thrown and caught when the router does its delegation.

```PHP

    class foo
    {
      protected function bar(\TypeOfUser $user, $querystring, $params, $exploded)
      {
        return srintf('OUTPUT: "%s" "%s" "%s" ', $querystring, $params, $exploded);
        // returns a string that says  'OUTPUT: "whatever" "you" "need"'
      }
    }

```
