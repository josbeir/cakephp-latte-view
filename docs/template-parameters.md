# Template parameters

> **Note:** Using the type system is completely optional. You can continue using traditional variable passing (e.g., `$this->set('variable', $value)`) as you would with any CakePHP view. The type system is an additional feature that enhances IDE support and type safety when desired.

## Using the Latte [type system](https://latte.nette.org/en/type-system)

The feature you didn't know you needed until you started building those delightfully complex templates! 🎯

Latte's powerful type system enhances your development experience through IDE integration via `{templateType}` and `{varType}` tags. This system not only provides intelligent code completion and type hints in your IDE, but also enables you to define custom functions and filters directly within your parameter classes.

This plugin leverages Latte's type system by allowing you to pass strongly-typed parameter objects to your templates, combining the benefits of type safety with enhanced IDE support for a more robust development workflow. 

To make use of this feature, you need to pass a class that extends `LatteView\View\Parameters`. 

> This class enables you to use the current view instance using the `getView()` method, allowing you to access it's methods and helpers from within your parameter class. Note that you should not access the view in the constructor as it is set at a later time.

First, create a class that extends `\LatteView\View\Parameters`:
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
// UsersController.php
use App\View\Parameters\MyTemplateParameters;

public function view($id)
{
    $user = $this->Users->get($id);
    
    // Method 1: Pass data to create an instance
    $this->set(MyTemplateParameters::class, [
        'name' => $user->full_name,
        'additional' => 'Profile Page',
        'entity' => $user,
    ]);
    
    // Method 2: Or pass an instance of your Parameters class
    $params = new MyTemplateParameters(
        name: $user->full_name,
        additional: 'User Profile',
        entity: $user
    );
    
    // The variable name doesn't matter when passing an instance
    $this->set('userParams', $params);
}
```

In your template, use the `{templateType}` tag to enable IDE support and type safety:

```latte
{templateType App\View\Parameter\MyTemplateParameters}

Name: {$name}
Additional: {$additional}
Entity param: {$entity->id}

Tag from parameter class: {tag()} {* Result: "<strong>Hello from view!</strong>" *}
Currency: {='1000'|currency} {* Result: "Currency: €1,000.00" *}
```
