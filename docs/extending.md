# Extending

You can add your own Latte extensions or modify the sandbox policy using:

```php
use Latte\Extension;
use Latte\Sandbox\SecurityPolicy;

$view->getEngine()->addExtension(new YourExtension());
$view->setSandboxPolicy($yourPolicy);
```
