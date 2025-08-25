# LatteView for CakePHP

[![PHPStan Level 8](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](https://github.com/josbeir/cakephp-latte-view)
[![Build Status](https://github.com/josbeir/cakephp-latte-view/actions/workflows/ci.yml/badge.svg)](https://github.com/josbeir/cakephp-latte-view/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-8.2%2B-blue.svg)](https://www.php.net/releases/8.2/en.php)

A CakePHP plugin providing [Latte](https://latte.nette.org/) template engine integration for CakePHP applications.

## Features

- Use `.latte` templates in your CakePHP app
- Sandbox mode for secure template execution
- Custom CakePHP tags: `{dump}` and `{debug}` override Nette dumper and use CakePHP's Debugger

## Installation

```bash
composer require josbeir/cakephp-latte-view
```

## Usage

Enable the plugin in your `Application.php`:

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

## Accessing the View Object in Templates

The current view object is always available in your Latte templates as `View`. You can use it to access helpers and other view methods:

```latte
{var $html = View->Html->link('Home', '/')}
```

**Note:** If you use helpers this way, remember to disable escaping for the output if they return markup:

```latte
{$View->Html->link('Home', '/')|noescape}
```

## Configuration Options

Set options via `ViewBuilder::setOption()` or `setOptions()`:

| Option         | Type    | Default                | Description                                                                 |
|----------------|---------|------------------------|-----------------------------------------------------------------------------|
| `cache`        | bool    | `true`                 | Enable/disable template caching. Caching is always enabled except when explicitly set to `false`. |
| `autoRefresh`  | bool    | `false` (or `true` in debug) | Automatically refresh templates. Auto-refresh is always enabled in debug mode. |
| `fallbackBlock`| string  | `'content'`            | Block name used when auto layout is disabled                                |
| `cachePath`    | string  | `CACHE . 'latte_view'` | Path for compiled template cache                                            |
| `sandbox`      | bool    | `false`                | Enable sandbox mode for secure template execution. When enabled, the security policy can be configured using `setSandboxPolicy()` and `getSandboxPolicy()`. |

## Custom Tags

- `{dump $var}` or `{debug $var}`: Overrides the default Nette dumper and uses CakePHP's `Debugger::printVar()` for output
- `{dump}`: Dumps all defined variables using CakePHP's dumper

## TODO

CakePHP-specific tags, filters, and functions are in development and will be added in future releases.

## Extending

You can add your own Latte extensions or modify the sandbox policy using:

```php
use Latte\Extension;
use Latte\Sandbox\SecurityPolicy;

$view->getEngine()->addExtension(new YourExtension());
$view->setSandboxPolicy($yourPolicy);
```

## Contributing

Contributions, issues, and feature requests are welcome! Feel free to open a pull request or issue on GitHub. Please follow CakePHP and Latte coding standards and ensure all code is properly tested before submitting.

## License

MIT
