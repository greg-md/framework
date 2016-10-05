# Greg PHP Framework

[![StyleCI](https://styleci.io/repos/29315729/shield?style=flat)](https://styleci.io/repos/29315729)
[![Build Status](https://travis-ci.org/greg-md/php-framework.svg)](https://travis-ci.org/greg-md/php-framework)
[![Total Downloads](https://poser.pugx.org/greg-md/php-framework/d/total.svg)](https://packagist.org/packages/greg-md/php-framework)
[![Latest Stable Version](https://poser.pugx.org/greg-md/php-framework/v/stable.svg)](https://packagist.org/packages/greg-md/php-framework)
[![Latest Unstable Version](https://poser.pugx.org/greg-md/php-framework/v/unstable.svg)](https://packagist.org/packages/greg-md/php-framework)
[![License](https://poser.pugx.org/greg-md/php-framework/license.svg)](https://packagist.org/packages/greg-md/php-framework)

**Let's make it happen**

```php
// Start a new application
$app = (new \Greg\Application\Runner($settings = [])->init();

// Run the application
$response = $app->run();

// Send response
$response->send();

```
