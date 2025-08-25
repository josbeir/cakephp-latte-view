<?php
declare(strict_types=1);

namespace LatteView;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;

/**
 * Plugin for LatteView
 */
class LatteViewPlugin extends BasePlugin
{
    /**
     * Add commands for the plugin.
     *
     * @param \Cake\Console\CommandCollection $commands The command collection to update.
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Add your commands here
        // remove this method hook if you don't need it

        $commands = parent::console($commands);

        return $commands;
    }
}
