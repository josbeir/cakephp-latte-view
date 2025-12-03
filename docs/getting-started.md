# Getting started

This guide will walk you through integrating Latte templating engine with your CakePHP application. Before proceeding, we recommend familiarizing yourself with [Latte syntax](https://latte.nette.org/en/syntax) to get the most out of this integration.

## TL;DR

This plugin replaces CakePHP's default templating engine with [Latte](https://latte.nette.org/), a modern and secure template engine from the Nette framework.

**Key differences from CakePHP templates:**
- Uses `.latte` files instead of `.php` templates
- Latte's native features replace some CakePHP view methods:
    - `{include}` and `{block}` replace `$this->element()` and `$this->start()`/`$this->end()`
    - Template inheritance with `{layout}` replaces `$this->extend()`
- `$this->fetch()` is still available to maintain compatibility with existing helpers that manipulate view blocks
- All CakePHP helpers remain fully functional

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
        parent::initialize();

        // Check the configuration page for more details.
        $this->setConfig([]);
    }
}
```

### Layout files

Create a layout template: `templates/layout/default.latte`

```latte
<!DOCTYPE html>
<html lang="en">
<head>
    {Html charset}
    <title>{block title}My CakePHP App{/block}</title>
    {Html meta 'icon'}
    {Html css 'app.css'}
    {fetch meta}
    {fetch css}
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a n:href="[controller: 'Pages', action: 'home']" class="brand">MyApp</a>
        </div>
    </nav>
    
    <main class="container">
        {include content}
    </main>
    
    <footer class="footer">
        {block footer}
            <p>&copy; Copyright {date('Y')} - Built with CakePHP & Latte</p>
        {/block}
    </footer>
    
    {Html script 'app.js'}
    {fetch script}
</body>
</html>
```

Create a child template for your controller action with the default layout `templates/Users/view.latte`:

```latte
{block title}User Profile - {$user->name}{/block}

{block content}
    <div class="user-profile">
        <h1>Welcome, {$user->name}!</h1>
        <p>Email: {$user->email}</p>
        <p>Joined: {$user->created|nice}</p>
        
        {if $user->bio}
            <div class="bio">
                <h3>About</h3>
                <p>{$user->bio|nl2br}</p>
            </div>
        {/if}
    </div>
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
