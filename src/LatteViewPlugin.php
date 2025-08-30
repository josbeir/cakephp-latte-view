<?php
declare(strict_types=1);

namespace LatteView;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use LatteView\Command\CacheCommand;
use LatteView\Command\LinterCommand;

/**
 * Plugin for LatteView
 */
class LatteViewPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('latte clear', CacheCommand::class);
        $commands->add('latte linter', LinterCommand::class);

        return $commands;
    }
}
