# Console commands

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