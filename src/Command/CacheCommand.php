<?php
declare(strict_types=1);

namespace LatteView\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\App;
use LatteView\View\LatteView;
use RuntimeException;

class CacheCommand extends Command
{
    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Clear the latte cache directory';
    }

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addOption('class', [
            'help' => 'The view class to use for rendering.',
            'default' => 'App',
            'short' => 'c',
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $class = (string)$args->getOption('class');
        $viewClass = App::className($class, 'View', 'View');

        if (!$viewClass) {
            throw new RuntimeException('View class ' . $class . ' not found.');
        }

        $cacheDir = null;
        if (is_subclass_of($viewClass, LatteView::class)) {
            $cacheDir = (new $viewClass())->getConfig('cachePath');
        } else {
            throw new RuntimeException('Invalid view class. View class must be subclass of \LatteView\View\LatteView.');
        }

        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*') ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $io->success('Cache directory cleared successfully.');
        } else {
            $io->warning('Cache directory does not exist.');
        }

        return self::CODE_SUCCESS;
    }
}
