<?php
declare(strict_types=1);

namespace LatteView;

use Cake\Command\CacheClearallCommand;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManagerInterface;
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

    /**
     * @inheritDoc
     */
    public function events(EventManagerInterface $eventManager): EventManagerInterface
    {
        if (!Configure::read('LatteView.disableCacheClearListener', false)) {
            $eventManager->on('Command.afterExecute', function (Event $event, $args, $result): void {
                $command = $event->getSubject();
                if ($command instanceof CacheClearallCommand) {
                    $command->executeCommand(CacheCommand::class);
                }
            });
        }

        return $eventManager;
    }
}
