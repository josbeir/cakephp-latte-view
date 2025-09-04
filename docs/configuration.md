# Configuration

This page covers the configuration options available for this plugin, allowing you to customize template rendering behavior for your application needs.

## Options

Set options via `ViewBuilder::setOption()` or `setOptions()`:

| Option         | Type    | Default                | Description                                                                 |
|----------------|---------|------------------------|-----------------------------------------------------------------------------|
| `cache`        | bool    | `true`                 | Enable/disable template caching. Caching is always enabled except when explicitly set to `false`. |
| `autoRefresh`  | bool    | `false` (or `true` in debug) | Automatically refresh templates. Auto-refresh is always enabled in debug mode. |
| `blocks`       | array|string  | `'content'`            | Block name(s) that are rendered when autoLayout is disabled. [Read more](#the-blocks-option) |
| `cachePath`    | string  | `CACHE . 'latte_view'` | Path for compiled template cache                                            |
| `sandbox`      | bool    | `false`                | Enable sandbox mode for secure template execution. When enabled, the security policy can be configured using `setSandboxPolicy()` and `getSandboxPolicy()`. |
| `rawphp`       | bool | `true` | Enable/disable the use of raw PHP code in templates via the [{php} tag](https://latte.nette.org/en/develop#toc-rawphpextension). |
| `defaultHelpers` | array | ... | List of default Cake helpers that need to be present. Defaults to all core helpers. |

## The `blocks` option.

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