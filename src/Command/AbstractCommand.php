<?php
declare(strict_types=1);

namespace LatteView\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\App;
use LatteView\View\LatteView;
use RuntimeException;

abstract class AbstractCommand extends Command
{
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
     * Get the view class name instance from the arguments.
     */
    public function getViewClass(Arguments $args): LatteView
    {
        $class = (string)$args->getOption('class');
        $viewClass = App::className($class, 'View', 'View');

        if (!$viewClass) {
            throw new RuntimeException('View class ' . $class . ' not found.');
        }

        if (!is_subclass_of($viewClass, LatteView::class)) {
            throw new RuntimeException('Invalid view class. View class must be subclass of \LatteView\View\LatteView.');
        }

        return new $viewClass();
    }
}
