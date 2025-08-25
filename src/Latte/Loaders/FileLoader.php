<?php
declare(strict_types=1);

namespace LatteView\Latte\Loaders;

use Cake\Core\App;
use Cake\Core\Plugin;
use Latte\Loaders\FileLoader as LatteFileLoader;
use LatteView\Exception\TemplateNotFoundException;
use function Cake\Core\pluginSplit;

/**
 * Cake convention(ish) compatible FileLoader.
 *
 * This allows us to use absolute template paths.
 * This allows us to use plugin paths:
 *   `@pluginName./templateName`
 *
 * Class FileLoader
 */
class FileLoader extends LatteFileLoader
{
    /**
     * Returns template source code.
     */
    public function getContent(string $fileName): string
    {
        $fileName = $this->findTemplate($fileName);

        return parent::getContent($fileName);
    }

    /**
     * Find the full path to a template file.
     *
     * @param string $name Template name
     */
    public function findTemplate(string $name): string
    {
        if (file_exists($name)) {
            return $name;
        }

        // Detect plugin names using the '@' symbol
        if (str_contains($name, '@')) {
            $name = substr($name, strpos($name, '@') + 1);
        }

        [$plugin, $name] = pluginSplit($name);
        $name = str_replace('/', DIRECTORY_SEPARATOR, $name);

        if ($plugin !== null) {
            return $this->findPluginTemplate($plugin, $name);
        }

        return $this->findAppTemplate($name);
    }

    /**
     * Find template in plugin path.
     */
    protected function findPluginTemplate(string $plugin, string $name): string
    {
        $name = $this->addExtension($name);
        $templatePath = Plugin::templatePath($plugin);

        $path = $templatePath . $name;
        if (file_exists($path)) {
            return $this->normalizer($path);
        }

        throw new TemplateNotFoundException(
            sprintf('Could not find template `%s` in plugin `%s` at path: %s', $name, $plugin, $templatePath),
        );
    }

    /**
     * Find template in application paths.
     */
    protected function findAppTemplate(string $name): string
    {
        $templatePaths = App::path('templates');
        $name = $this->addExtension($name);

        foreach ($templatePaths as $templatePath) {
            $path = str_replace($templatePath, '', $name);
            $path = $templatePath . DS . $path;
            if ($path !== null && file_exists($path)) {
                return $this->normalizer($path);
            }
        }

        $pathList = implode("\n- ", $templatePaths);
        throw new TemplateNotFoundException(
            "Could not find template `{$name}` in paths:\n- {$pathList}",
        );
    }

    /**
     * Add the default .latte extension if no extension is present.
     */
    protected function addExtension(string $path): string
    {
        return $path . '.latte';
    }

    /**
     * Normalize the template path.
     */
    protected function normalizer(string $path): string
    {
        return preg_replace('#/+#', DIRECTORY_SEPARATOR, $path) ?? '';
    }
}
