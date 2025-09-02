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
    - [The `blocks` option](#the-blocks-option)
- [Custom Tags, Functions and Filters](#custom-tags-functions-and-filters)
    - [Introduction](#introduction)
    - [Helpers](#helpers)
    - [Links](#links)
    - [Forms](#forms)
    - [I18n](#i18n)
    - [Debug Tags](#debug-tags)
    - [Filters](#filters)
    - [Other examples](#other-examples)
- [Using the Latte type system](#using-the-latte-type-system)
- [Console commands](#console-commands)
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
...
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
| `rawphp` | bool | `true` | Enable/disable the use of raw PHP code in templates via the [{php} tag](https://latte.nette.org/en/develop#toc-rawphpextension). |
| `defaultHelpers` | array | ... | List of default Cake helpers that need to be present. Defaults to all core helpers. |

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
$this->viewBuilder()->disableAutoLayout();

// Return only table rows for dynamic content updates
$this->viewBuilder()->setConfig('blocks', ['tableRows']);

// Return multiple fragments for complex partial updates
$this->viewBuilder()->setConfig('blocks', ['tableRows', 'otherFragment']);
```

This approach is particularly useful for:
- AJAX-powered dynamic content updates
- Optimizing performance by sending only the necessary HTML fragments
- More info [here](https://htmx.org/essays/template-fragments/)

## Custom Tags, Functions and Filters

### Introduction

The plugin comes with some handy functions and tags:

| Function | Description |
|----------|-------------|
| `view()` | Returns the current View instance. |
| `request()`| Returns the current request instance. |
| `env()` | Access environment variables |
| `url()` | Url generation - See `Router::url()`. |
| `rurl()` | Reverse url generation - See `Router::reverse()`. |
| `{fetch 'name'}`| Cake's `View::fetch()` method, introduced to keep legacy functionality of helpers that use view blocks.
| `{cell name}` | Cake's `View::cell()` method
| `{HelperName method arg1, arg2}` | Access any CakePHP helper using the helper name followed by its methodname args. (See docs below) |
| `helper('Html')` | Returns a helper instance object. Depending on your needs you can decide to use the function or the tag. |
| `n:href`, `n:named`, `n:context` | `n:attribute` access for links and form building. (See docs below)

### Helpers

All CakePHP helpers are automatically available as Latte tags using the `{HelperName method param, ...}` syntax. Be sure to always check that your name does not clash with other Latte tags:

> **Note:** Latte comes with a comprehensive list of functions and filters, making many CakePHP helper functions possibly obsolete. Using Latte's built-in functionality is preferred. Check the [filter](https://latte.nette.org/en/filters), [function](https://latte.nette.org/en/functions) and [tags](https://latte.nette.org/en/tags) documentation for what is available out of the box.

```latte
{* Html helper examples *}
{Html link 'My link', '/'}
{Html link 'My link', ['controller' => 'Pages', 'action' => 'home']}
{Html css 'style.css'}
{Html script 'app.js'}

{* Form helper examples *}
{Form create}
{Form control 'title'}
{Form button 'Submit'}
{Form end}

{* Text helper examples *}
{Text truncate $longText, 100}
{Text excerpt $text, 'keyword', 50}

{* Number helper examples *}
{Number currency $price, 'USD'}
{Number format $number, 2}

{* Access a helper using the helper() function *}
{var $first_name = helper('Identity')->get('first_name')}
{_'Hello %s !', $first_name}
...
```

Be sure to [add your helpers](https://book.cakephp.org/5/en/views/helpers.html#configuring-helpers) in your view to make them available. By default, only CakePHP's core helpers are automatically loaded.

### Links

The plugin provides convenient link building functionality through both traditional function calls and Latte's elegant `n:href` and `n:named` attributes:

**Function-based links:**
```latte
{* Generate URL strings *}
{link '/'} {* Outputs / *}
{link ['controller' => 'Posts', 'action' => 'view', 1]}
{link ['_name' => 'posts:view', 1], full: true}
```

**n:href attribute for automatic link generation:**
The `n:href` attribute automatically converts any element into a properly formatted link with the correct `href` attribute:

**n:named attribute for named routes:**
Use `n:named` to reference named routes defined in your routes configuration:

```latte
<a n:href="/">Simple route</a>
<a n:href="[controller: 'Pages', action: 'display']">Cake route using an array</a>
<a n:href="/, full: true">Route with full base url</a>

<a n:named="display">Named route</a>
<a n:named="user:index, $argument">Named route with argument</a>
<a n:named="user:view, $argument, '?' => ['page' => 1]">Named route with argument and query params</a>
```

### Forms

This plugin provides enhanced form handling through both the traditional `{Form}` helper integration and `n:attribute` attributes for more streamlined form creation.

**Tag style Form Helper:**
```latte
{Form create $user}
{Form control 'first_name'}
{Form control 'last_name'}
{Form submit}
{Form end}
```

**n:attribute style forms:**
The `n:context` and `n:name` attributes provide a more elegant way to create forms by automatically handling form creation and context binding:

```latte
{* Basic usage with automatic form creation *}
<form n:context="$user">
    <input n:name="email">
    <control n:name="username" /> {* Please note that custom elements do not self-close, make sure to close them using / or </control> *}
    <control n:name="user.company.name" label="Company name" />
    <select n:name="options" options="[1,2,3]" />
    <label n:name="description">Description</label>
    <textarea n:name="description"></textarea>
    <button type="submit">Save</button> {* or {Form submit} *}
</form>

{* Pass additional options via HTML attributes *}
<form n:context="$user" type="file" url="['_name' => 'display']" class="my-form">
    <control n:name="file">
    <button type="submit">{_'Upload'}</button>
</form>
```

> **Note:** All HTML attributes passed to the form element when using `n:context` are automatically passed to the `options` array of `FormHelper::create()`. This allows you to set any form options using standard HTML attribute syntax. Additionally, automatic detection of the value type is performed. For more complex controls, tag style form building may be preferred.


### I18n

The plugin provides seamless integration with CakePHP's I18n system through Latte's built-in translation tags and filters:

**Tokens:** By default, [CakePHP uses the ICU formatter](https://book.cakephp.org/5/en/core-libraries/internationalization-and-localization.html#using-different-formatters) to handle tokens in its translation functions. Although this works when using `{_'Hello {0}', 'world'}`, it would clash with the Latte pattern when doing `{translate 'world'}Hello {0}{/translate}`. It is therefore recommended to use the sprintf formatter which you can enable by setting the following in your application (bootstrap for instance).

```php
I18n::setDefaultFormatter('sprintf');
```

Examples:
```latte
{* Basic translation *}
{_'Hello, World!'}
{'Welcome back'|translate}

{* Translation with domain *}
{_'Admin Panel', domain: 'admin'}

{* Translation with tokens *}
{_'Hello from %s', 'Brussels'}
{translate $username, $email}Welcome %s, your email %s has been verified{/translate}

{* Pluralization *}
{translate $count, singular: '%s item'}%s items{/translate}
{_'%s items', $count, singular: '%s item'}
```

All translation calls automatically use CakePHP's I18n functions under the hood, ensuring full compatibility with your existing translation workflow and message files. 

Please note that no __x() related functions are implemented.

### Debug Tags

- `{dump $var}` or `{debug $var}`: Uses CakePHP's `Debugger::printVar()` instead of Nette's default dumper
- `{dump}`: Dumps all defined variables using CakePHP's debugger

### Filters

The following filters are mapped to their CakePHP counterparts, providing integration with CakePHP's utility classes for text manipulation, number formatting, and inflector operations.

| Function          | Maps to                          |
|-------------------|----------------------------------|
| **Text** |
| uuid              | Cake\Utility\Text::uuid          |
| tokenize          | Cake\Utility\Text::tokenize      |
| insert            | Cake\Utility\Text::insert        |
| cleanInsert       | Cake\Utility\Text::cleanInsert   |
| wrap              | Cake\Utility\Text::wrap          |
| wrapBlock         | Cake\Utility\Text::wrapBlock     |
| wordWrap          | Cake\Utility\Text::wordWrap      |
| highlight         | Cake\Utility\Text::highlight     |
| tail              | Cake\Utility\Text::tail          |
| truncateByWidth   | Cake\Utility\Text::truncateByWidth |
| excerpt           | Cake\Utility\Text::excerpt       |
| toList            | Cake\Utility\Text::toList        |
| utf8              | Cake\Utility\Text::utf8          |
| ascii             | Cake\Utility\Text::ascii         |
| parseFileSize     | Cake\Utility\Text::parseFileSize |
| transliterate     | Cake\Utility\Text::transliterate |
| slug              | Cake\Utility\Text::slug          |
| precision         | Cake\I18n\Number::precision      |
| toReadableSize    | Cake\I18n\Number::toReadableSize |
| toPercentage      | Cake\I18n\Number::toPercentage   |
| parseFloat        | Cake\I18n\Number::parseFloat     |
| formatDelta       | Cake\I18n\Number::formatDelta    |
| currency          | Cake\I18n\Number::currency       |
| formatter         | Cake\I18n\Number::formatter      |
| ordinal           | Cake\I18n\Number::ordinal        |
| **Inflector** |
| pluralize         | Cake\Utility\Inflector::pluralize |
| singularize       | Cake\Utility\Inflector::singularize |
| camelize          | Cake\Utility\Inflector::camelize |
| underscore        | Cake\Utility\Inflector::underscore |
| dasherize         | Cake\Utility\Inflector::dasherize |
| humanize          | Cake\Utility\Inflector::humanize |
| delimit           | Cake\Utility\Inflector::delimit  |
| tableize          | Cake\Utility\Inflector::tableize |
| classify          | Cake\Utility\Inflector::classify |
| iVariable         | Cake\Utility\Inflector::variable |
| **Time** |
| format            | Cake\View\Helper\TimeHelper::format           |
| i18nFormat        | Cake\View\Helper\TimeHelper::i18nFormat       |
| nice              | Cake\View\Helper\TimeHelper::nice             |
| toUnix            | Cake\View\Helper\TimeHelper::toUnix           |
| toAtom            | Cake\View\Helper\TimeHelper::toAtom           |
| toRss             | Cake\View\Helper\TimeHelper::toRss            |
| timeAgoInWords    | Cake\View\Helper\TimeHelper::timeAgoInWords   |
| gmt               | Cake\View\Helper\TimeHelper::gmt              |

### Other examples

```latte
{* Some examples *}

{link 'Click me' '/'}
{link 'Click me' url: ['controller' => 'Pages', 'action' => 'home'], options: ['class' => 'button]}
{view()->viewMethod()}
{request()->getQuery('search)}
{env('REMOTE_ADDR')}
{url(['controller' => 'Pages', 'action' => 'home'])}
{_'Bonjour'} - {='Hi'|translate} - {translate}Buongiorno{/translate}
{fetch cakeBlockName}
{cell cellName argument1, argument2, element: 'myEl', options: [option1 => 'value]}
{$date|timeAgoInWords}
{=[1,2,3]|toList}
```

## Using the Latte [type system](https://latte.nette.org/en/type-system)

> **Note:** Using the type system is completely optional. You can continue using traditional variable passing (e.g., `$this->set('variable', $value)`) as you would with any CakePHP view. The type system is an additional feature that enhances IDE support and type safety when desired.

One of the great things about Latte is its integration with various IDEs through the `{templateType}` and `{varType}` tags.

This plugin allows you to pass typed objects to templates, enabling you to utilize this powerful feature for better IDE support and type safety.

To make use of this feature, you need to pass a class extends `LatteView\View\Parameters`. 

> This class enables you to use the current view instance using the `getView()` method, allowing you to access it's methods and helpers from within your parameter class. Note that you should not access the view in the constructor as it is set at a later time.

First, create a class that implements this interface:
> This example shows how to add a custom Latte function and filter which both use a helper.

```php
<?php
declare(strict_types=1);

namespace App\View\Parameter;

use Latte\Attributes\TemplateFilter;
use Latte\Attributes\TemplateFunction;
use Latte\Runtime\Html;
use LatteView\View\Parameters;

class MyTemplateParameters extends Parameters
{
    public function __construct(
        public string $name = 'Default Name',
        public string $additional = 'Default Additional',
        public ?EntityInterface $entity = null,
    ) {
    }

    /**
     * A generator that yields the item count from a helper.
     */
    #[TemplateFunction]
    public function tag(): Html
    {
        $result = $this->getView()->Html->tag('strong', 'Hello from view!');

        // Use `Latte\Runtime\Html` if you need to return html. 
        return new Html($result);
    }

    #[TemplateFilter]
    public function currency(string|float $number, ?string $currency = 'EUR'): string
    {
        return $this->getView()->Number->currency($number, $currency);
    }    
}
```

Now, when passing data to your view (e.g., from inside your controller method), you can pass an instance of this class as an argument. Please note that all other arguments will be ignored as the class instance is the only object passed to your view.

```php
// MyController.php
use App\View\Parameters\MyTemplateParameters;

$entity = $users->get(1);

// Pass data to create an instance
$this->set(MyTemplateParameters::class, [
    'name' => 'John',
    'additional' => 'Doe',
    'entity' => $entity,
]);

// Or pass an instance of your ParameterInterface class
$params = new MyTemplateParameters(
    name: 'Hello',
    additional: 'World',
    entity: $entity
);

// Note that 'parameters' is ignored, you can use any name.
$this->set('parameters', $instance);
```

Then in your template

```latte
{templateType App\View\Parameter\MyTemplateParams}

Name: {$name}
Additional: {$additional}
Entity param: {$entity->id}

Tag from parameter class: {tag()} {* Result: "<strong>Hello from view!</strong>" *}
Currency: {='1000'|currency} {* Result: "Currency: €1,000.00" *}
```

## Console commands

This plugin ships with a console command for clearing the cache and [linting](https://latte.nette.org/en/develop#toc-linter) your templates.

The command will use the class path configured in your view settings.

Usage:
```bash
bin/cake latte clear
bin/cake latte clear -c MyView # For custom view classes
bin/cake latte clear -c MyPlugin.View # For plugin view classes

bin/cake latte linter # Scans your app templates
bin/cake latte linter -p MyPlugin # Scans your plugin templates
bin/cake latte linter -c CustomView -p MyPlugin # Uses CustomView in MyPlugin
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
