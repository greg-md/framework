# Greg Framework

[![StyleCI](https://styleci.io/repos/29315729/shield?style=flat)](https://styleci.io/repos/29315729)
[![Build Status](https://travis-ci.org/greg-md/php-framework.svg)](https://travis-ci.org/greg-md/php-framework)
[![Total Downloads](https://poser.pugx.org/greg-md/php-framework/d/total.svg)](https://packagist.org/packages/greg-md/php-framework)
[![Latest Stable Version](https://poser.pugx.org/greg-md/php-framework/v/stable.svg)](https://packagist.org/packages/greg-md/php-framework)
[![Latest Unstable Version](https://poser.pugx.org/greg-md/php-framework/v/unstable.svg)](https://packagist.org/packages/greg-md/php-framework)
[![License](https://poser.pugx.org/greg-md/php-framework/license.svg)](https://packagist.org/packages/greg-md/php-framework)

Greg Framework provides you a lightweight engine for fast creating powerful apps.

# Table of Contents

* [Requirements](#requirements)
* [Installation](#installation)
* [Quick Start](#quick-start)

# Requirements

* PHP Version `^7.1`

# Installation

`composer require greg-md/php-orm`

# Quick Start

All you need is to instantiate a new application and run it from your preferable environment.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \Greg\Framework\Application();

$app->run(function () {
    echo 'I\'m from inside of an application.' . PHP_EOL;
});
```

Of course the previous example doesn't do nothing special because we didn't initialise and didn't use nothing.
But let's look on the next examples when we want to create an application that should be run in Browser or in Console.

### Running for HTTP Requests

For HTTP Requests we can use the builtin `\Greg\Framework\Http\HttpKernel`
that is working with [HTTP Routes](https://github.com/greg-md/php-router).

In the next example we will instantiate the kernel and create a router that will say hello.

> Providing the `Application` to the `HttpKernel` is optional.
> If you will not provide it, the kernel will instantiate a new application by itself.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$httpKernel = new \Greg\Framework\Http\HttpKernel();

$httpKernel->router()->get('/', function() {
    return 'Hello World!';
});

$httpResponse = $httpKernel->run();

$httpResponse->send();
```

### Running for Console Requests

For Console Requests we can use the builtin `\Greg\Framework\Console\ConsoleKernel`
that is working with [Symfony Console Component](http://symfony.com/doc/current/components/console.html).

In the next example we will instantiate the kernel and create a command that will say hello.

> Providing the `Application` to the `HttpKernel` is optional.
> If you will not provide it, the kernel will instantiate a new application by itself.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$consoleKernel = new \Greg\Framework\Console\ConsoleKernel();

$helloCommand = new Symfony\Component\Console\Command\Command();
$helloCommand->setName('hello');
$helloCommand->setDescription('Say Hello.');
$helloCommand->setCode(function(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output) {
    $output->writeln('Hello World!');
});

$consoleKernel->addCommand($helloCommand);

$responseCode = $consoleKernel->run();

exit($responseCode);
```
