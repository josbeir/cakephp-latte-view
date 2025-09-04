# Debugging

This plugin provides several debugging tools to help you inspect variables and understand template structure during development.

## Tags

- `{dump $var}` or `{debug $var}`: Uses CakePHP's `Debugger::printVar()` instead of Nette's default dumper
- `{dump}`: Dumps all defined variables using CakePHP's debugger

## DebugKit Panel

![DebugKit Panel](./public/debugkit_panel.png)

When DebugKit is installed and debug mode is enabled, this plugin provides a "Latte" panel for the DebugKit toolbar that shows a visual tree representation of your template structure, including template inheritance hierarchy and layout relationships.

- Ensure `cakephp/debug_kit` is installed and enabled in development.
- The panel appears as `Latte` in the DebugKit toolbar.

To enable the panel, add it to your DebugKit configuration:

```php
Configure::write('DebugKit.panels', ['LatteView.Latte']);
```