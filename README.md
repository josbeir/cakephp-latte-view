# LatteView for CakePHP 🍰

[![PHPStan Level 8](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](https://github.com/josbeir/cakephp-latte-view)
[![Build Status](https://github.com/josbeir/cakephp-latte-view/actions/workflows/ci.yml/badge.svg)](https://github.com/josbeir/cakephp-latte-view/actions)
[![codecov](https://codecov.io/github/josbeir/cakephp-latte-view/graph/badge.svg?token=4VGWJQTWH5)](https://codecov.io/github/josbeir/cakephp-latte-view)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-8.2%2B-blue.svg)](https://www.php.net/releases/8.2/en.php)
[![Packagist Downloads](https://img.shields.io/packagist/dt/josbeir/cakephp-latte-view.svg)](https://packagist.org/packages/josbeir/cakephp-latte-view)

A CakePHP plugin providing [Latte](https://latte.nette.org/) template engine integration for CakePHP applications.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Using the Latte type system](#using-the-latte-type-system)
- [Configuration Options](#configuration-options)
- [Custom Tags and Functions](#custom-tags-and-functions)
- [Extending](#extending)
- [References](#references)
- [Contributing](#contributing)
- [License](#license)

## Features

- Use `.latte` templates in your CakePHP app
- Sandbox mode for secure template execution
- Template names are always relative to `App.path.templates` not the current file
- Plugin templates can be loaded using `@MyPlugin.myTemplate`
- Custom CakePHP tags and functions for seamless integration (see [Custom Tags and Functions](#custom-tags-and-functions))

## Requirements

- **PHP**: 8.2 or higher
- **CakePHP**: 5.x

## Installation

```bash
composer require josbeir/cakephp-latte-view
```

## Usage

Enable the plugin in your `Application.php` or in plugins.php:

```php
use LatteView\LatteViewPlugin;

$this->addPlugin(LatteViewPlugin::class);
```

Extend your AppView:

```php
use LatteView\View\LatteView;

class AppView extends LatteView
{
    public function initialize()
    {
        parent::initialize();,

        // See configuration options below.
        $this->setConfig([]);
    }
}
```

Create a layout template: `templates/layout/default.latte`

```latte
<html lang="en">
<head>
    <title>{block title}{/block}</title>
</head>
<body>
    <div id="content">
        {block content}{/block}
    </div>
    <div id="footer">
        {block footer}&copy; Copyright 2008{/block}
    </div>
</body>
</html>
```

Create a child template for your controller action with the default layout `templates/Controller/action.latte`:

```latte
{block title}My page title{/block}
{block content}
    My page content {$variable}
{/block}
```

Using another layout:
```latte
{layout '/layout/custom'}
...
```

Using a plugin template/layout:
```latte
{layout '@myPlugin./layout/custom'}
...
```

## Using the Latte [type system](https://latte.nette.org/en/type-system)

> **Note:** Using the type system is completely optional. You can continue using traditional variable passing (e.g., `$this->set('variable', $value)`) as you would with any CakePHP view. The type system is an additional feature that enhances IDE support and type safety when desired.

One of the great things about Latte is its integration with various IDEs through the `{templateType}` and `{varType}` tags.

This plugin allows you to pass typed objects to templates, enabling you to utilize this powerful feature for better IDE support and type safety.

To make use of this feature, you need to pass a class that implements `LatteView\View\ParameterInterface`.

First, create a class that implements this interface:

```php
<?php
declare(strict_types=1);

namespace App\View\Parameter;

use LatteView\View\ParameterInterface;

class MyTemplateParams implements ParameterInterface
{
    public function __construct(
        public string $name = 'Default Name',
        public string $additional = 'Default Additional',
        public ?EntityInterface $entity = null,
    ) {
    }
}
```

Now, when passing data to your view (e.g., from inside your controller method), you can pass an instance of this class as an argument. Please note that all other arguments will be ignored as the class instance is the only object passed to your view.

```php
// MyController.php

$entity = $users->get(1);

// Pass data to create an instance
$this->set(MyTemplateParams::class, [
    'name' => 'John',
    'additional' => 'Doe',
    'entity' => $entity,
]);

// Or pass an instance of your ParameterInterface class
$params = new MyTemplateParams(
    name: 'Hello',
    additional: 'World',
    entity: $entity
);

$this->set('parameters', $instance);
```

Then in your template

```latte
{templateType App\View\Parameter\MyTemplateParams}

Name: {$name}
Additional: {$additional}
Entity param: {$entity->id}
```

## Configuration Options

Set options via `ViewBuilder::setOption()` or `setOptions()`:

| Option         | Type    | Default                | Description                                                                 |
|----------------|---------|------------------------|-----------------------------------------------------------------------------|
| `cache`        | bool    | `true`                 | Enable/disable template caching. Caching is always enabled except when explicitly set to `false`. |
| `autoRefresh`  | bool    | `false` (or `true` in debug) | Automatically refresh templates. Auto-refresh is always enabled in debug mode. |
| `blocks`| array  | `['content']`            | Block names that are rendered when autoLayout is disabled. [Read more](#the-blocks-option) |
| `cachePath`    | string  | `CACHE . 'latte_view'` | Path for compiled template cache                                            |
| `sandbox`      | bool    | `false`                | Enable sandbox mode for secure template execution. When enabled, the security policy can be configured using `setSandboxPolicy()` and `getSandboxPolicy()`. |

### The `blocks` option.

The `blocks` option can be used when `autoLayout` is disabled to control which blocks inside the template will be returned. This is particularly useful when working with template fragments, allowing you to render specific parts of a template without the full layout structure.

Imagine you have this template

```latte
<table>
    {block tableRows}
        {foreach $rows as $row}
        <tr>
            <td>Block 1 content</td>
        </tr>
        {/foreach}
    {/block}
</table>

{block content}
    {* other template content that we don't want in our response *}
{/block}

{block otherFragment}
    Block 3 content
{/block}
```

When configuring the ViewBuilder to return only specific blocks, you can generate focused template fragments for partial page updates or AJAX responses:

```php
// Disable autoLayout first
$this->getViewBuilder()->disableAutoLayout();

// Return only table rows for dynamic content updates
$this->getViewBuilder()->setConfig('blocks', ['tableRows']);

// Return multiple fragments for complex partial updates
$this->getViewBuilder()->setConfig('blocks', ['tableRows', 'otherFragment']);
```

This approach is particularly useful for:
- AJAX-powered dynamic content updates
- Optimizing performance by sending only the necessary HTML fragments
- More info [here](https://htmx.org/essays/template-fragments/)

## Custom Tags and Functions

### CakePHP Debug Tags

- `{dump $var}` or `{debug $var}`: Uses CakePHP's `Debugger::printVar()` instead of Nette's default dumper
- `{dump}`: Dumps all defined variables using CakePHP's debugger

### Helper Tags, Filters, Functions

Access CakePHP's view layer from templates:
| Function | Description |
|----------|-------------|
| `view()` | Returns the current View instance |
| `request()`| Returns the current request instance |
| `url()` | Url generation - See Router::url() |
| `rurl()` | Reverse url generation - See Router::reverse() |
| `{link 'title' url options}` | Generate HTML links using CakePHP's HtmlHelper |
| `{cHelperName method arg1, arg2}` | Access any CakePHP helper using the `c` (🍰)  prefix followed by the helper name |

### CakePHP helpers

All CakePHP helpers are automatically available as Latte tags using the `{c[HelperName] ...}` syntax:

> **Note:** Latte comes with a comprehensive list of functions and filters, making many CakePHP helper functions possibly obsolete. Using Latte's built-in functionality is preferred. Check the [filter](https://latte.nette.org/en/filters), [function](https://latte.nette.org/en/functions) and [tags](https://latte.nette.org/en/tags) documentation for what is available out of the box.

```latte
{* Html helper examples *}
{cHtml link 'My link', '/'}
{cHtml link 'My link', ['controller' => 'Pages', 'action' => 'home']}
{cHtml css 'style.css'}
{cHtml script 'app.js'}

{* Form helper examples *}
{cForm create}
{cForm control 'title'}
{cForm button 'Submit'}
{cForm end}

{* Text helper examples *}
{cText truncate $longText, 100}
{cText excerpt $text, 'keyword', 50}

{* Number helper examples *}
{cNumber currency $price, 'USD'}
{cNumber format $number, 2}

...
```

Be sure to [add your helpers](https://book.cakephp.org/5/en/views/helpers.html#configuring-helpers) in your view to make them available. By default, only CakePHP's core helpers are automatically loaded.

### I18n functionality

The plugin provides seamless integration with CakePHP's I18n system through Latte's built-in translation tags and filters:

> **Note:** Translation functions use the `sprintf` formatter by default to prevent conflicts with Latte's syntax. This differs from CakePHP's default ICU formatter. See [CakePHP's I18n documentation](https://book.cakephp.org/5/en/core-libraries/internationalization-and-localization.html#using-different-formatters) for more details on formatters.

```latte
{* Basic translation *}
{_'Hello, World!'}
{'Welcome back'|translate}

{* Translation with domain *}
{_'Admin Panel', domain: 'admin'}

{* Translation with placeholders *}
{translate $username, $email}Welcome %s, your email %s has been verified{/translate}

{* Pluralization *}
{translate $count, singular: '%s item'}%s items{/translate}
{_'%s items', $count, singular: '%s item'}
```

All translation calls automatically use CakePHP's `__()`, `__d()`, `__n()`, and related I18n functions under the hood, ensuring full compatibility with your existing translation workflow and message files.

### Other examples

```latte
{* Some examples *}

{link 'Click me' '/'}
{view()->viewMethod()}
{request()->getQuery('search)}
{url(['controller' => 'Pages', 'action' => 'home'])}
{__('Bonjour')}
```

## Extending

You can add your own Latte extensions or modify the sandbox policy using:

```php
use Latte\Extension;
use Latte\Sandbox\SecurityPolicy;

$view->getEngine()->addExtension(new YourExtension());
$view->setSandboxPolicy($yourPolicy);
```

## References

- [Latte Official Website](https://latte.nette.org/)
- [Latte Documentation](https://latte.nette.org/en/guide)

## Contributing

Contributions, issues, and feature requests are welcome! Feel free to open a pull request or issue on GitHub. Please follow CakePHP and Latte coding standards and ensure all code is properly tested before submitting.

## License

[MIT](LICENSE.md)
