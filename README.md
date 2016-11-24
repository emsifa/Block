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
so you can easily install it without autoloader by following steps below:

* Download this repo or just download raw `src/Block.php`
* Place it somewhere in your project. For example in `yourproject/lib/Block.php`.
* Then include/require it to your code

## Getting Started

#### Preparation

Block using static methods, so instead initialize an object, 
you just need to set class alias and set views directory.

```php
<?php

class_alias('Emsifa\Block', 'Block'); // this is optional, you can aliasing as whatever you want
Block::setDirectory('path/to/views');

```

#### Your first template

Create file `path/to/views/hello.php`.

```php
<h1><?= $title ?></h1>
<p>
	<?= $message ?>
</p>
```

#### Render it

Then somewhere in your code, you can render it with `render` method like this:

```php
echo Block::render('hello', [
	'title' => 'Hello World'
	'message' => 'Lorem ipsum dolor sit amet'
]);
```

Yes. you don't need to put file extension in Block

## Extending and Blocking

There is two main view types in most template engines or template systems. 
Master view, and page view.

Master view is a view that contain base html tags like `<doctype>`, `<html>`, `<head>`, `<body>`, etc.
Page view is a view that `extend` master view and contain some blocks that defined in master view.

> Note: Master view **is not** for rendered by `render` method. Master view is just for extended by some page views.

If you familiar with laravel blade syntax, here are the differences.

| Blade                 | Block                               |
|-----------------------|-------------------------------------|
| @extends('view-name') | <?php Block::extend('view-name') ?> |
| @section('content')   | <?php Block::start('content') ?>    |
| @stop                 | <?php Block::stop() ?>              |
| @show                 | <?php Block::show() ?>              |
| @parent               | <?php Block::parent() ?>            |
| @yield('content')     | <?php echo Block::get('content') ?> |

Here is simple real world case about extending and blocking

#### Create Master View

```html
<!-- Stored in path/to/views/master.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?></title>
  <?= Block::start('stylesheets') ?>
  <link rel="stylesheet" href="bootstrap.css">
  <?= Block::show() ?>
</head>
<body>
  <header>
    <h1>App Name</h1>
  </header>
  <div id="content">
    <?= Block::get('content') ?>
  </div>
  <footer>
    &copy; 2016 - my app
  </footer>
  <?= Block::start('scripts') ?>
  <script src="jquery.js"></script>
  <script src="bootstrap.js"></script>
  <?= Block::show() ?>
</body>
</html>
```

> In example above we use `<?=` instead `<?php` for `Block::start` and `Block::show`. 
  It is ok because those methods doesn't return a value.

#### Create Page View

In master view above, there are block `stylesheets`, `content`, and `scripts`.
So you need to define them in your page view. 

```html
<!-- Stored in path/to/views/pages/lorem-ipsum.php -->
<?= Block::extend('master') ?>

<?= Block::start('stylesheets') ?>
  <?= Block::parent() ?>
  <!-- senpai!! \(^o^) -->
  <link rel="stylesheet" href="lorem.css">
<?= Block::stop() ?>

<?= Block::start('scripts') ?>
  <?= Block::parent() ?>
  <!-- notice me too senpai!! (^o^)/ -->
  <script src="lorem.js"></script>
  <script>
    initPage();
  </script>
<?= Block::stop() ?>

<?= Block::start('content') ?>
<!-- notice me senpai!! \(^o^)/ -->
<p>
  Lorem ipsum dolor sit amet, consectetur adipisicing elit. 
  Officiis, mollitia ad commodi. 
  Eligendi saepe unde iusto quis, praesentium deleniti eos incidunt quas vero, 
  voluptatem, reiciendis inventore, aliquam expedita et rerum.
</p>
<?= Block::stop() ?>
```

> All blocks above are actually optional

#### Render It!

```php
echo Block::render('pages.lorem-ipsum', [
  'Lorem Ipsum'
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

#### Block::insert($view, array $data = array())

There is another view type called _partial view_. 
_Partial view_ is a view file containing partial layout 
that you can use in some _page_ or _master view_ like widget, sidebar, navbar, main-menu, etc.
_Partial view_ is like _master view_, it is not for rendered by `render` method. 
But you can render it by put it in _master_ or _page view_ via `insert` method.

For example, let's create a new _page view_ that contain a widget slider.

First you need to create _partial view_ for widget slider:

```html
<!-- Stored in path/to/views/partials/slider.php -->

<!-- notice me senpai!! \(^o^)/ -->
<div class="widget widget-slider">
  <div class="slider-wrapper">
    <div class="slide-1">Slide 1</div>
    <div class="slide-2">Slide 2</div>
    <div class="slide-3">Slide 3</div>
  </div>
</div>

<?= Block::start('stylesheets') ?>
  <?= Block::parent() ?>
  <!-- senpai!! \(^o^) -->
  <link rel="stylesheet" href="slider.css">
<?= Block::stop() ?>

<?= Block::start('scripts') ?>
  <?= Block::parent() ?>
  <!-- senpai!! (^o^)/ -->
  <script src="slider.js"></script>
<?= Block::stop() ?>

```

Then put it in `home` _page view_.

```html
<!-- Stored in path/to/views/pages/home.php -->
<?= Block::extend('master') ?>

<?= Block::start('stylesheets') ?>
  <?= Block::parent() ?>
  <link rel="stylesheet" href="home.css">
<?= Block::stop() ?>

<?= Block::start('scripts') ?>
  <?= Block::parent() ?>
  <script src="home.js"></script>
  <script>
    initHomepage()
  </script>
<?= Block::stop() ?>

<?= Block::start('content') ?>
<div class="container">
  <?= Block::insert('partials.slider') ?>
  <p>
    Lorem ipsum dolor sit amet, consectetur adipisicing elit. 
    Officiis, mollitia ad commodi. 
    Eligendi saepe unde iusto quis, praesentium deleniti eos incidunt quas vero, 
    voluptatem, reiciendis inventore, aliquam expedita et rerum.
  </p>
</div>
<?= Block::stop() ?>
```
Now if you `echo Block::render('pages.home')`, the output should looks like this:

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

> Note: If you want to use page view data in partial view, you can pass `$__data` in `Block::insert`. 
  For example, slider above would be `Block::insert('partials.slider', $__data)`

#### Add Directory Namespace

You can put second argument in `setDirectory` method to set namespaced directory.

For example, you have module admin that have it's own views directory.

```php
Block::setDirectory('path/to/admin/views', 'admin');

// then you can load master/page/partial view in that directory like this
Block::render('admin::pages.dashboard');
Block::extend('admin::master');
Block::insert('admin::partials.some-chart');
```

#### Set Custom View Extension

Custom extension makes you easier to identify view files in your editor without open that file.
In Block, you can change view extension by `setViewExtension` like example below:

```php
Block::setViewExtension('block.php');
```

Then your view filenames must be suffixed by `.block.php` instead just `.php`


## Dot or Slash?

I love blade for template engine, but I can't always use blade in my project, especially in small projects. 
So I create this library to make it as similar as blade.

In blade you can use `/` or `.` to load view files. So block too. 
But we prefer you to use `.` instead `/` to make you easier remembering 
that you don't need to put view extension in block.
