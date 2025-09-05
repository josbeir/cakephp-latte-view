# Console commands

This plugin ships with a console command for clearing the cache and [linting](https://latte.nette.org/en/develop#toc-linter) your templates.

The command will use the class path configured in your view settings.

## Clearing the cache folder

This plugin will automatically clear the latte cache folder when using `bin/cake cache clear_all`.

If you wish to disable this behavior, add the following option.

```php
Configure::write('LatteView.disableCacheClearListener', true);
```

If you want to clear the cache in a more controlled way, use the command below.

Usage:
```bash
bin/cake latte clear
bin/cake latte clear -c MyView # For custom view classes
bin/cake latte clear -c MyPlugin.View # For plugin view classes
```

## Test your templates for errors

```bash
bin/cake latte linter # Scans your app templates
bin/cake latte linter -p MyPlugin # Scans your plugin templates
bin/cake latte linter -c CustomView -p MyPlugin # Uses CustomView in MyPlugin
```