# Getting started

This guide will walk you through integrating Latte templating engine with your CakePHP application.

## Requirements

- **PHP**: 8.2 or higher
- **CakePHP**: 5.x
- **DebugKit**: 5.2 or higher (development dependency)

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

### Layout files

Create a layout template: `templates/layout/default.latte`

```latte
<html lang="en">
<head>
    <title>{block title}Default title{/block} - My cool app</title>
</head>
<body>
    <div id="content">
        {include content}
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
{layout '@myPlugin./Pages/index'}
...
```