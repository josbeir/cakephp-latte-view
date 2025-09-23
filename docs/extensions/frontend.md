# Frontend Extension

The Frontend Extension provides seamless integration between CakePHP data and modern frontend frameworks through universal data serialization. It converts PHP variables to JSON with proper escaping for use in HTML attributes.

> **Note**: This extension is essentially a convenient wrapper around Latte's built-in `escapeHtml()` and `escapeJs()` filters, providing framework-specific attribute generation and automatic data serialization.

## Features

- **Universal Data Serialization**: Convert any PHP variable (entities, arrays, scalars) to JSON
- **Framework Agnostic**: Support for AlpineJS, Stimulus, HTMX, and custom frameworks
- **Named Components**: Support for multiple controllers/components with different data
- **Security**: Automatic XSS prevention using Latte's built-in escaping
- **Configurable**: Easy framework mapping configuration

## Installation & Configuration

### Enable the Extension

Add the frontend extension to your LatteView configuration:

```php
// In your application config (e.g., config/app.php)
'LatteView' => [
    'extensions' => [
        'frontend' => []
    ]
]
```

### Custom Framework Mappings

Configure custom framework mappings using the `{name}` placeholder:

```php
'LatteView' => [
    'extensions' => [
        'frontend' => [
            'alpine' => 'x-data',                    // Default
            'stimulus' => 'data-{name}-value',       // Default
            'htmx' => 'hx-vals',                     // Default
            'vue' => ':data',                        // Custom
            'turbo' => 'data-{name}-stream',         // Custom
            'custom' => 'data-{name}-props'          // Custom
        ]
    ]
]
```

The `{name}` placeholder will be replaced with the component name specified in templates.

## Usage

### Basic Data Attributes

#### Generic Data Attribute
```latte
{* Creates data-json attribute *}
<div n:data="$user" data-my-framework="true">
    Framework reads from data-json
</div>
```

**Compiles to:**
```html
<div data-json="{&quot;name&quot;:&quot;John Doe&quot;,&quot;email&quot;:&quot;john@example.com&quot;}" data-my-framework="true">
    Framework reads from data-json
</div>
```

#### AlpineJS Integration
```latte
{* Creates x-data attribute *}
<div n:data-alpine="$user">
    <h1 x-text="name"></h1>
    <p x-text="email"></p>
</div>
```

**Compiles to:**
```html
<div x-data="{&quot;name&quot;:&quot;John Doe&quot;,&quot;email&quot;:&quot;john@example.com&quot;}">
    <h1 x-text="name"></h1>
    <p x-text="email"></p>
</div>
```

```latte
{* Works with scalar values *}
<div n:data-alpine="'Hello World'">
    <span x-text="data"></span>
</div>
```

**Compiles to:**
```html
<div x-data="{&quot;data&quot;:&quot;Hello World&quot;}">
    <span x-text="data"></span>
</div>
```

```latte
{* Works with arrays and collections *}
<div n:data-alpine="$articles">
    <template x-for="article in data">
        <div x-text="article.title"></div>
    </template>
</div>
```

**Compiles to:**
```html
<div x-data="[{&quot;title&quot;:&quot;Article 1&quot;},{&quot;title&quot;:&quot;Article 2&quot;}]">
    <template x-for="article in data">
        <div x-text="article.title"></div>
    </template>
</div>
```

#### Stimulus Integration
```latte
{* Single controller *}
<div data-controller="user-profile" n:data-stimulus:user-profile="$user">
    <span data-user-profile-target="name"></span>
</div>
```

**Compiles to:**
```html
<div data-controller="user-profile" data-user-profile-value="{&quot;name&quot;:&quot;John Doe&quot;,&quot;email&quot;:&quot;john@example.com&quot;}">
    <span data-user-profile-target="name"></span>
</div>
```

```latte
{* Multiple controllers with different data *}
<div data-controller="user-profile form-validator"
     n:data-stimulus:user-profile="$user"
     n:data-stimulus:form-validator="$validationRules">

    <form data-form-validator-target="form">
        <input data-user-profile-target="nameField" />
    </form>
</div>
```

**Compiles to:**
```html
<div data-controller="user-profile form-validator"
     data-user-profile-value="{&quot;name&quot;:&quot;John Doe&quot;,&quot;email&quot;:&quot;john@example.com&quot;}"
     data-form-validator-value="{&quot;required&quot;:[&quot;name&quot;,&quot;email&quot;]}">

    <form data-form-validator-target="form">
        <input data-user-profile-target="nameField" />
    </form>
</div>
```

