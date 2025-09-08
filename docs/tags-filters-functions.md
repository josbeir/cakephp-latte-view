# Custom Tags, Functions and Filters

This plugin extends Latte with CakePHP-specific functionality, providing convenient functions, tags, and filters that integrate seamlessly with your CakePHP application:

| Function | Description |
|----------|-------------|
| `view()` | Returns the current View instance. |
| `request()`| Returns the current request instance. |
| `config()` | Read a config value, Maps to Configure::read() |
| `env()` | Access environment variables |
| `url()` | Url generation - See `Router::url()`. |
| `rurl()` | Reverse url generation - See `Router::reverse()`. |
| `{fetch 'name'}`| Cake's `View::fetch()` method, introduced to keep legacy functionality of helpers that use view blocks.
| `{cell name}` | Cake's `View::cell()` method
| `{HelperName method arg1, arg2}` | Access any CakePHP helper using the helper name followed by its methodname args. (See docs below) |
| `helper('Html')` | Returns a helper instance object. Depending on your needs you can decide to use the function or the tag. |
| `n:href`, `n:named` | `n:attribute` access for building [links](#links). |
| `n:context`, `n:name` | `n:attribute` access for building [forms](#forms). |

## Helpers

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

## Links

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

## Forms

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
    <select n:name="options, options: $my_options" />
    <label n:name="description">Description</label>
    <textarea n:name="description"></textarea>
    <button type="submit">Save</button> {* or {Form submit} *}
</form>

<form n:context="$user, type: file, url: ['_name' => 'display']" class="my-form">
    <control n:name="file">
    <button type="submit">{_'Upload'}</button>
</form>
```

### postLink and postButton support

This plugin provides `n:attributes` for `FormHelper::postLink` and `FormHelper::postButton` in the form of `n:post`

```latte
<!-- Links -->
<a n:post="[_name: 'named:route']" confirmMessage="Are you sure?">
    I'm a postLink
</a>

<!-- Buttons -->
<button n:post="[action: 'delete', $artcle->id]">
    <strong>Hello</strong>
</button>
```

### Passing options to `n:context`, `n:name`, `n:post`

There are 2 ways of passing options to FormHelper methods. 

1: Using n:name style arguments, these give you full control of dataa passed to the method.

```latte
{var $label = 'My label'}
<control n:name="myfield, label: $label" /> 
{* Compiles to: $this->Form->control('myfield', ['label' => $label]); *}
```

2: Using HTML attributes:
```latte
{var $label = 'My label'}
<control n:name="myfield" label="{$label}" /> 
{* Compiles to: $this->Form->control('myfield', ['label' => 'My label']); *}
```

> [!TIP]
> You can decide on how to pass arguments. The downside of passing HTML attributes (2nd option) is that currently no modifiers are supported. So filters and complex expressions will not work. In this case passing a variable would be a better choice.

## I18n

The plugin provides seamless integration with CakePHP's I18n system through Latte's built-in translation tags and filters:

**Tokens:** By default, [CakePHP uses the ICU formatter](https://book.cakephp.org/5/en/core-libraries/internationalization-and-localization.html#using-different-formatters) to handle tokens in its translation functions. Although this works when using `{_'Hello {0}', 'world'}`, it would clash with the Latte pattern when doing `{translate 'world'}Hello {0}{/translate}`. It is therefore recommended to use the sprintf formatter which you can enable by setting the following in your application (bootstrap for instance).

```php
I18n::setDefaultFormatter('sprintf');
```

Examples:
```latte
{* Basic translation *}
{_'Hello, World!'}
{='Welcome back'|translate}

{* Translation with domain *}
{_'Admin Panel', domain: 'admin'}

{* Translation with tokens *}
{_'Hello from %s', 'Brussels'}
{translate $username, $email}Welcome %s, your email %s has been verified{/translate}

{* Pluralization *}
{translate $count, singular: '%s item', count: $count}%s items{/translate}
{_'%s items', $count, singular: '%s item'}
```

All translation calls automatically use CakePHP's I18n functions under the hood, ensuring full compatibility with your existing translation workflow and message files. 

Please note that no __x() related functions are implemented.

## Filters

The following [filters](https://latte.nette.org/en/filters) are mapped to their CakePHP counterparts, providing integration with CakePHP's utility classes for text manipulation, time formatting, number operations, and inflector transformations. 

> Some filters present in these classes are omitted because Latte already has its own implementation.

### [Text](https://book.cakephp.org/5/en/core-libraries/text.html) filters

| Filter          | Maps to                          |
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

### [Number](https://book.cakephp.org/5/en/core-libraries/number.html) filters

| Filter            | Maps to                          |
|-------------------|----------------------------------|
| precision         | Cake\I18n\Number::precision      |
| toReadableSize    | Cake\I18n\Number::toReadableSize |
| toPercentage      | Cake\I18n\Number::toPercentage   |
| parseFloat        | Cake\I18n\Number::parseFloat     |
| formatDelta       | Cake\I18n\Number::formatDelta    |
| currency          | Cake\I18n\Number::currency       |
| formatter         | Cake\I18n\Number::formatter      |
| ordinal           | Cake\I18n\Number::ordinal        |


### [Inflector](https://book.cakephp.org/5/en/core-libraries/inflector.html) filters

| Filter            | Maps to                          |
|-------------------|----------------------------------|
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

### Time filters

| Filter            | Maps to                          |
|-------------------|----------------------------------|
| format            | Cake\View\Helper\TimeHelper::format           |
| i18nFormat        | Cake\View\Helper\TimeHelper::i18nFormat       |
| nice              | Cake\View\Helper\TimeHelper::nice             |
| toUnix            | Cake\View\Helper\TimeHelper::toUnix           |
| toAtom            | Cake\View\Helper\TimeHelper::toAtom           |
| toRss             | Cake\View\Helper\TimeHelper::toRss            |
| timeAgoInWords    | Cake\View\Helper\TimeHelper::timeAgoInWords   |
| gmt               | Cake\View\Helper\TimeHelper::gmt              |