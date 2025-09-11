# Configuration

This page covers the configuration options available for this plugin, allowing you to customize template rendering behavior for your application needs.

## Options

Set options via `ViewBuilder::setOption()` or `setOptions()`:

| Option         | Type    | Default                | Description                                                                 |
|----------------|---------|------------------------|-----------------------------------------------------------------------------|
| `cache`        | bool    | `true`                 | Enable/disable template caching. Caching is always enabled except when explicitly set to `false`. |
| `autoRefresh`  | bool    | `false` (or `true` in debug) | Automatically refresh templates. Auto-refresh is always enabled in debug mode. |
| `fragments`       | array,string  | `'content'`            | Fragment (block) name(s) that should be rendered when autoLayout is disabled. [Read more](#the-fragments-option) |
| `cachePath`    | string  | `CACHE . 'latte_view'` | Path for compiled template cache                                            |
| `sandbox`      | bool    | `false`                | Enable sandbox mode for secure template execution. When enabled, the security policy can be configured using `setSandboxPolicy()` and `getSandboxPolicy()`. |
| `rawphp`       | bool | `true` | Enable/disable the use of raw PHP code in templates via the [{php} tag](https://latte.nette.org/en/develop#toc-rawphpextension). |
| `defaultHelpers` | array | ... | List of default Cake helpers that need to be present. Defaults to all core helpers. |

## The `fragments` option

The `fragments` option controls which template fragments (Latte blocks) are rendered when `autoLayout` is disabled. This feature enables you to return specific portions of a template without the full layout structure, making it ideal for partial page updates and AJAX responses.

Imagine you have this template

```latte
{* users/index.latte *}
<div class="users-index">
    <h1>{block pageTitle}All Users{/block}</h1>
    
    {block userTable}
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {foreach $users as $user}
            <tr>
                <td>{$user->name}</td>
                <td>{$user->email}</td>
                <td>
                    <a n:named="users:view, $user->id">View</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    {/block}
    
    {block pagination}
        {$this->Paginator->numbers()}
    {/block}
</div>

{block content}
    <div class="sidebar">
        <h3>User Management</h3>
        <ul>
            <li><a n:named="users:add">Add New User</a></li>
            <li><a n:named="users:export">Export Users</a></li>
        </ul>
    </div>
{/block}
```

When configuring the ViewBuilder to return only specific blocks, you can generate focused template fragments for partial page updates or AJAX responses:

```php
// In your controller action
public function index()
{
    $users = $this->paginate($this->Users);
    $this->set(compact('users'));
    
    // Handle AJAX requests for partial updates
    if ($this->getRequest()->is('ajax')) {
        // Disable autoLayout for AJAX responses
        $this->viewBuilder()->disableAutoLayout();
        
        // Return only the user table for dynamic updates
        $this->viewBuilder()->setOption('fragments', ['userTable']);
    }
}

public function search()
{
    $query = $this->getRequest()->getQuery('q');
    $users = $this->Users->find('all')
        ->where(['name LIKE' => '%' . $query . '%']);
    $this->set(compact('users'));
    
    if ($this->getRequest()->is('ajax')) {
        $this->viewBuilder()->disableAutoLayout();
        // Return multiple fragments for complex updates
        $this->viewBuilder()->setOption('fragments', ['userTable', 'pagination']);
    }
}
```

This approach is particularly useful for:
- AJAX-powered dynamic content updates
- Optimizing performance by sending only the necessary HTML fragments
- More info [here](https://htmx.org/essays/template-fragments/)