#### HTMX Integration
```latte
{* Creates hx-vals attribute *}
<button n:data-htmx="$params" hx-post="/api/update" hx-target="#result">
    Update Data
</button>
```

**Compiles to:**
```html
<button hx-vals="{&quot;id&quot;:123,&quot;action&quot;:&quot;update&quot;}" hx-post="/api/update" hx-target="#result">
    Update Data
</button>
```

```latte
{* Form with HTMX data *}
<form n:data-htmx="$formData" hx-post="/submit" hx-swap="outerHTML">
    <input type="text" name="title" />
    <button type="submit">Submit</button>
</form>
```

**Compiles to:**
```html
<form hx-vals="{&quot;author_id&quot;:42,&quot;category&quot;:&quot;news&quot;}" hx-post="/submit" hx-swap="outerHTML">
    <input type="text" name="title" />
    <button type="submit">Submit</button>
</form>
```

### Data Types

The extension handles all PHP data types:

#### CakePHP Entities
```latte
{* Automatically uses toArray() method *}
<div n:data-alpine="$user">
    <span x-text="name"></span>
    <span x-text="email"></span>
</div>
```

#### Arrays and Collections
```latte
{* Direct array serialization *}
<div n:data-alpine="$articles">
    <template x-for="article in data">
        <div x-text="article.title"></div>
    </template>
</div>
```

#### Scalar Values
```latte
{* Wrapped in {data: value} for consistency *}
<div n:data-alpine="'Hello World'">
    <span x-text="data"></span>
</div>

<div n:data-alpine="42">
    <span x-text="data"></span>
</div>
```

#### Mixed Data
```latte
{* Combine different data types *}
<div n:data-alpine="[
    'message' => $message,
    'count' => $count,
    'users' => $users,
    'config' => ['theme' => 'dark']
]">
    <span x-text="message"></span>
    <span x-text="count"></span>
    <template x-for="user in users">
        <div x-text="user.name"></div>
    </template>
</div>
```

## Framework-Specific Examples

### AlpineJS
```latte
{* Component with reactive data - include all data from controller *}
<div n:data-alpine="['user' => $user, 'editing' => false]">
    <template x-if="!editing">
        <div>
            <h2 x-text="user.name"></h2>
            <button @click="editing = true">Edit</button>
        </div>
    </template>

    <template x-if="editing">
        <form @submit.prevent="editing = false">
            <input x-model="user.name" />
            <button type="submit">Save</button>
        </form>
    </template>
</div>
```

### Stimulus
```latte
{* User profile controller *}
<div data-controller="user-profile" n:data-stimulus:user-profile="$user">
    <img data-user-profile-target="avatar" />
    <h1 data-user-profile-target="name"></h1>
    <button data-action="click->user-profile#edit">Edit Profile</button>
</div>

{* Form validation controller *}
<form data-controller="form-validator"
      n:data-stimulus:form-validator="$validationRules"
      data-action="submit->form-validator#validate">

    <input data-form-validator-target="field" name="email" />
    <div data-form-validator-target="errors"></div>
</form>
```

### HTMX
```latte
{* Dynamic content loading *}
<div hx-get="/users" n:data-htmx="['page' => 1, 'limit' => 10]">
    Loading users...
</div>

{* Form submission with context *}
<form hx-post="/articles" n:data-htmx="['author_id' => $currentUser->id]">
    <input name="title" placeholder="Article title" />
    <textarea name="content" placeholder="Content"></textarea>
    <button type="submit">Publish</button>
</form>
```

### XSS Protection Example

```latte
{* Dangerous input *}
<div n:data-alpine="['script' => '<script>alert(\'xss\')</script>', 'safe' => 'normal text']">
    <span x-text="safe"></span>
</div>
```

**Safely compiles to:**
```html
<div x-data="{&quot;script&quot;:&quot;\\u003Cscript\\u003Ealert(\\u0027xss\\u0027)\\u003C\\/script\\u003E&quot;,&quot;safe&quot;:&quot;normal text&quot;}">
    <span x-text="safe"></span>
</div>
```

Notice how:
- `<` becomes `\\u003C` (escaped angle bracket)
- `'` becomes `\\u0027` (escaped quote)
- `/` becomes `\\/` (escaped slash)
- The malicious script is completely neutralized

## Helper Functions

The extension provides a convenient helper function for templates:

```latte
{* Convert any data to JSON *}
{json($user)}
```