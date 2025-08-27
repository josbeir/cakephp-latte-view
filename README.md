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

## Configuration Options

Set options via `ViewBuilder::setOption()` or `setOptions()`:

| Option         | Type    | Default                | Description                                                                 |
|----------------|---------|------------------------|-----------------------------------------------------------------------------|
| `cache`        | bool    | `true`                 | Enable/disable template caching. Caching is always enabled except when explicitly set to `false`. |
| `autoRefresh`  | bool    | `false` (or `true` in debug) | Automatically refresh templates. Auto-refresh is always enabled in debug mode. |
| `fallbackBlock`| string  | `'content'`            | Block name used when auto layout is disabled. Unlike CakePHP views, a Latte child template always requires a block definition. When auto layout is disabled, you must specify which block should be rendered by the renderer. |
| `cachePath`    | string  | `CACHE . 'latte_view'` | Path for compiled template cache                                            |
| `sandbox`      | bool    | `false`                | Enable sandbox mode for secure template execution. When enabled, the security policy can be configured using `setSandboxPolicy()` and `getSandboxPolicy()`. |

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
| `__()` `__d()` `__dn()` `__n()` | Cake's translation functions |
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
