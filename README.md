# LatteView for CakePHP

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
| `helper('HelperName')` | Returns a specific helper instance (e.g., `Html`, `Form`) |
| `url()` | Url generation - See Router::url() |
| `rurl()` | Reverse url generation - See Router::reverse() |
| `__()` `__d()` `__dn()` `__n()` | Cake's translations functions |
| `{link 'title' url options}` | Generate HTML links using CakePHP's HtmlHelper |

```latte
{* Some examples *}

{link 'Click me' '/'}
{view()->getRequest()}
{helper('Html')->link('Home', '/')|noescape}
{url(['controller' => 'Pages', 'action' => 'home'])}
{__('Bonjour')}
```

### Link Tag

Generate HTML links using CakePHP's HtmlHelper:

```latte
{link 'Home', '/'}
```

Equivalent to `$this->Html->link('Home' '/')` in Cake.

For additional options, use named arguments:

```latte
{link 'Profile' url: ['controller' => 'Users', 'action' => 'view', 1] options: ['class' => 'profile-link']}
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
