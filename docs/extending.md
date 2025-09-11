# Extending Latte

This page covers how to extend the Latte templating engine with custom functionality in your CakePHP application.

## Adding Custom Extensions

You can add your own Latte extensions to provide custom functions, filters, and tags. For detailed information on creating extensions, see the [Latte Extensions documentation](https://latte.nette.org/en/creating-extension).

### Registering Extensions in Your View

```php
<?php
namespace App\View;

use App\View\Extension\MyCustomExtension;
use Latte\Extension;
use LatteView\View\LatteView;

class AppView extends LatteView
{
    public function initialize(): void
    {
        parent::initialize();
        
        // Add your custom extension
        $this->getEngine()->addExtension(new MyCustomExtension());
        
        // Or add multiple extensions
        $this->getEngine()->addExtension(new AnotherExtension());
    }
}
```

## Configuring Sandbox Security

When sandbox mode is enabled, you can customize the security policy to control template access:

```php
<?php
namespace App\View;

use Latte\Sandbox\SecurityPolicy;
use LatteView\View\LatteView;

class AppView extends LatteView
{
    public function initialize(): void
    {
        parent::initialize();
        
        // Enable sandbox mode
        $this->setConfig('sandbox', true);
        
        // Create and configure security policy
        $policy = new SecurityPolicy();
        $policy->allowFunctions(['date', 'strtoupper']);
        $policy->allowMethods([
            'Cake\ORM\Entity' => ['get', 'has', 'toArray'],
        ]);
        
        $this->setSandboxPolicy($policy);
    }
}
```

For more information on security policies, see the [Latte Sandbox documentation](https://latte.nette.org/en/sandbox).
