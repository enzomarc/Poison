# Poison

Poison is a simple PHP template engine. Not as powerful as blade but it allows to do basic operations
.

## Installation

    composer require focus237/poison

## Basic usage

Here is a simple example for rendering a view :

    <?php
    
    use Poison\Poison;
    
    $renderer = new Poison('views_path', 'cache_path');
    
    $renderer->render('home');
    
By default the view extension is .poison.php, so the view `home` match the file `home.poison.php`.

## Settings

Here is the default settings of Poison :

`VIEWS_PATH = NULL`

`CACHE_PATH = NULL`

`VIEW_EXTENSION = .poison.php`

You can change these settings with :

    Poison::setViewsPath('views_path');
    
    Poison::setCachePath('cache_path');
    
    Poison::setViewExtension('view_extension');
    
## Global variables

Sometimes you need some parameters or variables to be accessible from your views, you can do this. 
Take our example above :

You can do this by passing your variables in parameters to the `render` function

    <?php
    
    use Poison\Poison;
    
    $renderer = new Poison('views_path', 'cache_path');
    
    $name = "John Doe";
    $items = ["Item 1", "Item 2", "Item 3"];
    
    $renderer->render('home', ['name' => $name, 'items' => $items]);
    
Or you can use the `addGlobal` function :

    <?php
    
    use Poison\Poison;
    
    $renderer = new Poison('views_path', 'cache_path');
    
    $name = "John Doe";
    $items = ["Item 1", "Item 2", "Item 3"];
    
    $renderer->addGlobal('name', $name)->addGlobal('items', $items)->render('home');
    
With the second method, you need to add all your variables before rendering the view.

## Poison/HTML Tags


Poison has a tag system that allows you to perform operations that are not available by default with simple HTML. Here's a list of available tags :

    @include('file_to_include_in_html') // Include some poison file in another
    
    
    @extend('view_to_extend', ['key' => 'value'])   // Extend another view with some parameters 
    
    
    @content
    
        Some HTML ...
        
    @endcontent     // Define the content to pass to a extended view
    
    
    @if(condition)
    
        Some HTML ...
        
    @elseif(condition)
    
        Some HTML ...
        
    @else
    
        Some HTML ...
        
    @endif
    
    
    @foreach($items as $item)
    
        {{ $item }}     // Echo $item
        
    @endforeach