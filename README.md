Block - PHP Native Template System
===========================================

[![Build Status](https://img.shields.io/travis/emsifa/Block.svg?style=flat-square)](https://travis-ci.org/emsifa/Block)
[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://doge.mit-license.org)

Block is PHP native template system inspired by Laravel Blade.
Block is not template language, so block doesn't need to be compiled and cached like blade, twig, smarty, etc.

## Requirements

Block requires PHP 5.5 or greater

## Installation

#### With Composer

If your project using composer, you can install Block via composer by typing this command:

```
composer require "emsifa/block"
```

#### Without Composer

Block is single file library, 
so you can easily install it without any autoloader by following steps below:

* Download this repo or just download raw `src/Block.php`
* Place it somewhere in your project. For example in `yourproject/lib/Block.php`.
* Then include/require it to your code

## Getting Started

#### Initialize Block

```php
<?php

use Emsifa\Block;

$view_dir = __DIR__.'/app/views';
$view_extension = 'block.php';

$block = new Block($view_dir, $view_extension);
```

By default `$view_extension` is `php`. We prefer to use custom extension.
Custom extension make you easier to identify view files in your editor without open that file.

In this examples we use `block.php`, so our view filenames must be suffixed by `.block.php` instead just `.php`.

#### Your first template

Create file `path/to/views/hello.block.php`.

```php
<h1><?= $title ?></h1>
<p>
	<?= $message ?>
</p>
```

#### Render it

Then somewhere in your code, you can render it with `render` method like this:

```php
echo $block->render('hello', [
	'title' => 'Hello World'
	'message' => 'Lorem ipsum dolor sit amet'
]);
```

Yes. you don't need to put file extension in Block.

## Extending and Blocking

Practically, there is two main view types in most template engines or template systems. 
_Master view_ and _page view_.

_Master view_ is a view that contain base html tags like `<doctype>`, `<html>`, `<head>`, `<body>`, etc.
_Page view_ is a view that `extend` _master view_ and contain some blocks that defined in _master view_.

> Note: _Master view_ **is not** for rendered by `render` method. _Master view_ is just for extended by any _page views_.

If you familiar with laravel blade syntax, here are the differences.

| Blade                 | Block                               |
|-----------------------|-------------------------------------|
| @extends('view-name') | <?php $this->extend('view-name') ?> |
| @section('content')   | <?php $this->section('content') ?>  |
| @stop                 | <?php $this->stop() ?>              |
| @show                 | <?php $this->show() ?>              |
| @parent               | <?php $this->parent() ?>            |
| @yield('content')     | <?php echo $this->get('content') ?> |

Here is simple real world case about extending and blocking

#### Create Master View

```html
<!-- Stored in path/to/views/master.block.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?></title>
  <?= $this->section('stylesheets') ?>
  <link rel="stylesheet" href="bootstrap.css">
  <?= $this->show() ?>
</head>
<body>
  <header>
    <h1>App Name</h1>
  </header>
  <div id="content">
    <?= $this->get('content') ?>
  </div>
  <footer>
    &copy; 2016 - my app
  </footer>
  <?= $this->section('scripts') ?>
  <script src="jquery.js"></script>
  <script src="bootstrap.js"></script>
  <?= $this->show() ?>
</body>
</html>
```

> In example above we use `<?=` instead `<?php` for `$this->section` and `$this->show`. 
  It is ok because those methods doesn't return a value.

#### Create Page View

In master view above, there are block `stylesheets`, `content`, and `scripts`.
So you need to define them in your page view. 

```html
<!-- Stored in path/to/views/pages/lorem-ipsum.block.php -->
<?= $this->extend('master') ?>

<?= $this->section('stylesheets') ?>
  <?= $this->parent() ?>
  <!-- senpai!! \(^o^) -->
  <link rel="stylesheet" href="lorem.css">
<?= $this->stop() ?>

<?= $this->section('scripts') ?>
  <?= $this->parent() ?>
  <!-- notice me too senpai!! (^o^)/ -->
  <script src="lorem.js"></script>
  <script>
    initPage();
  </script>
<?= $this->stop() ?>

<?= $this->section('content') ?>
<!-- notice me senpai!! \(^o^)/ -->
<p>
  Lorem ipsum dolor sit amet, consectetur adipisicing elit. 
  Officiis, mollitia ad commodi. 
  Eligendi saepe unde iusto quis, praesentium deleniti eos incidunt quas vero, 
  voluptatem, reiciendis inventore, aliquam expedita et rerum.
</p>
<?= $this->stop() ?>
```

> All blocks above are actually optional

#### Render It!

```php
echo $block->render('pages.lorem-ipsum', [
  'title' => 'Lorem Ipsum'
]);
```

And the result should looks like this

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lorem Ipsum</title>
  <link rel="stylesheet" href="bootstrap.css">
  <!-- senpai!! \(^o^) -->
  <link rel="stylesheet" href="lorem.css">
</head>
<body>
  <header>
    <h1>App Name</h1>
  </header>
  <div id="content">
    <!-- notice me senpai!! \(^o^) -->
    <p>
      Lorem ipsum dolor sit amet, consectetur adipisicing elit. 
      Officiis, mollitia ad commodi. 
      Eligendi saepe unde iusto quis, praesentium deleniti eos incidunt quas vero, 
      voluptatem, reiciendis inventore, aliquam expedita et rerum.
    </p>
  </div>
  <footer>
    &copy; 2016 - my app
  </footer>
  <script src="jquery.js"></script>
  <script src="bootstrap.js"></script>
  <!-- notice me too senpai!! (^o^)/ -->
  <script src="lorem.js"></script>
  <script>
    initPage();
  </script>
</body>
</html>
```

## Another Useful Stuffs

#### HTML Escaping

Like another template engine, Block also have shortcuts for escaping HTML. 
In Block you can escaping HTML using `$this->escape($html)` or `$e($html)`.

Example:

Render
```php
$block->render('pages/sample-escaping', [
  'title' => 'Title <script>XSS.attack()</script>'
]);
```

View
```html
<!-- Stored in path/to/views/pages/sample-escaping.block.php -->
<div>
  <h4><?= $e($title) ?></h4>
</div>
```

Then, title will be escaped like this:
```php
<div>
  <h4>Title &lt;script&gt;XSS.attack()&lt;/script&gt;</h4>
</div>
```

#### $get($key, $default = NULL)

When rendering a view, we add variable `$get` that contain anonymous function.
This function allows you to get a value passed by `render` method. 
If the key exists, it will return that value, 
and if not it will return default value (NULL).

For example in master view above, if you didn't set `title` in array, it will show an error undefined variable title.
So to fix that, instead using `isset` like this

```php
<title><?= isset($title) ? $title : 'Default Title' ?></title>
```

You can use `$get` like this:

```php
<title><?= $get('title', 'Default Title') ?></title>
```

Note: `$get` also support dot notation. It mean, you can access array using dot as separator in `$key`. 

For example you render a view with array data like this:
```php
$block->render('pages/profile', [
  'user' => [
    'name' => 'John Doe'
  ]
])
```

You can use `$get` like this:

```html
<div class='profile'>
  Name: <?= $get('user.name') ?>
  City: <?= $get('user.city.name', 'Unknown') ?> 
</div>
```

In example above `user.city.name` will return 'Unknown' because you didn't set `city` in array `user`.

#### Include Partial View

There is another view type called _partial view_. 
_Partial view_ is a view file containing partial layout 
that you can use in some _page_ or _master view_ like widget, sidebar, navbar, main-menu, etc.
_Partial view_ is like _master view_, it is not for rendered by `render` method. 
But you can render it by put it inside _master_ or _page view_ via `insert` method.

For example, let's create a new _page view_ that contain a widget slider.

First you need to create _partial view_ for widget slider:

```html
<!-- Stored in path/to/views/partials/slider.block.php -->

<!-- notice me senpai!! \(^o^)/ -->
<div class="widget widget-slider">
  <div class="slider-wrapper">
    <div class="slide-1">Slide 1</div>
    <div class="slide-2">Slide 2</div>
    <div class="slide-3">Slide 3</div>
  </div>
</div>

<?= $this->section('stylesheets') ?>
  <?= $this->parent() ?>
  <!-- senpai!! \(^o^) -->
  <link rel="stylesheet" href="slider.css">
<?= $this->stop() ?>

<?= $this->section('scripts') ?>
  <?= $this->parent() ?>
  <!-- senpai!! (^o^)/ -->
  <script src="slider.js"></script>
<?= $this->stop() ?>

```

Then you can include it in `home` _page view_ using `insert` method.

```html
<!-- Stored in path/to/views/pages/home.block.php -->
<?= $this->extend('master') ?>

<?= $this->section('stylesheets') ?>
  <?= $this->parent() ?>
  <link rel="stylesheet" href="home.css">
<?= $this->stop() ?>

<?= $this->section('scripts') ?>
  <?= $this->parent() ?>
  <script src="home.js"></script>
  <script>
    initHomepage()
  </script>
<?= $this->stop() ?>

<?= $this->section('content') ?>
<div class="container">
  <?= $this->insert('partials.slider') ?>
  <p>
    Lorem ipsum dolor sit amet, consectetur adipisicing elit. 
    Officiis, mollitia ad commodi. 
    Eligendi saepe unde iusto quis, praesentium deleniti eos incidunt quas vero, 
    voluptatem, reiciendis inventore, aliquam expedita et rerum.
  </p>
</div>
<?= $this->stop() ?>
```
Now if you `echo $block->render('pages.home')`, the output should looks like this:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Default Title</title>
  <link rel="stylesheet" href="bootstrap.css">
  <!-- senpai!! \(^o^) -->
  <link rel="stylesheet" href="slider.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>
  <header>
    <h1>App Name</h1>
  </header>
  <div id="content">
    <div class="container">
      <!-- notice me senpai!! \(^o^)/ -->
      <div class="widget widget-slider">
        <div class="slider-wrapper">
          <div class="slide-1">Slide 1</div>
          <div class="slide-2">Slide 2</div>
          <div class="slide-3">Slide 3</div>
        </div>
      </div>
      <p>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. 
        Officiis, mollitia ad commodi. 
        Eligendi saepe unde iusto quis, praesentium deleniti eos incidunt quas vero, 
        voluptatem, reiciendis inventore, aliquam expedita et rerum.
      </p>
    </div>
  </div>
  <footer>
    &copy; 2016 - my app
  </footer>
  <script src="jquery.js"></script>
  <script src="bootstrap.js"></script>
  <!-- senpai!! (^o^)/ -->
  <script src="slider.js"></script>
  <script src="home.js"></script>
  <script>
    initHomepage();
  </script>
</body>
</html>
```

Notice: `slider.css` and `slider.js` are placed in that order.

> Note: If you want to use page view data in partial view, you can pass `$__data` in `$this->insert`. 
  For example, slider above would be `$this->insert('partials.slider', $__data)`

## Add Directory Namespace

You can put second argument in `setDirectory` method to set namespaced directory.

For example, you have module admin that have its own views directory.

```php
$block->setDirectory('path/to/admin/views', 'admin');

// then you can render it like this
$block->render('admin::pages.dashboard');

// and extend or insert something in your view files like this
$this->extend('admin::master');
$this->insert('admin::partials.some-chart');
```

## View Composer

We have told you that Block is inspired by Blade right. So Block also have view composer like blade.

Sometimes you may have a view partial that have its own data. 
For example, think about navbar. In navbar, you want to display logged user name.
So basically you need to pass data user name in all views who rendering that navbar.
Alternatively, you may set user data inside navbar view. 
But set data inside view file is a bad practice.

So, the solution is using view composer. 
With composer, you can add some data to view before rendering that view.

Here is an example for that case:

First you need to register view composer for navbar using `composer` method.

```php
$block->composer('partials.navbar', function($data) {
    // $data is data you passed into `render` or `insert` method
    return [
        'username' => Auth::user()->username
    ];
});
```

Then in your navbar, you can do this

```html
<!-- Stored in path/to/views/partials/navbar.block.php -->
<nav>
  <li>Some menu</li>
  ...
  <li>
    <?= $username ?>
  </li>
</nav>
```

So now, whenever navbar is rendered, composer will set variable `username` to it.

> You can set first argument as array if you wanna set a composer to multiple views.

## Component and Slot

This is new feature in Laravel 5.4 which inspired by Vue.js. 
Sometimes you may have partial view containing dynamic HTML.
With `insert` method, you can add HTML string as data for second argument.
But put HTML code in string is bad practice, most text editors cannot highlight it.

So, this features allows you to write HTML that will be transformed to variable in partial view.

Think about alert, you may have alert which contain dynamic HTML inside it like this:

```php
<!-- Stored in path/to/views/partials/alert.block.php -->
<div class="alert">
  <h4><?= $title ?></h4>
  <?= $slot ?>
</div>
```

With `insert` method, you need to pass `slot` and `title` variables like this:

```php
<?php 

$this->insert('partials.alert', [
    'title' => 'Validation Errors <strong class="close">&times;</strong>',
    'slot' => '
      <ul>
        <li>Email is required</li>
        <li>Password is required</li>
      </ul>
    '
]);

?>
```

It is ugly to put HTML inside string like that. 
With component and slot, you can insert `alert` view like this:


```php
<?= $this->component('partials.alert') ?>
  <?= $this->slot('title') ?>
    Validation Errors <strong class="close">&times;</strong>
  <?= $this->endslot() ?>
  <ul>
    <li>Email is required</li>
    <li>Password is required</li>
  </ul>
<?= $this->endcomponent() ?>
```

Now code inside `component` will transformed into `slot` variable,
and code inside `slot('title')` will transformed into `title` variable.

> You can pass array view data as second argument in `component` method. 

## Dot or Slash?

I love blade for template engine, but I can't always use blade in my project, especially in small projects. 
So I create this library to make it as similar as blade.

In blade you can use `/` or `.` to load view files. So block too. 
But we prefer you to use `.` instead `/` to make you easier remembering 
that you don't need to put view extension in block.
