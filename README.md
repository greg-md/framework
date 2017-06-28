# Greg Framework

[![StyleCI](https://styleci.io/repos/29315729/shield?style=flat)](https://styleci.io/repos/29315729)
[![Build Status](https://travis-ci.org/greg-md/php-framework.svg)](https://travis-ci.org/greg-md/php-framework)
[![Total Downloads](https://poser.pugx.org/greg-md/php-framework/d/total.svg)](https://packagist.org/packages/greg-md/php-framework)
[![Latest Stable Version](https://poser.pugx.org/greg-md/php-framework/v/stable.svg)](https://packagist.org/packages/greg-md/php-framework)
[![Latest Unstable Version](https://poser.pugx.org/greg-md/php-framework/v/unstable.svg)](https://packagist.org/packages/greg-md/php-framework)
[![License](https://poser.pugx.org/greg-md/php-framework/license.svg)](https://packagist.org/packages/greg-md/php-framework)

Greg Framework provides you a lightweight engine for fast creating powerful apps.

[Greg PHP Application](https://github.com/greg-md/php-app) is a prepared and deploy-ready application that you can use
to achieve maximum productivity of that framework.

# Table of Contents

* [Requirements](#requirements)
* [Installation](#installation)
* [How It Works](#how-it-works)
* [Bootstrapping](#bootstrapping)
* [Events](#events)
    * [Listeners](#listeners)
    * [Fire Events](#fire-events)
    * [Events Objects](#events-objects)
    * [Builtin Events](#builtin-events)
* [License](#license)
* [Huuuge Quote](#huuuge-quote)

# Requirements

* PHP Version `^7.1`

# Installation

`composer require greg-md/php-framework`

# How It Works

All you need is to instantiate a new Application and run it from your chosen environment.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \Greg\Framework\Application();

$app->run(function () {
    echo 'Hello World!';
});
```

You can also construct the Application with custom configs and an [IoC Container](https://github.com/greg-md/php-dependency-injection).

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$config = new \Greg\Framework\Config([
    'name' => 'John',
]);

$ioc = new \Greg\DependencyInjection\IoCContainer();

$ioc->register($config);

$app = new \Greg\Framework\Application($config, $ioc);

$app->run(function (\Greg\Framework\Config $config) {
    echo 'Hello ' . $config['name'] . '!';
});
```

Yeah, the previous example practically doesn't do nothing because you practically didn't use any features.
But let's look on the next examples when we want to create an Application
that should be run in [Browser](#running-for-http-requests) or in [Console](#running-for-console-requests).

### Running for HTTP Requests

For HTTP Requests we can use the builtin `\Greg\Framework\Http\HttpKernel`
that is working with [HTTP Routes](https://github.com/greg-md/php-router).

In the next example we will instantiate the kernel and create a router that will say hello.

> Providing the Application and Router to the kernel is optional.
> If you will not provide them, the kernel will instantiate them by itself.

```php
$httpKernel = new \Greg\Framework\Http\HttpKernel($app);

$router = $httpKernel->router();

$router->get('/', function() {
    return 'Hello World!';
});

$httpResponse = $httpKernel->run();

$httpResponse->send();
```

### Running for Console Requests

For Console Requests we can use the builtin `\Greg\Framework\Console\ConsoleKernel`
that is working with [Symfony Console Component](http://symfony.com/doc/current/components/console.html).

In the next example we will instantiate the kernel and create a command that will say hello.

> Providing the Application and Symfony Console Application to the kernel is optional.
> If you will not provide them, the kernel will instantiate them by itself.

```php
$consoleKernel = new \Greg\Framework\Console\ConsoleKernel($app);

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

# Bootstrapping

To keep the consistency of the application, you can define/register/load it's components into the bootstrap classes.

A bootstrap class should be an instance of `\Greg\Framework\BootstrapStrategy` that requires the `boot` method.

```php
class AppBootstrap extends \Greg\Framework\BootstrapAbstract
{
    public function boot(\Greg\Framework\Application $app)
    {
        $app->inject('redis.client', function() {
            return new Redis();
        });
    }
}
```

For a better experience you can extend the `\Greg\Framework\BootstrapAbstract` class.
It will allow you to define multiple boots in a specific order.
Each method in this class that starts with `boot` in a `lowerCamelCase` format will be called. 

```php
class AppBootstrap extends \Greg\Framework\BootstrapAbstract
{
    public function bootFoo()
    { 
        $this->dependsOn('redis'); // Call `bootRedis` method first.

        $redis = $this->app()->get('redis.client');

        // ...
    }

    public function bootRedis()
    {
        $redis = new Redis();

        $this->app()->inject('redis.client', $redis);
    }
}
```

***Next***, you have to add the bootstrap to the application.

```php
$app->addBootstrap(new AppBootstrap());
```

The same way you can define and create bootstraps only for the HTTP and Console kernels,
using the `Greg\Framework\Console\BootstrapStrategy` and `Greg\Framework\Http\BootstrapStrategy` strategies,
or extending the `Greg\Framework\Console\BootstrapAbstract` and `Greg\Framework\Http\BootstrapAbstract` classes.

```php
$httpKernel->addBootstrap(new AppHttpBootstrap());
```

```php
$consoleKernel->addBootstrap(new AppConsoleBootstrap());
```

# Events

You can define and fire events in your application.

### Listeners

Listeners could be a callable, an object or a class name.

```php
$app->listen('my.event', function() {
    echo "Event is fired.";
});
```

Objects and class names requires the `handle` method that will be fired.

```php
class MyListener
{
    public function handle() {
        echo "Event is fired.";
    }
}
```

```php
$app->listen('my.event', MyListener::class);
// or
$app->listen('my.event', new MyListener());
```

### Fire Events

You can fire events with or without custom arguments.

```php
$app->fire('my.event');

// or

$user = new User();

$app->fire('my.event', $user);

// or

$app->fireArgs('my.event', [new User()]);
```

### Events Objects

Following the best practices, you can define events as objects.

Let say we have a `LoginEvent` class that requires an `User`.

```php
class LoginEvent
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
```

You can define listeners using the class name as event name.

```php
$app->listen(LoginEvent::class, function(LoginEvent $event) {
    // do the bussiness logic...
});
```

And fire them in application.

```php
$app->event(new LoginEvent($user));
// the same as
$app->fire(LoginEvent::class, ...[new LoginEvent($user)]);
```

### Builtin Events

#### Application

##### `\Greg\Framework\Application::EVENT_RUN`

This event is fired before the Application is run.

###### `\Greg\Framework\Application::EVENT_FINISHED`

This event is fired after the Application is run.

#### **HTTP Kernel**

###### `\Greg\Framework\Http\HttpKernel::EVENT_RUN`

This event is fired before the Http Kernel is run.

###### `\Greg\Framework\Http\HttpKernel::EVENT_DISPATCHING`

This event is fired before the Http Kernel Route is dispatched.

###### `\Greg\Framework\Http\HttpKernel::EVENT_DISPATCHED`

This event is fired after the Http Kernel Route is dispatched.

###### `\Greg\Framework\Http\HttpKernel::EVENT_FINISHED`

This event is fired after the Http Kernel is run.

#### **Console Kernel**

###### `\Greg\Framework\Console\ConsoleKernel::EVENT_RUN`

This event is fired before the Console Kernel is run.

###### `\Greg\Framework\Console\ConsoleKernel::EVENT_FINISHED`

This event is fired after the Console Kernel is run.

# License

MIT © [Grigorii Duca](http://greg.md)

# Huuuge Quote

![I fear not the man who has practiced 10,000 programming languages once, but I fear the man who has practiced one programming language 10,000 times. &copy; #horrorsquad](http://greg.md/huuuge-quote-fb.jpg)
