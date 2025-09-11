# Console Commands

This plugin ships with console commands for clearing the cache and [linting](https://latte.nette.org/en/develop#toc-linter) your templates.

The commands will use the class path configured in your view settings.

## Clearing the cache folder

This plugin will automatically clear the latte cache folder when using `bin/cake cache clear_all`.

If you wish to disable this behavior, add the following option.

```php
Configure::write('LatteView.disableCacheClearListener', true);
```

If you want to clear the cache in a more controlled way, use the command below.

**Usage:**
```bash
bin/cake latte clear
bin/cake latte clear -c MyView # For custom view classes
bin/cake latte clear -c MyPlugin.View # For plugin view classes
```

## Template Linting

The Latte linter helps you catch syntax errors and potential issues in your templates before they cause runtime errors. This is particularly useful during static analysis processes to ensure code quality and prevent deployment of faulty templates.

**Usage:**
```bash
bin/cake latte linter                    # Scan app templates
bin/cake latte linter -p MyPlugin        # Scan plugin templates
bin/cake latte linter -c CustomView -p MyPlugin  # Use custom view class
```